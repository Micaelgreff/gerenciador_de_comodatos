<?php
// Read-only HTTP checks against local Apache, with a temporary local session.
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../app/bootstrap.php';
$id=query('SELECT id FROM users WHERE active=1 LIMIT 1')->fetchColumn();
if(!$id)exit("No active user for local HTTP smoke test.\n");
ob_start(); session_id(bin2hex(random_bytes(20))); session_init(); $_SESSION['user_id']=(int)$id;
$cookie=session_name().'='.session_id();session_write_close();
function request_local(string $path,?string $cookie=null,?array $post=null):array{
 $ch=curl_init('http://localhost/comodato/'.$path);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false]);if($cookie)curl_setopt($ch,CURLOPT_COOKIE,$cookie);if($post!==null)curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($post)]);$body=curl_exec($ch);$code=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);return [$code,$body];
}
try {
 $paths=['app.php'];foreach(array_keys(require __DIR__.'/../app/modules.php') as $m){$paths[]='app.php?module='.$m;$paths[]='app.php?module='.$m.'&action=edit';}
 foreach($paths as $path){[$code,$body]=request_local($path,$cookie);if($code!==200 || str_contains($body,'Fatal error') || str_contains($body,'Warning:'))throw new RuntimeException('HTTP failed: '.$path.' '.$code);}
 foreach(['legacy/menu.php','config/env_export.php','database/setup.php','old/index.php','doc-comodatos/teste_word.php'] as $path){if(request_local($path)[0]!==403)throw new RuntimeException('Not blocked: '.$path);}
 if(request_local('app.php')[0]!==302)throw new RuntimeException('Authentication guard failed');
 if(($csrfResponse=request_local('app.php?module=marcas',$cookie,['nome'=>'CSRF invalid test']))[0]!==403)throw new RuntimeException('CSRF guard failed: '.$csrfResponse[0].' '.strip_tags($csrfResponse[1]));
 if(request_local('app.php?module=unknown',$cookie)[0]!==404)throw new RuntimeException('Module allowlist failed');
 // Verify DML lock access without mutating data.
 db()->beginTransaction();query('SELECT id FROM app_write_lock WHERE id=1 FOR UPDATE');db()->rollBack();
 echo "PASS: 17 authenticated pages, 5 blocked legacy/internal paths, authentication, CSRF, module allowlist and runtime database lock.\n";
}finally{session_start();$_SESSION=[];session_destroy();}
