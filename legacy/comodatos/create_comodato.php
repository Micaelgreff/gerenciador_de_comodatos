<?php
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
if(isset($_POST['submit']))
{
    // Armazenando os dados recebidos do formulário através do método POST
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $setor = $_POST["setor"];
    $data = $_POST["data"];
    $equipamento = $_POST["equipamento"];
    $patrimonio = $_POST["patrimonio"];

    $query = "INSERT INTO comodatos (nome, cpf, data, carteira, equipamento, pat) VALUES ('$nome', '$cpf', '$data', '$setor', '$equipamento', '$patrimonio')";

    if(mysqli_query($concomodato, $query))
    {
        echo "DADOS ENVIADOS!";
        header('refresh:1.5; url=home.php');
    }
    else{
        echo "Error: " . $query . ":-" . mysqli_error($concomodato);
        echo "<br>";
        echo "Favor contatar o administrador!";
        header('refresh:5; url=comodato.php');
    }

    mysqli_close($concomodato); // Encerrando a conexão com o banco de dados
}
?>