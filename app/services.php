<?php
function options(string $table, int $current=0): array {
    global $modules;
    if($table==='modelos') return query('SELECT m.id, CONCAT(ma.nome," · ",m.nome," · ",t.nome) label FROM modelos m JOIN marcas ma ON ma.id=m.marca_id JOIN tipos_equipamentos t ON t.id=m.tipo_equipamento_id WHERE m.ativo=1 OR m.id=? ORDER BY label',[$current])->fetchAll();
    $label=$modules[$table]['label'];
    return query("SELECT id, `$label` label FROM `$table` WHERE ativo=1 OR id=? ORDER BY `$label`",[$current])->fetchAll();
}
function save_record(string $table, int $id, array $input): int {
    global $modules;
    $meta=$modules[$table]; $values=[];
    foreach($meta['fields'] as $key=>$f) {
        $v=$key==='password'?(string)($input[$key]??''):trim((string)($input[$key]??''));
        if(in_array($key,['cpf','cnpj'])) $v=preg_replace('/\D/','',$v);
        if($f[2] && $v==='') throw new DomainException('Preencha o campo '.$f[0].'.');
        if(isset($f[3]) && is_int($f[3]) && mb_strlen($v)>$f[3]) throw new DomainException($f[0].': texto acima do limite.');
        if(in_array($key,['cpf','cnpj']) && strlen($v)!==($key==='cpf'?11:14)) throw new DomainException($f[0].' deve conter '.($key==='cpf'?11:14).' dígitos.');
        if($f[1]==='email' && !filter_var($v,FILTER_VALIDATE_EMAIL)) throw new DomainException('Informe um e-mail válido.');
        if($f[1]==='date' && $v!=='' && (!preg_match('/^\d{4}-\d{2}-\d{2}$/',$v) || !checkdate((int)substr($v,5,2),(int)substr($v,8,2),(int)substr($v,0,4)))) throw new DomainException('Informe uma data válida.');
        if($key==='password') {
            if($v==='' && $id) continue;
            if(strlen($v)<10) throw new DomainException('Use uma senha com pelo menos 10 caracteres.');
            $v=password_hash($v,PASSWORD_DEFAULT);
        }
        $values[$key]=$v===''?null:$v;
    }
    db()->beginTransaction();
    try {
        // All mutations serialize on this tiny lock table so checks and assignments are atomic.
        query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');
        $old=$id?query("SELECT * FROM `$table` WHERE id=? FOR UPDATE",[$id])->fetch():null;
        if($id && !$old) throw new DomainException('Registro não encontrado.');
        if($table==='comodatos' && $old && !$old['ativo']) throw new DomainException('Comodatos encerrados são mantidos apenas para consulta.');
        foreach($meta['fields'] as $key=>$f) if($f[1]==='select') {
            $ref=query("SELECT id,ativo FROM `{$f[3]}` WHERE id=?",[$values[$key]])->fetch();
            if(!$ref || (!$ref['ativo'] && ($old[$key]??null)!=$values[$key])) throw new DomainException('Selecione um registro ativo em '.$f[0].'.');
        }
        foreach(['cpf','cnpj','email','patrimonio'] as $key) if(!empty($values[$key]) && query("SELECT id FROM `$table` WHERE `$key`=? AND id<>?",[$values[$key],$id])->fetch()) throw new DomainException('Já existe um registro com este '.$key.'.');
        $status=$table==='users'?'active':'ativo';
        if($table!=='comodatos') {
            $values[$status]=isset($input[$status])?1:0;
            if($table==='users' && $id===($_SESSION['user_id']??0) && !$values[$status]) throw new DomainException('Você não pode desativar sua própria conta.');
            if($old && !$values[$status]) {
                $dependencies=[
                    'comodantes'=>['comodatos','comodante_id'], 'comodatarios'=>['comodatos','comodatario_id'],
                    'marcas'=>['modelos','marca_id'], 'tipos_equipamentos'=>['modelos','tipo_equipamento_id'], 'modelos'=>['inventario','modelo_id'],
                    'inventario'=>['comodato_itens','inventario_id']
                ];
                if(isset($dependencies[$table])) { [$dt,$fk]=$dependencies[$table]; if(query("SELECT id FROM `$dt` WHERE `$fk`=? AND ativo=1 LIMIT 1",[$id])->fetch()) throw new DomainException('Este registro possui vínculos ativos. Finalize os vínculos antes de desativá-lo.'); }
            }
        }
        if($table==='inventario' && $old && $old['modelo_id']!=$values['modelo_id'] && query('SELECT id FROM comodato_itens WHERE inventario_id=? LIMIT 1',[$id])->fetch()) throw new DomainException('O modelo de um item com histórico de empréstimo não pode ser alterado.');
        $items=[];
        if($table==='comodatos') {
            if($values['data_fim'] && $values['data_fim']<$values['data_inicio']) throw new DomainException('A previsão de devolução deve ser igual ou posterior ao início.');
            $items=array_values(array_unique(array_map('intval',(array)($input['itens']??[])))); sort($items);
            if(!$items) throw new DomainException('Selecione ao menos um item do inventário.');
            foreach($items as $item) {
                $record=query('SELECT id FROM inventario WHERE id=? AND ativo=1 FOR UPDATE',[$item])->fetch();
                if(!$record) throw new DomainException('Um dos itens não está ativo no inventário.');
                if(query('SELECT id FROM comodato_itens WHERE inventario_id=? AND ativo=1 AND comodato_id<>?',[$item,$id])->fetch()) throw new DomainException('Um dos itens já está em outro comodato aberto. Atualize a seleção.');
            }
        }
        if($id) {
            $set=implode(',',array_map(fn($k)=>"`$k`=?",array_keys($values)));
            query("UPDATE `$table` SET $set".($table==='users'?',updated_at=NOW()':'')." WHERE id=?",[...array_values($values),$id]);
        } else {
            $cols=implode('`,`',array_keys($values)); $marks=implode(',',array_fill(0,count($values),'?'));
            query("INSERT INTO `$table` (`$cols`) VALUES ($marks)",array_values($values)); $id=(int)db()->lastInsertId();
        }
        if($table==='comodatos') {
            query('UPDATE comodato_itens SET ativo=0 WHERE comodato_id=?',[$id]);
            foreach($items as $item) query('INSERT INTO comodato_itens (comodato_id,inventario_id,ativo) VALUES (?,?,1) ON DUPLICATE KEY UPDATE ativo=1',[$id,$item]);
        }
        db()->commit(); return $id;
    } catch(Throwable $ex) { if(db()->inTransaction()) db()->rollBack(); throw $ex; }
}
function close_loan(int $id): void {
    db()->beginTransaction();
    try {
        query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');
        $loan=query('SELECT * FROM comodatos WHERE id=? AND ativo=1 FOR UPDATE',[$id])->fetch();
        if(!$loan) throw new DomainException('Comodato não encontrado ou já encerrado.');
        query('UPDATE comodatos SET ativo=0, devolvido_em=NOW() WHERE id=?',[$id]);
        query('UPDATE comodato_itens SET ativo=0 WHERE comodato_id=?',[$id]);
        db()->commit();
    } catch(Throwable $e) { db()->rollBack(); throw $e; }
}
function listing(string $table, string $search='', string $status='', int $page=1): array {
    global $modules;
    $params=[]; $where=[]; $joins=''; $select='a.*'; $active=$table==='users'?'active':'ativo';
    if($table==='comodatos') {
        $joins=' JOIN comodantes c ON c.id=a.comodante_id JOIN comodatarios p ON p.id=a.comodatario_id';
        $select.=', c.nome_fantasia comodante, p.nome_completo comodatario, (SELECT COUNT(*) FROM comodato_itens ci WHERE ci.comodato_id=a.id AND ci.ativo=1) itens';
        $searchCols=['CAST(a.id AS CHAR)','c.nome_fantasia','p.nome_completo'];
    } elseif($table==='inventario') {
        $joins=' JOIN modelos m ON m.id=a.modelo_id JOIN marcas ma ON ma.id=m.marca_id';
        $select.=', CONCAT(ma.nome," · ",m.nome) modelo, (SELECT c.id FROM comodato_itens ci JOIN comodatos c ON c.id=ci.comodato_id WHERE ci.inventario_id=a.id AND ci.ativo=1 AND c.ativo=1 LIMIT 1) comodato_id, (SELECT p.nome_completo FROM comodato_itens ci JOIN comodatos c ON c.id=ci.comodato_id JOIN comodatarios p ON p.id=c.comodatario_id WHERE ci.inventario_id=a.id AND ci.ativo=1 AND c.ativo=1 LIMIT 1) responsavel';
        $searchCols=['a.patrimonio','m.nome','ma.nome','CAST(a.id AS CHAR)'];
    } elseif($table==='modelos') {
        $joins=' JOIN marcas ma ON ma.id=a.marca_id JOIN tipos_equipamentos t ON t.id=a.tipo_equipamento_id'; $select.=',ma.nome marca,t.nome tipo'; $searchCols=['a.nome','ma.nome','t.nome'];
    } else { $searchCols=['a.'.$modules[$table]['label']]; }
    if($search!=='') { $where[]='('.implode(' OR ',array_map(fn($c)=>"$c LIKE ?",$searchCols)).')'; foreach($searchCols as $_) $params[]='%'.$search.'%'; }
    if(in_array($status,['0','1'],true)) { $where[]="a.`$active`=?"; $params[]=$status; }
    if($table==='comodatos' && $status==='overdue') $where[]='a.ativo=1 AND a.data_fim<CURDATE()';
    if($table==='inventario' && in_array($status,['available','loaned'],true)) $where[]='a.ativo=1 AND '.($status==='available'?'NOT ':'').'EXISTS (SELECT 1 FROM comodato_itens ci WHERE ci.inventario_id=a.id AND ci.ativo=1)';
    $base=" FROM `$table` a $joins".($where?' WHERE '.implode(' AND ',$where):'');
    $count=(int)query('SELECT COUNT(*)'.$base,$params)->fetchColumn();
    $pages=max(1,(int)ceil($count/20)); $page=min(max(1,$page),$pages); $offset=($page-1)*20;
    return [query("SELECT $select".$base." ORDER BY a.id DESC LIMIT 20 OFFSET $offset",$params)->fetchAll(),$count,$page,$pages];
}
