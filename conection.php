<?php
define('HOST', '127.0.0.1');
define('USUARIO', 'host_api');
define('SENHA', 'teste123!@#');
define('DB', 'comodatos');

$conexao = mysqli_connect(HOST, USUARIO, SENHA, DB) or die('Não foi possível conectar');
?>