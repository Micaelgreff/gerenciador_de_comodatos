<?php
require __DIR__.'/../bootstrap.php';
$me=user();
$modules=require __DIR__.'/../modules.php';
require __DIR__.'/../services.php';
$module=(string)($_GET['module']??'dashboard');
if($module!=='dashboard' && !isset($modules[$module])) { http_response_code(404); exit('Página não encontrada.'); }
$action=(string)($_GET['action']??'list');
$id=max(0,(int)($_GET['id']??0)); $error=null; $record=[];
if($action==='picker') serve_picker($module,$id);
try {
    if($_SERVER['REQUEST_METHOD']==='POST') {
        verify_csrf();
        if(!isset($modules[$module])) throw new DomainException('Operação inválida.');
        if(($_POST['operation']??'')==='close' && $module==='comodatos') { close_loan($id); flash('Devolução registrada. Os itens estão disponíveis novamente.'); }
        else {
            if($module==='comodatos') verify_reservation_context($id,(string)($_POST['reserva_token']??''));
            $id=save_record($module,$id,$_POST);
            if($module==='comodatos') unset($_SESSION['inventory_forms'][(string)$_POST['reserva_token']]);
            flash('Registro salvo com sucesso.');
        }
        redirect('app.php?module='.$module.'&action=edit&id='.$id);
    }
} catch(DomainException $ex) { $error=$ex->getMessage(); }
catch(PDOException $ex) { error_log('GDC database error: '.$ex->getCode()); $error='Não foi possível salvar. Verifique os vínculos e se já existe um cadastro igual.'; }
if($module!=='dashboard' && $action==='edit') {
    $record=$id?query("SELECT * FROM `$module` WHERE id=?",[$id])->fetch():[];
    if($id && !$record) { http_response_code(404); exit('Registro não encontrado.'); }
    if($error) $record=array_merge($record,$_POST);
}
$title=$module==='dashboard'?'Visão geral':$modules[$module]['title'];
require __DIR__.'/../../templates/layout.php';
