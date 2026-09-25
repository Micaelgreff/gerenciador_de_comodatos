<?php
session_start();
include('conection.php');

if(empty($_POST['cemail']) || empty($_POST['csenha']) || empty($_POST['cfirst_name']) || empty($_POST['clast_name'])){      //verificar se o email, senha e nome estão vazios
    //header('Location: cadUsuario.php');        //se estiver ele volta pra pag inicial
    //exit();
    echo "nao foi";
}  

$cadnome = mysqli_real_escape_string($conexao, $_POST['cfirst_name'] . $_POST['clast_name']);  //pega o email pra var usuario
//$cadsobrenome = mysqli_real_escape_string($conexao, $_POST['clast_name']);
$cademail = mysqli_real_escape_string($conexao, $_POST['cemail']);
$senha = $_POST['csenha'];   //pega a senha pra var senha


$caduser = "INSERT INTO users (name, email, password) VALUES ('$cadnome', '$cademail', md5('$senha'))";  //variavel de query setada
if ($conexao->query($caduser) === TRUE) {
    echo "Usuário cadastrado com sucesso!";
  } else {
    echo "Error: " . $caduser . "<br>" . $conexao->error;
    exit();
  }
?>