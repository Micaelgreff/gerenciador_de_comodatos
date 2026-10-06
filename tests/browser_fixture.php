<?php
// Local browser fixture: synthetic database only; never loads application configuration/.env.
if(PHP_SAPI!=='cli' && PHP_SAPI!=='cli-server') { http_response_code(404); exit; }
date_default_timezone_set('America/Sao_Paulo');
$marker=__DIR__.'/browser-fixture.json';
$mode=PHP_SAPI==='cli'?($argv[1]??''):'';
$pdo=new PDO('mysql:host=127.0.0.1;charset=utf8mb4',getenv('GDC_TEST_DB_USER')?:'root',getenv('GDC_TEST_DB_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
function db(): PDO { global $pdo; return $pdo; }
function query(string $sql,array $params=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($params); return $s; }
function e($v): string { return htmlspecialchars((string)($v??''),ENT_QUOTES,'UTF-8'); }
function csrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??''))) { http_response_code(403); exit('{"error":"CSRF inválido"}'); } }
function date_br(?string $v): string { return $v?date('d/m/Y',strtotime($v)):'Sem previsão'; }
$modules=require __DIR__.'/../app/modules.php';
require __DIR__.'/../app/services.php';
if($mode==='setup') {
    if(is_file($marker)) exit("Fixture existente. Execute cleanup antes de recriar.\n");
    $database='comodato_test_'.bin2hex(random_bytes(5));
    query("CREATE DATABASE `$database` CHARACTER SET utf8mb4"); query("USE `$database`");
    file_put_contents($marker,json_encode(['database'=>$database]));
    foreach(explode(';',file_get_contents(__DIR__.'/../database/schema.sql')) as $sql) if(trim($sql)) db()->exec($sql);
    $_SESSION=['user_id'=>0];
    $brand=save_record('marcas',0,['nome'=>'Marca Alfa','ativo'=>1]);
    $otherBrand=save_record('marcas',0,['nome'=>'Marca Beta','ativo'=>1]);
    $notebook=save_record('tipos_equipamentos',0,['nome'=>'Notebook','ativo'=>1]);
    $monitor=save_record('tipos_equipamentos',0,['nome'=>'Monitor','ativo'=>1]);
    $model=save_record('modelos',0,['nome'=>'Notebook A','marca_id'=>$brand,'tipo_equipamento_id'=>$notebook,'ativo'=>1]);
    save_record('modelos',0,['nome'=>'Monitor A','marca_id'=>$brand,'tipo_equipamento_id'=>$monitor,'ativo'=>1]);
    save_record('modelos',0,['nome'=>'Notebook B','marca_id'=>$otherBrand,'tipo_equipamento_id'=>$notebook,'ativo'=>1]);
    for($n=1;$n<=15;$n++) {
        save_record('comodantes',0,['nome_fantasia'=>sprintf('Empresa %02d',$n),'razao_social'=>'Empresa fictícia','cnpj'=>sprintf('%014d',$n),'endereco'=>'Fictício','ativo'=>1]);
        save_record('comodatarios',0,['nome_completo'=>sprintf('Pessoa %02d',$n),'cpf'=>sprintf('%011d',$n),'endereco'=>'Fictício','ativo'=>1]);
        save_record('inventario',0,['modelo_id'=>$model,'patrimonio'=>sprintf('TEST-%03d',$n),'ativo'=>1]);
    }
    echo "Fixture local criada: $database.\n"; exit;
}
if(!is_file($marker)) exit("Execute setup para criar a fixture local.\n");
$database=json_decode(file_get_contents($marker),true)['database']??'';
if(!preg_match('/^comodato_test_[a-f0-9]{10}$/',$database)) exit('Banco de teste inválido.');
if($mode==='cleanup') { query("DROP DATABASE `$database`"); unlink($marker); echo "Fixture removida.\n"; exit; }
if(PHP_SAPI==='cli') exit("Use setup, cleanup ou php -S 127.0.0.1:8765 tests/browser_fixture.php.\n");
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if(preg_match('#^/assets/(app\.js|app\.css|pickers\.css|login\.js|login\.css|vendor/sb-admin-2\.min\.css)$#',$path,$match)) {
    header('Content-Type: '.(str_ends_with($match[1],'.js')?'application/javascript':'text/css').'; charset=utf-8');
    readfile(__DIR__.'/../assets/'.$match[1]); exit;
}
if(preg_match('#^/vendor/fontawesome-free/(css/all\.min\.css|webfonts/fa-(?:brands|regular|solid)-(?:400|900)\.(?:eot|woff2?|ttf|svg))$#',$path,$match)) {
    $extension=pathinfo($match[1],PATHINFO_EXTENSION);
    header('Content-Type: '.(['css'=>'text/css','woff2'=>'font/woff2','woff'=>'font/woff','ttf'=>'font/ttf','svg'=>'image/svg+xml','eot'=>'application/vnd.ms-fontobject'][$extension]));
    readfile(__DIR__.'/..'.$path); exit;
}
if(!in_array($path,['/app.php','/','/index.php'],true)) { http_response_code(404); exit; }
query("USE `$database`");
session_name('GDC_BROWSER_FIXTURE'); session_start();
$_SESSION['csrf']??=bin2hex(random_bytes(32)); $_SESSION['user_id']=0;
if($path==='/index.php') { require __DIR__.'/../templates/login.php'; exit; }
$module=(string)($_GET['module']??'dashboard');
if($module!=='dashboard' && !isset($modules[$module])) { http_response_code(404); exit; }
$id=max(0,(int)($_GET['id']??0)); $action=(string)($_GET['action']??'list');
if($action==='picker') serve_picker($module,$id);
$error=null;$record=[];$me=['name'=>'Teste local'];
try {
    if($_SERVER['REQUEST_METHOD']==='POST') {
        verify_csrf();
        if($module==='comodatos' && ($_POST['operation']??'')==='close') close_loan($id);
        else {
            if($module==='comodatos') verify_reservation_context($id,(string)($_POST['reserva_token']??''));
            $id=save_record($module,$id,$_POST);
            if($module==='comodatos') unset($_SESSION['inventory_forms'][(string)$_POST['reserva_token']]);
        }
        $_SESSION['flash']='Registro salvo com sucesso.';
        header('Location: /app.php?module='.$module.'&action=edit&id='.$id); exit;
    }
} catch(DomainException $ex) { $error=$ex->getMessage(); }
if($module!=='dashboard' && $action==='edit') {
    $record=$id?query("SELECT * FROM `$module` WHERE id=?",[$id])->fetch():[];
    if($error) $record=array_merge($record,$_POST);
}
$title=$module==='dashboard'?'Visão geral':$modules[$module]['title'];
require __DIR__.'/../templates/layout.php';
