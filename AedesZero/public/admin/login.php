<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!empty($_SESSION['admin_id'])) { header('Location: painel.php'); exit; }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim((string)($_POST['usuario'] ?? ''));
    $senha = (string)($_POST['senha'] ?? '');
    try {
        if (admin_login($usuario, $senha)) { header('Location: painel.php'); exit; }
        $erro = 'Usuário ou senha incorretos.';
    } catch (Throwable $e) {
        error_log((string)$e);
        $erro = 'Não foi possível acessar. Confira a configuração do banco de dados.';
    }
}
function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Login administrativo | AedesZero</title><link rel="stylesheet" href="../css/form.css"><style>.login-box{max-width:480px;margin:50px auto;background:white;padding:32px;border-radius:18px;border:1px solid #d5dfd6}.login-box h1{margin-bottom:20px}.login-box form{display:flex;flex-direction:column}.login-box label{margin:12px 0 5px;font-weight:bold}.login-box input{padding:12px;border:1px solid #d5dfd6;border-radius:10px}.login-box button{margin-top:20px;padding:13px;border:0;border-radius:999px;background:#0f3d2e;color:white;font-weight:bold;cursor:pointer}.erro{background:#fdecec;color:#8a1f1f;padding:12px;border-radius:8px;margin-bottom:15px}</style></head>
<body><header class="topbar"><div class="wrap topbar-inner"><a class="brand" href="../index.php">AedesZero</a><a class="btn-back" href="../index.php">Início</a></div></header>
<main class="login-box"><h1>Área administrativa</h1><p>Entre com a conta de administrador.</p><?php if($erro): ?><p class="erro"><?= e($erro) ?></p><?php endif; ?>
<form method="post" action="login.php"><label for="usuario">Usuário</label><input id="usuario" name="usuario" required maxlength="80" autocomplete="username"><label for="senha">Senha</label><input type="password" id="senha" name="senha" required autocomplete="current-password"><button type="submit">Entrar</button></form></main></body></html>
