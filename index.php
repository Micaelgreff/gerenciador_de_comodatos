<?php
require __DIR__.'/app/bootstrap.php';
session_init();
if(isset($_SESSION['user_id'])) redirect('app.php');
require __DIR__.'/templates/login.php';
