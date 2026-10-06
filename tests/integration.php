<?php
// Local, isolated integration tests. Never uses application credentials or production data.
if(PHP_SAPI!=='cli') exit;
require __DIR__.'/frontend_assets.php';
$pdo=new PDO('mysql:host=localhost;charset=utf8mb4',getenv('GDC_TEST_DB_USER')?:'root',getenv('GDC_TEST_DB_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
$database='comodato_test_'.bin2hex(random_bytes(5));
function db(): PDO { global $pdo; return $pdo; }
function query(string $sql,array $params=[]): PDOStatement {$s=db()->prepare($sql);$s->execute($params);return $s;}
function e($v):string{return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8');}
function csrf():string{return '<input type="hidden" name="csrf" value="test">';}
function date_br(?string $v):string{return $v?date('d/m/Y',strtotime($v)):'Sem previsão';}
$checks=0;
function check(bool $ok,string $message):void{global $checks;if(!$ok)throw new RuntimeException($message);$checks++;echo "OK: $message\n";}
function rejects(callable $fn,string $message):void{try{$fn();}catch(DomainException $ex){check(true,$message);return;}throw new RuntimeException('Expected rejection: '.$message);}
try {
 query("CREATE DATABASE `$database` CHARACTER SET utf8mb4");query("USE `$database`");
 $sql=file_get_contents(__DIR__.'/../database/schema.sql');
 foreach(explode(';',$sql) as $statement)if(trim($statement))db()->exec($statement);
 query('ALTER TABLE users MODIFY password VARCHAR(255)');
 foreach(explode(';',file_get_contents(__DIR__.'/../database/migrations/002_loan_control.sql')) as $s)if(trim($s))db()->exec(ltrim($s,"\xEF\xBB\xBF"));
 $modules=require __DIR__.'/../app/modules.php';require __DIR__.'/../app/services.php';
 $_SESSION=['user_id'=>0];
 $company=['nome_fantasia'=>'Empresa de teste','razao_social'=>'Empresa de teste LTDA','cnpj'=>'00000000000100','endereco'=>'Endereço fictício','ativo'=>1];
 $owner=save_record('comodantes',0,$company);
 $person=save_record('comodatarios',0,['nome_completo'=>'Colaborador de teste','cpf'=>'00000000000','endereco'=>'Endereço fictício','ativo'=>1]);
 $brand=save_record('marcas',0,['nome'=>'Fabricante de teste','ativo'=>1]);
 $type=save_record('tipos_equipamentos',0,['nome'=>'Notebook','ativo'=>1]);
 $model=save_record('modelos',0,['nome'=>'Modelo de teste','marca_id'=>$brand,'tipo_equipamento_id'=>$type,'ativo'=>1]);
 $itemInput=['modelo_id'=>$model,'patrimonio'=>'TEST-001','ativo'=>1];
 $assetId=$item=save_record('inventario',0,$itemInput);
 $account=save_record('users',0,['name'=>'Técnico de teste','email'=>'teste@example.invalid','password'=>'test-password-123','active'=>1]);
 check(password_verify('test-password-123',query('SELECT password FROM users WHERE id=?',[$account])->fetchColumn()),'Modern password hash');
 $loanInput=['comodante_id'=>$owner,'comodatario_id'=>$person,'data_inicio'=>'2026-01-01','data_fim'=>'2026-02-01','observacao'=>'Teste local','itens'=>[$item]];
 $loan=save_record('comodatos',0,$loanInput);
 check((int)query('SELECT COUNT(*) FROM comodato_itens WHERE ativo=1')->fetchColumn()===1,'Loan assigns one physical item');
 rejects(fn()=>save_record('comodatos',0,$loanInput),'Cannot double lend an item');
 check((int)query('SELECT COUNT(*) FROM comodatos')->fetchColumn()===1,'Rejected loan rolls back');
 rejects(fn()=>save_record('inventario',0,$itemInput),'Duplicate asset rejected');
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['data_fim'=>'2025-01-01'])),'Invalid date range rejected');
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['data_inicio'=>'2026-02-30'])),'Invalid calendar date rejected');
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['itens'=>[]])),'Empty loan rejected');
 rejects(fn()=>save_record('inventario',$item,array_diff_key($itemInput,['ativo'=>1])),'Lent item cannot be deactivated');
 rejects(fn()=>save_record('comodantes',$owner,array_diff_key($company,['ativo'=>1])),'Company with open loan cannot be deactivated');
 $_SESSION['user_id']=$account;
 rejects(fn()=>save_record('users',$account,['name'=>'Teste','email'=>'teste@example.invalid']),'Cannot disable own account');
 $saved=save_record('comodatos',$loan,$loanInput);check($saved===$loan,'Loan edit keeps identity');
 foreach(array_keys($modules) as $module){[$rows,$count]=listing($module);check($count===1 && count($rows)===1,"List $module");}
 check(listing('inventario','','available')[1]===0,'Lent asset unavailable');
 check(listing('inventario','','loaned')[1]===1,'Lent filter works');
 check(listing('comodatos','','overdue')[1]===1,'Overdue filter works');
 check(listing('users',"' OR 1=1 --")[1]===0,'Search parameters cannot change query');
 // Render every template with synthetic data and convert warnings to failures.
 set_error_handler(function($severity,$message,$file,$line){throw new RuntimeException("$message ($file:$line)");});
 $me=['name'=>'Técnico de teste']; $error=null;
 foreach(['dashboard',...array_keys($modules)] as $module){foreach($module==='dashboard'?['list']:['list','edit','new'] as $mode){
  $action=$mode==='new'?'edit':$mode;$id=$mode==='edit'?1:0;$record=$id?query("SELECT * FROM `$module` WHERE id=?",[$id])->fetch():[];
  $_GET=[];$title=$module==='dashboard'?'Visão geral':$modules[$module]['title'];ob_start();require __DIR__.'/../templates/layout.php';$html=ob_get_clean();check(str_contains($html,'</html>'),"Render $module/$mode");
  check(check_frontend_assets($html)>0,"Frontend resources $module/$mode");
  if(getenv('GDC_TEST_PREVIEW') && $module==='dashboard')file_put_contents(__DIR__.'/../preview-test.html',$html);
 }}
 restore_error_handler();
 close_loan($loan);
 check((int)query('SELECT COUNT(*) FROM comodato_itens WHERE ativo=1')->fetchColumn()===0,'Return releases inventory');
 check((bool)query('SELECT devolvido_em FROM comodatos WHERE id=?',[$loan])->fetchColumn(),'Return timestamp saved');
 rejects(fn()=>close_loan($loan),'Repeated return rejected');
 rejects(fn()=>save_record('comodatos',$loan,$loanInput),'Closed loan immutable');
 $newLoan=save_record('comodatos',0,$loanInput);check($newLoan!==$loan,'Returned item can be lent again');
 check((int)query('SELECT COUNT(*) FROM comodato_itens WHERE inventario_id=?',[$assetId])->fetchColumn()===2,'Loan history retained');
 // Cascading catalog, bounded pickers and temporary reservations use synthetic records only.
 check(listing('inventario')[0][0]['tipo']==='Notebook','Inventory includes equipment type');
 check(listing('inventario','Notebook')[1]===1,'Inventory search includes type');
 rejects(fn()=>save_record('inventario',0,array_replace($itemInput,['patrimonio'=>'TEST-BAD','inventario_marca_id'=>$brand+100,'inventario_tipo_id'=>$type])),'Incompatible brand/model rejected');
 rejects(fn()=>save_record('inventario',0,array_replace($itemInput,['patrimonio'=>'TEST-BAD','inventario_marca_id'=>$brand,'inventario_tipo_id'=>$type+100])),'Incompatible type/model rejected');
 close_loan($newLoan);
 $_SESSION=['user_id'=>$account]; $tokenA=reservation_context(0); $sessionA=$_SESSION;
 $_SESSION=['user_id'=>$account]; $tokenB=reservation_context(0);
 rejects(fn()=>verify_reservation_context(0,$tokenA),'Form token bound to originating session');
 verify_reservation_context(0,$tokenB); check(true,'Own form token accepted');
 rejects(fn()=>verify_reservation_context(123,$tokenB),'Form token bound to loan');
 reserve_equipment($tokenA,0,$item);
 check(count(picker_results('inventario','',0,$tokenA))===1,'Owner can see reserved equipment');
 check(count(picker_results('inventario','',0,$tokenB))===0,'Other session cannot see reserved equipment');
 rejects(fn()=>reserve_equipment($tokenB,0,$item),'Second session cannot reserve selected equipment');
 rejects(fn()=>save_record('comodatos',0,$loanInput),'Direct save cannot bypass another session reservation');
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['reserva_token'=>$tokenB])),'Foreign reserved item cannot be saved');
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['reserva_token'=>$tokenA,'itens'=>[$item,$item]])),'Repeated item rejected instead of silently deduplicated');
 check(renew_equipment($tokenA,0,[$item])===[],'Owner renews reservation');
 release_equipment($tokenB,$item);
 check((string)query('SELECT token FROM inventario_reservas WHERE inventario_id=?',[$item])->fetchColumn()===$tokenA,'Other session cannot release reservation');
 query('UPDATE inventario_reservas SET expira_em=DATE_SUB(NOW(),INTERVAL 1 SECOND) WHERE inventario_id=?',[$item]);
 rejects(fn()=>save_record('comodatos',0,array_replace($loanInput,['reserva_token'=>$tokenA])),'Expired reservation cannot be saved');
 reserve_equipment($tokenB,0,$item);
 check(renew_equipment($tokenA,0,[$item])===[$item],'Expired owner cannot renew new owner reservation');
 release_equipment($tokenA);
 check((string)query('SELECT token FROM inventario_reservas WHERE inventario_id=?',[$item])->fetchColumn()===$tokenB,'Stale release preserves new owner');
 $reservedLoan=save_record('comodatos',0,array_replace($loanInput,['reserva_token'=>$tokenB]));
 check((int)query('SELECT COUNT(*) FROM inventario_reservas WHERE token=?',[$tokenB])->fetchColumn()===0,'Saving consumes reservations');
 close_loan($reservedLoan);
 for($n=2;$n<=15;$n++) save_record('inventario',0,array_replace($itemInput,['patrimonio'=>sprintf('TEST-%03d',$n)]));
 for($n=2;$n<=15;$n++) {
     save_record('comodantes',0,array_replace($company,['nome_fantasia'=>sprintf('Empresa %02d',$n),'cnpj'=>sprintf('%014d',$n)]));
     save_record('comodatarios',0,['nome_completo'=>sprintf('Pessoa %02d',$n),'cpf'=>sprintf('%011d',$n),'endereco'=>'Fictício','ativo'=>1]);
 }
 foreach(['comodantes','comodatarios','inventario'] as $kind) check(count(picker_results($kind,'',0,$tokenA))===10,"Picker $kind capped at ten results");
 check(count(picker_results('inventario','TEST-015',0,$tokenA))===1,'Equipment search reaches beyond initial ten');
 check(count(picker_results('comodantes','Empresa 15'))===1,'Company search reaches beyond initial ten');
 check(count(picker_results('comodatarios','Pessoa 15'))===1,'Borrower search reaches beyond initial ten');
 check(count(picker_results('inventario','TEST-015',0,$tokenA,[15]))===0,'Other rows excluded on server');
 check(count(picker_results('inventario',"' OR 1=1 --",0,$tokenA))===0,'Remote search uses SQL parameters');
 reserve_equipment($tokenA,0,1); reserve_equipment($tokenB,0,2);
 rejects(fn()=>reserve_equipment($tokenA,0,2,1),'Conflicting replacement rejected');
 check(query('SELECT token FROM inventario_reservas WHERE inventario_id=1')->fetchColumn()===$tokenA,'Failed replacement retains original reservation');
 reserve_equipment($tokenA,0,3,1);
 check(!query('SELECT token FROM inventario_reservas WHERE inventario_id=1')->fetchColumn(),'Successful replacement releases previous item');
 release_equipment($tokenA,3); check(!query('SELECT token FROM inventario_reservas WHERE inventario_id=3')->fetchColumn(),'Removing row releases equipment');
 release_equipment($tokenB);
 $otherType=save_record('tipos_equipamentos',0,['nome'=>'Monitor','ativo'=>1]);
 $otherModel=save_record('modelos',0,['nome'=>'Tela de teste','marca_id'=>$brand,'tipo_equipamento_id'=>$otherType,'ativo'=>1]);
 $otherBrand=save_record('marcas',0,['nome'=>'Outra marca','ativo'=>1]);
 save_record('modelos',0,['nome'=>'Outro fabricante','marca_id'=>$otherBrand,'tipo_equipamento_id'=>$type,'ativo'=>1]);
 $catalog=inventory_models($brand);
 check(count($catalog)===2 && count(array_unique(array_column($catalog,'tipo_equipamento_id')))===2,'Brand catalog supports both type-first and model-first selection');
 check(count(inventory_models($otherBrand))===1,'Catalog isolates selected brand');
 query('UPDATE modelos SET ativo=0 WHERE id=?',[$otherModel]);
 check(count(inventory_models($brand))===1 && count(inventory_models($brand,$otherModel))===2,'Inactive model retained only for existing selection');
 $module='comodatos';$action='edit';$id=$reservedLoan;$record=query('SELECT * FROM comodatos WHERE id=?',[$id])->fetch();$error=null;
 ob_start();require __DIR__.'/../templates/form.php';$closedHtml=ob_get_clean();
 check(str_contains($closedHtml,'fieldset disabled') && !str_contains($closedHtml,'class="add-equipment"'),'Closed loans remain read-only');
 $workers=[];
 foreach([$tokenA,$tokenB] as $raceToken) {
     $pipes=[];
     $process=proc_open([PHP_BINARY,__DIR__.'/reservation_worker.php',$database,'4',$raceToken],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
     if(!is_resource($process)) throw new RuntimeException('Unable to start race worker');
     $workers[]=[$process,$pipes];
 }
 foreach($workers as [$process,$pipes]) if(trim((string)fgets($pipes[1]))!=='ready') throw new RuntimeException('Race worker not ready: '.stream_get_contents($pipes[2]));
 foreach($workers as [$process,$pipes]) { fwrite($pipes[0],"go\n"); fclose($pipes[0]); }
 $results=[];
 foreach($workers as [$process,$pipes]) {
     $output=trim(stream_get_contents($pipes[1]));$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
     $code=proc_close($process);if(!in_array($code,[0,2],true)) throw new RuntimeException('Race worker failed: '.$errors);$results[]=$output;
 }
 sort($results);check($results===['conflict','reserved'],'Concurrent connections: exactly one session reserves equipment');
 check((int)query('SELECT COUNT(*) FROM inventario_reservas WHERE inventario_id=4 AND expira_em>NOW()')->fetchColumn()===1,'Concurrent reservation retains one owner');
 echo "PASS: $checks checks.\n";
}finally{if(db()->inTransaction())db()->rollBack();if(preg_match('/^comodato_test_[a-f0-9]{10}$/',$database))query("DROP DATABASE `$database`");}
