<?php
$loginAssetVersions=[];
foreach(['login.css','login.js','vendor/sb-admin-2.min.css'] as $asset) $loginAssetVersions[$asset]=substr(hash_file('sha256',__DIR__.'/../assets/'.$asset),0,12);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>GDC - Login</title>

    <!-- Fontes e Ícones -->

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- CSS Bootstrap 4 / SB Admin 2 -->
    <link href="assets/vendor/sb-admin-2.min.css?v=<?= $loginAssetVersions['vendor/sb-admin-2.min.css'] ?>" rel="stylesheet">
    <link rel="stylesheet" href="assets/login.css?v=<?= $loginAssetVersions['login.css'] ?>">
</head>
<body class="bg-gradient-primary body-login">

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">

    <!-- Outer Row -->
    <div class="row justify-content-center w-100">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg login-card">
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-lg-6 d-none d-lg-block bg-login-image">
                            <div class="brand-overlay d-flex flex-column justify-content-between p-5 h-100 text-white">
                                <div class="brand-logo">
                                    <i class="fas fa-cubes fa-2x"></i>
                                </div>
                                <div class="brand-text">
                                    <h2 class="font-weight-bold mb-2">GDC</h2>
                                    <p class="text-white-50 small mb-0">Gestão e Controle Simplificados</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 bg-white">
                            <div class="p-5 d-flex flex-column justify-content-center h-100">

                                <div class="text-center mb-4">
                                    <h1 class="h4 text-gray-900 font-weight-bold mb-1">Bem vindo de volta!</h1>
                                    <p class="text-muted small">Acesse sua conta para continuar</p>
                                </div>
                                <form class="user" action="login.php" method="POST"><?= csrf() ?><?php if(!empty($_SESSION["naoautentic"])): ?><div class="alert alert-danger" role="alert">E-mail ou senha inválidos, ou conta inativa.</div><?php unset($_SESSION["naoautentic"]); endif ?>
                                    <div class="form-group mb-3">
                                        <label for="email" class="small text-muted font-weight-bold">Endereço de E-mail</label>
                                        <div class="input-group-custom">
                                            <i class="fas fa-envelope input-icon"></i>
                                            <input type="email" name="email" class="form-control form-control-modern"
                                                id="email" aria-describedby="emailHelp"
                                                placeholder="seu.email@empresa.com" required autocomplete="email">
                                        </div>
                                    </div>

                                    <div class="form-group mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label for="senha" class="small text-muted font-weight-bold">Senha</label>

                                        </div>
                                        <div class="input-group-custom">
                                            <i class="fas fa-lock input-icon"></i>
                                            <input type="password" name="senha" class="form-control form-control-modern"
                                                id="senha" placeholder="Sua senha" autocomplete="current-password" required>
                                            <button type="button" aria-label="Mostrar ou ocultar senha" class="btn-toggle-password" data-toggle-password aria-pressed="false">
                                                <i class="fas fa-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>

                                    </div>



                                    <input type="submit" value="Entrar" class="btn btn-primary btn-block btn-modern font-weight-bold py-2 shadow-sm">
                                </form>

                                <hr class="my-4">

                                <div class="text-center">
                                    <span class="small text-muted">Para obter acesso ou redefinir sua senha, procure o responsável pelo sistema.</span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script src="assets/login.js?v=<?= $loginAssetVersions['login.js'] ?>" defer></script>
</body>
</html>
