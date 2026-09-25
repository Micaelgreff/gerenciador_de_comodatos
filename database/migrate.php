<?php
if(PHP_SAPI!=='cli') { http_response_code(404); exit; }
require __DIR__.'/../config/env_export.php';
try {
 $pdo=new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE_NAME.';charset=utf8mb4',getenv('GDC_MIGRATION_USER')?:DB_USER,getenv('GDC_MIGRATION_PASSWORD')!==false?getenv('GDC_MIGRATION_PASSWORD'):DB_PASSWORD,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 foreach(glob(__DIR__.'/migrations/*.sql') as $file) {
  foreach(explode(';',file_get_contents($file)) as $sql) { $sql=trim(ltrim($sql,"\xEF\xBB\xBF")); if($sql!=='')$pdo->exec($sql); }
  echo basename($file)." aplicada.\n";
 }
} catch(PDOException $e) { fwrite(STDERR,"Falha na migração. Use uma conta local com permissão ALTER/CREATE via GDC_MIGRATION_USER e GDC_MIGRATION_PASSWORD. Código: ".$e->getCode()."\n");exit(1); }
