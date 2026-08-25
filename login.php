<?php
session_start();
include('conection.php');

if(empty($_POST['email']) || empty($_POST['senha'])){      //verificar se o email e senha estão vazios
    header('Location: index.php');                        //se estiver ele volta pra pag inicial
    exit();
}  

$usuario = mysqli_real_escape_string($conexao, $_POST['email']);  //pega o email pra var usuario
$senha = mysqli_real_escape_string($conexao, $_POST['senha']);   //pega a senha pra var senha
$passw = $_POST['senha'];

$query = "select * from users where email = '{$usuario}' and password = md5('$senha')";  //variavel de query setada
$result = mysqli_query($conexao, $query);  //pega o resultado da query
$row = mysqli_num_rows($result);   //verificar o número de linhas da query executada



if($row == 1){          //se o numeros de linhas for 1 o login está correto e o usuário pode acessar o home.php
    $_SESSION['usuario'] = $usuario;
    $_SESSION['senha'] = $senha;
    header('Location: menu.php');
    exit();
}else {
    $_SESSION["naoautentic"] = true;      //volta pra página inicial com 'login e senha invalidso'
    header('Location: index.php');
    exit();
}

?>