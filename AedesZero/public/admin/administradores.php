<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/administradores.php';
exigir_login();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));

$mensagem = '';
$tipoMensagem = '';
if (!empty($_SESSION['flash'])) {
    [$tipoMensagem, $mensagem] = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
$usuarioDigitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    $acao = (string)($_POST['acao'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $mensagem = 'Sessão do formulário inválida. Atualize a página e tente novamente.';
        $tipoMensagem = 'erro';
    } else {
        try {
            if ($acao === 'criar') {
                $usuarioDigitado = trim((string)($_POST['usuario'] ?? ''));
                criar_administrador($usuarioDigitado, (string)($_POST['senha'] ?? ''));
                $_SESSION['flash'] = ['sucesso', 'Administrador criado com sucesso.'];
                header('Location: administradores.php');
                exit;
            } elseif ($acao === 'apagar') {
                $ok = apagar_administrador_por_credenciais((string)($_POST['usuario'] ?? ''), (string)($_POST['senha'] ?? ''), (int)$_SESSION['admin_id']);
                $_SESSION['flash'] = $ok
                    ? ['sucesso', 'Administrador apagado.']
                    : ['erro', 'Administrador não encontrado.'];
                header('Location: administradores.php');
                exit;
            }
        } catch (InvalidArgumentException $e) {
            $mensagem = $e->getMessage();
            $tipoMensagem = 'erro';
        } catch (Throwable $e) {
            error_log((string)$e);
            $mensagem = 'Não foi possível concluir a operação. Confira o banco de dados.';
            $tipoMensagem = 'erro';
        }
    }
}

try { $admins = listar_administradores(); }
catch (Throwable $e) { error_log((string)$e); $admins = []; $mensagem = 'Falha ao consultar administradores.'; $tipoMensagem = 'erro'; }
function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administradores | AedesZero</title><link rel="stylesheet" href="../css/form.css"><style>
body{padding-bottom:40px}.panel{width:min(900px,94%);margin:35px auto;background:#fff;padding:24px;border:1px solid #d5dfd6;border-radius:16px;overflow-x:auto}.panel-head{display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:20px}.panel h1{color:#0f3d2e}.panel h2{color:#0f3d2e;margin:28px 0 12px;font-size:1.2rem}.logout{background:#0f3d2e;color:white;padding:10px 16px;border-radius:20px}.notice{padding:12px;background:#e9f0e8;border-radius:8px;margin-bottom:16px}.notice.erro{background:#fdecec;color:#8a1f1f}.novo-admin{display:flex;flex-direction:column;max-width:420px}.novo-admin input[type=password]{width:100%;padding:13px 14px;border:1.5px solid #d5dfd6;border-radius:12px;font-family:inherit;font-size:15px;background:#f9fbf8}table{width:100%;border-collapse:collapse;font-size:14px}th,td{text-align:left;padding:11px;border-bottom:1px solid #d5dfd6;vertical-align:middle}th{background:#e9f0e8}.del-form{display:flex;flex-direction:row}.del-form button{margin-top:0;padding:7px 12px;border:1px solid #d64545;border-radius:7px;background:#fff;color:#8a1f1f;cursor:pointer;font-size:14px}.del-form button:hover{background:#fdecec;transform:none;box-shadow:none}.voce{color:#4a5f56;font-size:13px}
</style></head><body><header class="topbar"><div class="wrap topbar-inner"><a class="brand" href="../index.php">AedesZero</a><span style="color:#fff">Olá, <?= e($_SESSION['admin_usuario'] ?? 'admin') ?></span><span style="display:flex;gap:10px"><a class="logout" href="painel.php">Denúncias</a><a class="logout" href="logout.php">Sair</a></span></div></header>
<main class="panel"><div class="panel-head"><div><h1>Administradores</h1><p>Crie novas contas de acesso ou apague contas antigas.</p></div><strong>Total: <?= count($admins) ?></strong></div>
<?php if($mensagem): ?><p class="notice <?= e($tipoMensagem) ?>"><?= e($mensagem) ?></p><?php endif; ?>

<h2>Novo administrador</h2>
<form class="novo-admin" method="post" action="administradores.php" autocomplete="off">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="acao" value="criar">
<label for="usuario">Nome de usuário</label><input type="text" id="usuario" name="usuario" required minlength="3" maxlength="80" value="<?= e($usuarioDigitado) ?>" autocomplete="off">
<label for="senha">Senha (mínimo 10 caracteres)</label><input type="password" id="senha" name="senha" required minlength="10" autocomplete="new-password">
<button type="submit">Criar administrador</button></form>

<h2>Contas existentes</h2>
<table><thead><tr><th>ID</th><th>Usuário</th><th>Criado em</th><th>Ação</th></tr></thead><tbody>
<?php foreach($admins as $a): ?><tr><td><?= (int)$a['id'] ?></td><td><?= e($a['usuario']) ?><?php if ((int)$a['id'] === (int)$_SESSION['admin_id']): ?> <span class="voce">(você)</span><?php endif; ?></td><td><?= e($a['criado_em'] ?? '') ?></td><td>
<?php if ((int)$a['id'] === (int)$_SESSION['admin_id']): ?>—<?php else: ?>
<form class="del-form" method="post" action="administradores.php" onsubmit="var s=prompt('Digite a senha do administrador <?= e(addslashes($a['usuario'])) ?> para apagar:'); if(s===null||s===''){return false;} this.senha.value=s; return true;"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="acao" value="apagar"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="usuario" value="<?= e($a['usuario']) ?>"><input type="hidden" name="senha" value=""><button type="submit">Apagar</button></form>
<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></main></body></html>
