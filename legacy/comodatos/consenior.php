<?php
$pdo = new PDO("sqlsrv:server=172.16.56.27; database=REDESUL;", "BaseUnica", "Rede2020@");
DEFINE('HOSTSENIOR', '172.16.56.27');
DEFINE('USERSENIOR', 'BaseUnica');
DEFINE('SENHASENIOR', 'Rede2020@');
DEFINE('DBSENIOR', 'REDESUL');
DEFINE('SENIORDRIVER', 'sqlsrv');
$connectionstring_senior = array("Database" => "REDESUL", "UID" => "BaseUnica", "PWD" => "Rede2020@");
$consenior = sqlsrv_connect( "172.16.56.27", $connectionstring_senior);
?>