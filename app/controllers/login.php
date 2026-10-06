<?php
require __DIR__.'/../bootstrap.php';
session_init();
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('index.php');
verify_csrf();
$email=trim((string)($_POST['email']??'')); $password=(string)($_POST['senha']??'');
$u=query('SELECT * FROM users WHERE email=? AND active=1',[$email])->fetch();
$legacy=$u && preg_match('/^[a-f0-9]{32}$/i',(string)$u['password']);
$valid=$u && ($legacy?hash_equals(strtolower($u['password']),md5($password)):password_verify($password,(string)$u['password']));
if(!$valid) { $_SESSION['naoautentic']=true; redirect('index.php'); }
if($legacy || password_needs_rehash($u['password'],PASSWORD_DEFAULT)) query('UPDATE users SET password=?, updated_at=NOW() WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$u['id']]);
query('UPDATE users SET last_access_at=NOW() WHERE id=?',[$u['id']]);
session_regenerate_id(true); $_SESSION=['user_id'=>(int)$u['id'],'csrf'=>bin2hex(random_bytes(32))];
redirect('app.php');
