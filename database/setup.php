<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

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
if ($seed === false) {
    die("Erro ao ler seed.sql." . PHP_EOL);
}
if (trim($seed) !== '') {
    if (!$conn->multi_query($seed)) {
        die("Erro ao executar seed.sql: " . $conn->error . PHP_EOL);
    }
    while ($conn->more_results() && $conn->next_result()) {
    }
    echo "Seed executado." . PHP_EOL;
} else {
    echo "seed.sql vazio; execução ignorada." . PHP_EOL;
}

$name = MASTER_USER;
$email = MASTER_EMAIL;
$password = password_hash(MASTER_PASSWORD, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password) SELECT ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM users WHERE name = ?)');
if (!$stmt) {
    die("Erro ao preparar seed do usuário master: " . $conn->error . PHP_EOL);
}
$stmt->bind_param('ssss', $name, $email, $password, $name);
if (!$stmt->execute()) {
    die("Erro ao inserir usuário master: " . $stmt->error . PHP_EOL);
}
echo ($stmt->affected_rows > 0 ? "Usuário master criado." : "Usuário master já existe.") . PHP_EOL;
$stmt->close();


$conn->close();
echo "Banco configurado com sucesso!" . PHP_EOL;
