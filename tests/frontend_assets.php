<?php
// Checks local frontend dependencies without loading configuration or a database.
function check_frontend_assets(string $html): int {
    $root=realpath(__DIR__.'/..');
    $document=new DOMDocument();
    $previous=libxml_use_internal_errors(true);
    $document->loadHTML($html);
    libxml_clear_errors(); libxml_use_internal_errors($previous);
    $xpath=new DOMXPath($document);
    $pending=[]; $checked=[];
    foreach($xpath->query('//script[@src]/@src | //link[@rel="stylesheet"]/@href | //img[@src]/@src') as $attribute) {
        $pending[]=[$attribute->value,$root];
    }
    while($pending) {
        [$reference,$base]=array_pop($pending);
        $reference=trim($reference);
        if($reference==='' || str_starts_with($reference,'#') || preg_match('#^(?:[a-z][a-z0-9+.-]*:|//)#i',$reference)) continue;
        $relative=rawurldecode(preg_split('/[?#]/',$reference,2)[0]);
        if(preg_match('/^\.env(?:\.|$)/i',basename($relative))) throw new RuntimeException('Protected configuration reference.');
        $path=realpath((str_starts_with($relative,'/')?$root:$base).DIRECTORY_SEPARATOR.ltrim($relative,'/'));
        if(!$path || !str_starts_with($path,$root.DIRECTORY_SEPARATOR) || !is_file($path)) {
            throw new RuntimeException('Missing local frontend resource: '.$reference);
        }
        if(isset($checked[$path])) continue;
        $checked[$path]=true;
        if(strtolower(pathinfo($path,PATHINFO_EXTENSION))!=='css') continue;
        preg_match_all('/url\(\s*[\'\"]?([^\)\'\"]+)[\'\"]?\s*\)/i',file_get_contents($path),$matches);
        foreach($matches[1] as $dependency) $pending[]=[$dependency,dirname($path)];
    }
    return count($checked);
}

if(PHP_SAPI==='cli' && realpath($_SERVER['SCRIPT_FILENAME'])===__FILE__) {
    function csrf(): string { return '<input type="hidden" name="csrf" value="test">'; }
    $_SESSION=[];
    ob_start(); require __DIR__.'/../templates/login.php'; $html=ob_get_clean();
    echo 'PASS: '.check_frontend_assets($html)." login resources and CSS dependencies.\n";
}
