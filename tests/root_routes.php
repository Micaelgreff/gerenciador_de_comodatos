<?php
// Local Apache routing checks. Never follows redirects or loads application configuration.
if(PHP_SAPI!=='cli') exit;
$targets=[
    'home.php'=>'app.php', 'menu.php'=>'app.php', 'dadoslogin.php'=>'app.php',
    'verificalogin.php'=>'app.php', 'esqueceu.php'=>'index.php',
    'cadastra_comodato.php'=>'app.php?module=comodatos',
    'cadComodato.php'=>'app.php?module=comodatos&action=edit',
    'cadastrauser.php'=>'app.php?module=users',
    'cadUsuario.php'=>'app.php?module=users&action=edit',
];
function root_route_response(string $path,bool $post=false): array {
    $curl=curl_init('http://127.0.0.1/comodato/'.$path);
    $location=null;
    curl_setopt_array($curl,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>10,
        CURLOPT_HEADERFUNCTION=>static function($curl,string $line) use (&$location): int {
            if(str_starts_with(strtolower($line),'location:')) $location=trim(substr($line,9));
            return strlen($line);
        }]);
    if($post) curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>'unused=test']);
    $result=curl_exec($curl);
    if($result===false) throw new RuntimeException('Local Apache connection failed: '.curl_error($curl));
    $code=curl_getinfo($curl,CURLINFO_HTTP_CODE); curl_close($curl);
    return [$code,$location];
}
$checks=0;
foreach(['index.php'=>'login-page.php','app.php'=>'application.php','login.php'=>'login.php','logout.php'=>'logout.php'] as $entry=>$controller) {
    $expected="<?php\nrequire __DIR__.'/app/controllers/".$controller."';\n";
    $source=file_get_contents(__DIR__.'/../'.$entry);
    if(str_replace("\r\n","\n",$source)!==$expected) throw new RuntimeException('Public entry contains unexpected logic: '.$entry);
    $controllerPath=__DIR__.'/../app/controllers/'.$controller;
    if(!is_file($controllerPath)) throw new RuntimeException('Missing controller: '.$controller);
    preg_match_all('/require\s+__DIR__\s*\.\s*\'([^\']+)\'/',$source."\n".file_get_contents($controllerPath),$includes);
    foreach($includes[1] as $dependency) {
        $base=str_starts_with($dependency,'/app/controllers/')?__DIR__.'/..':dirname($controllerPath);
        if(!is_file($base.$dependency)) throw new RuntimeException('Broken controller dependency: '.$dependency);
    }
    $checks++;
}
foreach($targets as $source=>$target) foreach([false,true] as $post) {
    [$code,$location]=root_route_response($source.'?old_parameter=discard',$post);
    $expected='/comodato/'.$target;
    $actual=parse_url((string)$location,PHP_URL_PATH);
    $query=parse_url((string)$location,PHP_URL_QUERY);
    if($query!==null) $actual.='?'.$query;
    if($code!==303 || $actual!==$expected) throw new RuntimeException('Compatibility route failed: '.$source.' '.$code.' '.$location);
    $checks++;
}
foreach(['app/controllers/application.php','app/controllers/login-page.php','app/controllers/login.php','app/controllers/logout.php','doc-comodatos/bootstrap.php'] as $path) {
    if(root_route_response($path)[0]!==403) throw new RuntimeException('Internal route is not blocked: '.$path);
    $checks++;
}
echo "PASS: $checks local routing checks; entry points, redirects and internal controller protection verified.\n";
