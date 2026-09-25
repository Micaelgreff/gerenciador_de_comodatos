<?php
if(!isset($_SESSION)){
    session_start();
}
if(!$_SESSION['usuario']){
    header('Location: ..\index.php');
    exit();
}
?>