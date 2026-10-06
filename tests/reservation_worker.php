<?php
// A second PDO connection for an actual concurrent race in the isolated test database.
if(PHP_SAPI!=='cli') exit;
[$script,$database,$item,$token]=$argv;
if(!preg_match('/^comodato_test_[a-f0-9]{10}$/',$database) || !preg_match('/^[a-f0-9]{64}$/',$token)) exit(1);
$pdo=new PDO('mysql:host=127.0.0.1;dbname='.$database.';charset=utf8mb4',getenv('GDC_TEST_DB_USER')?:'root',getenv('GDC_TEST_DB_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
function db(): PDO { global $pdo; return $pdo; }
function query(string $sql,array $params=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($params); return $s; }
require __DIR__.'/../app/services.php';
echo "ready\n"; fflush(STDOUT);
if(trim((string)fgets(STDIN))!=='go') exit(1);
try { reserve_equipment($token,0,(int)$item); echo "reserved\n"; }
catch(DomainException $ex) { echo "conflict\n"; exit(2); }
