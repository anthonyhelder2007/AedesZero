<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Execute este script pelo terminal, não pelo navegador.\n");
}
if ($argc !== 3) {
    fwrite(STDERR, "Uso: php database/criar_admin.php USUARIO SENHA\n");
    exit(1);
}
$usuario = trim($argv[1]);
$senha = $argv[2];
if (mb_strlen($usuario) < 3 || mb_strlen($usuario) > 80 || strlen($senha) < 10) {
    fwrite(STDERR, "O usuário deve ter 3-80 caracteres e a senha no mínimo 10 caracteres.\n");
    exit(1);
}
$pdo = conectar_banco();
$stmt = $pdo->prepare('INSERT INTO administradores (usuario, senha_hash) VALUES (:usuario, :hash)');
try {
    $stmt->execute([':usuario' => $usuario, ':hash' => password_hash($senha, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        fwrite(STDERR, "Já existe um administrador com esse usuário.\n");
        exit(1);
    }
    throw $e;
}
echo "Administrador criado com sucesso.\n";
