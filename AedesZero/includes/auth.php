<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function iniciar_sessao(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

function admin_login(string $usuario, string $senha): bool
{
    $db = conectar_banco();
    $stmt = $db->prepare(
        'SELECT id, usuario, senha_hash FROM administradores WHERE usuario = :usuario LIMIT 1'
    );
    $stmt->execute([':usuario' => $usuario]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
        return false;
    }

    iniciar_sessao();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_usuario'] = $admin['usuario'];
    return true;
}

function exigir_login(): void
{
    iniciar_sessao();
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function admin_sair(): void
{
    iniciar_sessao();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}
