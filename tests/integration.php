<?php
// Local, isolated integration tests. Never uses application credentials or production data.
if(PHP_SAPI!=='cli') exit;
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
 echo "PASS: $checks checks.\n";
}finally{if(db()->inTransaction())db()->rollBack();if(preg_match('/^comodato_test_[a-f0-9]{10}$/',$database))query("DROP DATABASE `$database`");}
