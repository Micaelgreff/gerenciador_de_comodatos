<!-- FORMULÁRIO TEMPORÁRIO PARA TESTAR AS FUNCIONALIDADES DO CRUD -->
<?php
session_start();
include('verificalogin.php');
include('..\dadoslogin.php');
include('concomodato.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar - Comodato</title>
</head>
<body>
    <?php
    // Pega o ID do comodato enviado na URL e procura no banco de dados
    $idComodato = $_GET['id'];
    $query = "SELECT nome, cpf, data, carteira, equipamento, pat, id FROM comodatos WHERE id = '$idComodato'";
    $result = mysqli_fetch_array(mysqli_query($concomodato, $query), MYSQLI_NUM); // Faz a query no MySQL e retorna os dados em formato de Array com índice numérico
    $result[2] = str_replace('/','-', $result[2]) //Converte a formatação de Data para YYYY-MM-DD
    ?>
    <form action="edit_comodato.php" method="post">
        <!-- O resultado da query é salvo como valor predefinido nos inputs -->
        <h3>DADOS COLABORADOR</h3>
        <input type="hidden" name="id" value="<?php echo $result[6] ?>"></input> <!-- Não é necessário mostrar só precisamos enviar junto com o FORM -->
        <label>NOME:</label><br>
        <input type="text" name="nome" value="<?php echo $result[0] ?>"><br>
        <label>CPF:</label><br>
        <input type="text" name="cpf" value="<?php echo $result[1] ?>"><br>
        <label>SETOR:</label><br>
        <input type="text" name="setor" value="<?php echo $result[3] ?>"><br>
        <hr>
        <h3>DADOS COMODATO:</h3>
        <label>DATA:</label><br>
        <input type="date" name="data" value="<?php echo date("Y-m-d", strtotime($result[2])) ?>"><br>
        <label>EQUIPAMENTO:</label><br>
        <input type="text" name="equipamento" value="<?php echo $result[4] ?>"><br>
        <label>PATRIMONIO:</label><br>
        <input type="text" name="patrimonio" value="<?php echo $result[5] ?>"><br><br>
        <input type="submit" name="submit" value="Atualizar">
    </form>
    <br>
    <a href = 'comodato.php'>cancelar</a>
</body>
</html>