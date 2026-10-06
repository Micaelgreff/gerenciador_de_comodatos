<?php
// Apply only the additive reservation migration, without loading any .env file.
if(PHP_SAPI!=='cli') { http_response_code(404); exit; }
try {
    $database=getenv('GDC_MIGRATION_DATABASE')?:'comodatos';
    if(!preg_match('/^[a-zA-Z0-9_]+$/',$database)) throw new RuntimeException('Nome de banco inválido.');
    $pdo=new PDO('mysql:host='.(getenv('GDC_MIGRATION_HOST')?:'127.0.0.1').';dbname='.$database.';charset=utf8mb4',getenv('GDC_MIGRATION_USER')?:'root',getenv('GDC_MIGRATION_PASSWORD')?:'',[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $pdo->query('SELECT id FROM inventario LIMIT 0');
    foreach(explode(';',file_get_contents(__DIR__.'/migrations/003_inventory_reservations.sql')) as $sql) if(trim($sql)) $pdo->exec($sql);
    // Keep table-scoped application accounts usable. Database-wide grants already cover the new table.
    $grantQuery=$pdo->prepare("SELECT p.User,p.Host FROM mysql.tables_priv p WHERE p.Db=? AND p.Table_name='comodato_itens' AND FIND_IN_SET('Select',p.Table_priv) AND FIND_IN_SET('Insert',p.Table_priv) AND FIND_IN_SET('Update',p.Table_priv) AND EXISTS (SELECT 1 FROM mysql.tables_priv i WHERE i.User=p.User AND i.Host=p.Host AND i.Db=p.Db AND i.Table_name='inventario' AND FIND_IN_SET('Select',i.Table_priv))");
    $grantQuery->execute([$database]);
    $accounts=$grantQuery->fetchAll(PDO::FETCH_ASSOC);
    foreach($accounts as $account) $pdo->exec("GRANT SELECT, INSERT, UPDATE, DELETE ON `$database`.`inventario_reservas` TO ".$pdo->quote($account['User']).'@'.$pdo->quote($account['Host']));
    echo "Migração de reservas aplicada ao banco local $database. Nenhum cadastro foi alterado.\n";
    echo count($accounts)." conta(s) com permissões por tabela habilitada(s) para reservas.\n";
} catch(Throwable $ex) { fwrite(STDERR,"Falha na migração de reservas. Verifique a conta administrativa local e o banco. Código: ".$ex->getCode()."\n"); exit(1); }
