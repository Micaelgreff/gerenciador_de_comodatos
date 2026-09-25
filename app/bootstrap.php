<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/env_export.php';
date_default_timezone_set('America/Sao_Paulo');
function db(): PDO {
    static $db;
    return $db ??= new PDO('mysql:host='.DB_HOST.';dbname='.DB_DATABASE_NAME.';charset=utf8mb4', DB_USER, DB_PASSWORD, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES=>false]);
}
function query(string $sql, array $params=[]): PDOStatement { $s=db()->prepare($sql); $s->execute($params); return $s; }
function e($value): string { return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8'); }
function redirect(string $url): never { header('Location: '.$url); exit; }
function session_init(): void {
    if(session_status()!==PHP_SESSION_ACTIVE) { session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!=='off']); session_start(); }
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
}
function csrf(): string { return '<input type="hidden" name="csrf" value="'.e($_SESSION['csrf']).'">'; }
function verify_csrf(): void { if(!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(403); exit('Sessão expirada. Volte e atualize a página.'); } }
function user(): array {
    session_init();
    $u=query('SELECT id,name,email FROM users WHERE id=? AND active=1', [$_SESSION['user_id'] ?? 0])->fetch();
    if(!$u) { unset($_SESSION['user_id']); redirect('index.php'); }
    return $u;
}
function flash(string $message): void { $_SESSION['flash']=$message; }
function date_br(?string $date): string { return $date ? date('d/m/Y', strtotime($date)) : 'Sem previsão'; }
