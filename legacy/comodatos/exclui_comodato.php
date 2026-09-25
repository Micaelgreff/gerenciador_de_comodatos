<?php
include('concomodato.php'); // Arquivo contendo a variável com os dados da conexão ao banco de dados
if(isset($_POST['submit'])){
    $id = $_GET["id"];
    $query = "DELETE from comodatos where id = '$id'";
    if(mysqli_query($concomodato, $query))
    {
        echo "Registro de Comodato Deletado!";
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