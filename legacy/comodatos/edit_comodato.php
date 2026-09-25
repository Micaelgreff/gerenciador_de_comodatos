<?php
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
include('verificalogin.php');
if(isset($_POST['submit']))
{
    // Armazenando os dados recebidos do formulário através do método POST
    $nome = $_POST["nome"];
    $cpf = $_POST["cpf"];
    $setor = $_POST["setor"];
    $data = $_POST["data"];
    $equipamento = $_POST["equipamento"];
    $patrimonio = $_POST["patrimonio"];
    $id = $_POST["id"];

    $data = date_create($data); //Pega a string e transforma em data
    $data = date_format($data, 'd/m/Y'); //converte o formato da data para DD/MM/YYYY
    $query = "UPDATE comodatos SET nome = '$nome', cpf = '$cpf', data = '$data', carteira = '$setor', equipamento = '$equipamento', pat = '$patrimonio' WHERE id = '$id'";

    if(mysqli_query($concomodato, $query))
    {
        echo "DADOS ATUALIZADOS!";
        header('refresh:1.5; url=comodato.php');
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