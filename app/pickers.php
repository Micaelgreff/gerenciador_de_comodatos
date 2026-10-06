<?php
// All picker queries and reservations stay in the local application/database.
function inventory_models(int $brand, int $current=0): array {
    return query('SELECT m.id,m.nome label,m.marca_id,m.tipo_equipamento_id,t.nome tipo FROM modelos m JOIN marcas ma ON ma.id=m.marca_id JOIN tipos_equipamentos t ON t.id=m.tipo_equipamento_id WHERE m.marca_id=? AND ((m.ativo=1 AND ma.ativo=1 AND t.ativo=1) OR m.id=?) ORDER BY m.nome,m.id',[$brand,$current])->fetchAll();
}

function equipment_labels(array $ids): array {
    $ids=array_values(array_unique(array_filter(array_map('intval',$ids),fn($id)=>$id>0)));
    if(!$ids) return [];
    $marks=implode(',',array_fill(0,count($ids),'?'));
    return query('SELECT i.id,CONCAT(ma.nome," · ",t.nome," · ",m.nome," · Item #",i.id," · Patrimônio: ",COALESCE(NULLIF(i.patrimonio,""),"não informado")) label FROM inventario i JOIN modelos m ON m.id=i.modelo_id JOIN marcas ma ON ma.id=m.marca_id JOIN tipos_equipamentos t ON t.id=m.tipo_equipamento_id WHERE i.id IN ('.$marks.')',$ids)->fetchAll();
}

function picker_results(string $kind, string $search='', int $loan=0, string $token='', array $exclude=[]): array {
    $search=mb_substr(trim($search),0,100);
    if(in_array($kind,['comodantes','comodatarios'],true)) {
        $label=$kind==='comodantes'?'nome_fantasia':'nome_completo';
        $document=$kind==='comodantes'?'cnpj':'cpf';
        return query("SELECT id,`$label` label FROM `$kind` WHERE ativo=1 AND (`$label` LIKE ? OR `$document` LIKE ?) ORDER BY `$label`,id LIMIT 10",['%'.$search.'%','%'.$search.'%'])->fetchAll();
    }
    if($kind!=='inventario') throw new DomainException('Seleção inválida.');
    $params=[$loan,$token];
    $where='i.ativo=1 AND NOT EXISTS (SELECT 1 FROM comodato_itens ci WHERE ci.inventario_id=i.id AND ci.ativo=1 AND ci.comodato_id<>?) AND NOT EXISTS (SELECT 1 FROM inventario_reservas r WHERE r.inventario_id=i.id AND r.expira_em>NOW() AND r.token<>?)';
    $exclude=array_values(array_unique(array_filter(array_map('intval',$exclude),fn($id)=>$id>0)));
    if($exclude) { $where.=' AND i.id NOT IN ('.implode(',',array_fill(0,count($exclude),'?')).')'; $params=[...$params,...$exclude]; }
    $where.=' AND (i.patrimonio LIKE ? OR m.nome LIKE ? OR ma.nome LIKE ? OR t.nome LIKE ? OR CAST(i.id AS CHAR) LIKE ?)';
    for($n=0;$n<5;$n++) $params[]='%'.$search.'%';
    return query('SELECT i.id,CONCAT(ma.nome," · ",t.nome," · ",m.nome," · Item #",i.id," · Patrimônio: ",COALESCE(NULLIF(i.patrimonio,""),"não informado")) label FROM inventario i JOIN modelos m ON m.id=i.modelo_id JOIN marcas ma ON ma.id=m.marca_id JOIN tipos_equipamentos t ON t.id=m.tipo_equipamento_id WHERE '.$where.' ORDER BY ma.nome,m.nome,i.id LIMIT 10',$params)->fetchAll();
}

function reservation_context(int $loan, string $existing=''): string {
    if($existing!=='' && isset($_SESSION['inventory_forms'][$existing]) && $_SESSION['inventory_forms'][$existing]===$loan) return $existing;
    $token=bin2hex(random_bytes(32));
    $_SESSION['inventory_forms'][$token]=$loan;
    return $token;
}

function verify_reservation_context(int $loan, string $token): void {
    if(!preg_match('/^[a-f0-9]{64}$/',$token) || !isset($_SESSION['inventory_forms'][$token]) || $_SESSION['inventory_forms'][$token]!==$loan) throw new DomainException('O formulário expirou. Recarregue a página para selecionar os equipamentos.');
}

function editable_loan(int $loan): void {
    if($loan && !query('SELECT id FROM comodatos WHERE id=? AND ativo=1',[$loan])->fetchColumn()) throw new DomainException('Comodato não encontrado ou já encerrado.');
}

