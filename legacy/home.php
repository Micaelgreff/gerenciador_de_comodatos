<?php
session_start();
include('verificalogin.php');
include('dadoslogin.php');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Bem-vindo!</title>
    <link rel="stylesheet" href="css\paginicial.css">
</head>
<body>
    <div class = "fundo">
        <header class = "headerin">
            <h1>Olá <?php echo $nome; ?>, seja bem-vindo</h1>
                <nav>
                    <ul class = "menuin">
                        <li><a href = "comodatos\comodato.php">Comodatos</a></li>
                        <li><a href = '/'>Informações</a></li>
                        <li><a href = '/'>Meu perfil</a></li>
                        <li><a href = 'logout.php'>Sair</a></li>
                    </ul>
                </nav>
        </header>
        <section class = "boards">
            <div class = "board">
                <div class = "card">
                    <a href="comodatos\comodato.php" id = "comodato"><img alt="comodato" src = "imagens\consulta_comodato.png">Consultar Comodatos</a>
                </div>
            </div>
            <div class = "board">
                <div class = "card" id = "info">
                    <a href = "comodatos\form_criar.php" id = "bcriacomodato"><img alt="comodato" src = "imagens\comodato.png">Criar Comodato</a>
                </div>
            </div>
        </section>
    </div>
    <script src="cards.js" defer></script>
</body>
</html>

