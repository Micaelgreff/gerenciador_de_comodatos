<?php
session_start();
include('comodatos/concomodato.php');

if(empty($_POST['nomecomodato']) || empty($_POST['cpfcomodato']) || empty($_POST['dataentregacomodato']) || empty($_POST['equipamentocomodato'])){      //verificar se o email, senha e nome estão vazios
    //header('Location: cadUsuario.php');        //se estiver ele volta pra pag inicial
    //exit();
    echo "nao foi";
}  

$nomecomodato = mysqli_real_escape_string($concomodato, $_POST['nomecomodato']);  //pega o email pra var usuario
$cpfcomodato = mysqli_real_escape_string($concomodato, $_POST['cpfcomodato']);
$dataentregacomodato = mysqli_real_escape_string($concomodato, $_POST['dataentregacomodato']);
$carteiracomodato = mysqli_real_escape_string($concomodato, $_POST['carteiracomodato']);
$equipamentocomodato = mysqli_real_escape_string($concomodato, $_POST['equipamentocomodato']);
$patrimoniocomodato = mysqli_real_escape_string($concomodato, $_POST['patrimoniocomodato']);

$insertcomodato = "INSERT INTO comodatos (nome, cpf, data, carteira, equipamento, pat) VALUES ('$nomecomodato', '$cpfcomodato', '$dataentregacomodato', '$carteiracomodato', '$equipamentocomodato', '$patrimoniocomodato')";  //variavel de query setada
if ($concomodato->query($insertcomodato) === TRUE) {
    echo "comodato cadastrado com sucesso!";
  } else {
    echo "Error: " . $insertcomodato . "<br>" . $concomodato->error;
    exit();
  }
?>