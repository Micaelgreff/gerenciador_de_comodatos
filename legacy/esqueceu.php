<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas - Autenticação</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>
    <header>Alertas</header>
    <div id = "login-container">
        <h2>Esqueceu a senha?</h2>
        <p>Enviaremos um e-mail para redefir sua senha</p>
        <form action="reenviaemail.php" method="POST">
            <label for = "email">E-mail</label>
            <input type = "email" name = "email" id="email" placeholder = "Digite seu e-mail">
            <a href="index.php" id="forgot-pass">Voltar a página inicial</a>
            <input type = "submit" value = "Enviar">
        </form>
    </div>
</body>
</html>