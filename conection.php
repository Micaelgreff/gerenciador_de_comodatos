<?php
require_once __DIR__ . '/config/env_export.php';

$conexao = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE_NAME) or die('Não foi possível conectar');
?>