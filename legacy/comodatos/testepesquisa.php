<?php
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
include('verificalogin.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisar colaborador</title>
</head>
<body>
    <form action="resultpesquisa.php" method="GET">
        <h3>Pesquisa de Matrícula</h3>
        <label>Matricula:</label><br>
        <input type="text" name="matricula" placeholder = "Matricula"><br>
        <label>Endereço:</label><br>
        <input type="text" name="adress" placeholder = "Endereços"><br>
        <input type="submit" name="submit" value="Pesquisar">
    </form>
</br>
</br>
<h1> Resposta aparece aqui: </h1>
</body>
</html>