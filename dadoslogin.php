
<?php
if(!isset($_SESSION)){
    session_start();
}
include("conection.php");
$usuario = $_SESSION['usuario'];  //pega o email que é o login
$senha = $_SESSION['senha'];   //pega a senha da sessão
$querynome = mysqli_query($conexao, "select name from users where email = '{$usuario}' and password = md5('$senha')");  //query pra selecionar nome
while($row = mysqli_fetch_array($querynome)){  //while pra pesquisar as rows da query, vai pegar todos os dados da consulta
    $nome = $row["name"];               //var nome recebe o que estiver no campo nome. só serve se só tive 1 row, se não ia sobreescrever
    $snome = "";
}

?>