function available_equipment(int $item, int $loan): bool {
    return (bool)query('SELECT id FROM inventario i WHERE i.id=? AND i.ativo=1 AND NOT EXISTS (SELECT 1 FROM comodato_itens ci WHERE ci.inventario_id=i.id AND ci.ativo=1 AND ci.comodato_id<>?)',[$item,$loan])->fetchColumn();
}

function reserve_equipment(string $token, int $loan, int $item, int $previous=0): void {
    db()->beginTransaction();
    try {
        query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');
        editable_loan($loan);
        if(!available_equipment($item,$loan)) throw new DomainException('Este equipamento não está mais disponível. Selecione outro item.');
        $owner=query('SELECT token FROM inventario_reservas WHERE inventario_id=? AND expira_em>NOW()',[$item])->fetchColumn();
        if($owner && $owner!==$token) throw new DomainException('Este equipamento foi selecionado em outra sessão. Selecione outro item.');
        query('INSERT INTO inventario_reservas (inventario_id,token,expira_em) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 3 MINUTE)) ON DUPLICATE KEY UPDATE token=VALUES(token),expira_em=VALUES(expira_em)',[$item,$token]);
        if($previous && $previous!==$item) query('DELETE FROM inventario_reservas WHERE inventario_id=? AND token=?',[$previous,$token]);
        db()->commit();
    } catch(Throwable $ex) { if(db()->inTransaction()) db()->rollBack(); throw $ex; }
}

function renew_equipment(string $token, int $loan, array $items): array {
    $items=array_values(array_unique(array_map('intval',$items))); $lost=[];
    db()->beginTransaction();
    try {
        query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');
        editable_loan($loan);
        foreach($items as $item) {
            if(!available_equipment($item,$loan)) { query('DELETE FROM inventario_reservas WHERE inventario_id=? AND token=?',[$item,$token]); $lost[]=$item; continue; }
            $updated=query('UPDATE inventario_reservas SET expira_em=DATE_ADD(NOW(),INTERVAL 3 MINUTE) WHERE inventario_id=? AND token=? AND expira_em>NOW()',[$item,$token]);
            // MariaDB may report zero changed rows when two renewals happen in the same second.
            if(!$updated->rowCount() && !query('SELECT inventario_id FROM inventario_reservas WHERE inventario_id=? AND token=? AND expira_em>NOW()',[$item,$token])->fetchColumn()) $lost[]=$item;
        }
        db()->commit(); return $lost;
    } catch(Throwable $ex) { if(db()->inTransaction()) db()->rollBack(); throw $ex; }
}

function release_equipment(string $token, int $item=0): void {
    db()->beginTransaction();
    try {
        query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');
        query('DELETE FROM inventario_reservas WHERE token=?'.($item?' AND inventario_id=?':''),$item?[$token,$item]:[$token]);
        db()->commit();
    } catch(Throwable $ex) { if(db()->inTransaction()) db()->rollBack(); throw $ex; }
}

function serve_picker(string $module, int $loan): never {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    try {
        if($module==='inventario' && $_SERVER['REQUEST_METHOD']==='GET' && ($_GET['kind']??'')==='inventory_models') {
            $data=['options'=>inventory_models(max(0,(int)($_GET['brand']??0)))];
        } elseif($module==='comodatos') {
            $token=(string)($_POST['reserva_token']??$_GET['reserva_token']??'');
            verify_reservation_context($loan,$token);
            if($_SERVER['REQUEST_METHOD']==='GET') {
                editable_loan($loan);
                $data=['options'=>picker_results((string)($_GET['kind']??''),(string)($_GET['q']??''),$loan,$token,array_slice((array)($_GET['exclude']??[]),0,500))];
            } elseif($_SERVER['REQUEST_METHOD']==='POST') {
                verify_csrf();
                $operation=(string)($_POST['operation']??'');
                if($operation==='reserve') { reserve_equipment($token,$loan,(int)($_POST['item']??0),(int)($_POST['previous']??0)); $data=['ok'=>true]; }
                elseif($operation==='renew') $data=['lost'=>renew_equipment($token,$loan,array_slice((array)($_POST['itens']??[]),0,500))];
                elseif($operation==='release') { release_equipment($token,(int)($_POST['item']??0)); $data=['ok'=>true]; }
                else throw new DomainException('Operação inválida.');
            } else throw new DomainException('Operação inválida.');
        } else throw new DomainException('Seleção inválida.');
        echo json_encode($data,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
    } catch(DomainException $ex) { http_response_code(409); echo json_encode(['error'=>$ex->getMessage()],JSON_UNESCAPED_UNICODE); }
    catch(PDOException $ex) { error_log('GDC picker database error: '.$ex->getCode()); http_response_code(500); echo json_encode(['error'=>'Não foi possível consultar os registros. Tente novamente.']); }
    exit;
}
