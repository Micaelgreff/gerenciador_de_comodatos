<?php

require_once __DIR__ . '/../config/env_export.php';

$conn = new mysqli(
    DB_HOST,
    DB_USER,
    DB_PASSWORD
);

if ($conn->connect_error) {
    die("Erro ao conectar ao MySQL: " . $conn->connect_error . PHP_EOL);
}

echo "Conectado ao MySQL." . PHP_EOL;

$database = DB_DATABASE_NAME;

if (!$conn->query("
    CREATE DATABASE IF NOT EXISTS `$database`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci
")) {
    die("Erro ao criar database: " . $conn->error . PHP_EOL);
}

echo "Database '$database' criado/verificado." . PHP_EOL;
if (!$conn->select_db($database)) {
    die("Erro ao selecionar database: " . $conn->error . PHP_EOL);
}


$schema = file_get_contents(__DIR__ . '/schema.sql');
if (!$conn->multi_query($schema)) {
    die("Erro ao executar schema.sql: " . $conn->error . PHP_EOL);
}
while ($conn->more_results() && $conn->next_result()) {
}
echo "Schema executado." . PHP_EOL;


$seed = file_get_contents(__DIR__ . '/seed.sql');
if (!$conn->multi_query($seed)) {
    die("Erro ao executar seed.sql: " . $conn->error . PHP_EOL);
}
echo "Seed executado." . PHP_EOL;


$conn->close();
echo "Banco configurado com sucesso!" . PHP_EOL;