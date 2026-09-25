<!-- FORMULÁRIO TEMPORÁRIO PARA TESTAR AS FUNCIONALIDADES DO CRUD -->
<?php
session_start();
include('verificalogin.php');
include('..\dadoslogin.php');
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inserir - Comodato</title>
</head>
<body>
    <form action="create_comodato.php" method="post">
        <h3>DADOS COLABORADOR</h3>
        <label>NOME:</label><br>
        <input type="text" name="nome"><br>
        <label>CPF:</label><br>
        <input type="text" name="cpf"><br>
        <label>SETOR:</label><br>
        <input type="text" name="setor"><br>
        <hr>
        <h3>DADOS COMODATO:</h3>
        <label>DATA:</label><br>
        <input type="date" name="data"><br>
        <label>EQUIPAMENTO:</label><br>
        <input type="text" name="equipamento"><br>
        <label>PATRIMONIO:</label><br>
        <input type="text" name="patrimonio"><br>
        <input type="submit" name="submit" value="Cadastrar">
    </form>
</body>
</html>