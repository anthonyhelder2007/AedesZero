<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/denuncias.php';
exigir_login();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    if (!hash_equals($_SESSION['csrf'], $csrf)) {
        $mensagem = 'Sessão do formulário inválida. Atualize a página e tente novamente.';
    } else {
        try {
            $ok = atualizar_status_denuncia((int)($_POST['id'] ?? 0), (string)($_POST['status'] ?? ''));
            $mensagem = $ok ? 'Status atualizado.' : 'Nenhuma alteração realizada.';
        } catch (Throwable $e) {
            error_log((string)$e);
            $mensagem = 'Não foi possível atualizar o status.';
        }
    }
}
try { $denuncias = listar_denuncias(); }
catch (Throwable $e) { error_log((string)$e); $denuncias = []; $mensagem = 'Falha ao consultar denúncias. Confira o banco de dados.'; }
function e(?string $v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Painel | AedesZero</title><link rel="stylesheet" href="../css/form.css"><style>
body{padding-bottom:40px}.panel{width:min(1200px,94%);margin:35px auto;background:#fff;padding:24px;border:1px solid #d5dfd6;border-radius:16px;overflow-x:auto}.panel-head{display:flex;justify-content:space-between;gap:15px;align-items:center;flex-wrap:wrap;margin-bottom:20px}.panel h1{color:#0f3d2e}.logout{background:#0f3d2e;color:white;padding:10px 16px;border-radius:20px}.notice{padding:12px;background:#e9f0e8;border-radius:8px;margin-bottom:16px}table{width:100%;border-collapse:collapse;font-size:14px}th,td{text-align:left;padding:11px;border-bottom:1px solid #d5dfd6;vertical-align:top}th{background:#e9f0e8}td img{width:90px;height:auto;border-radius:8px}.status-form{display:flex;flex-direction:row;gap:6px;align-items:center}.status-form select{width:auto}.status-form button{margin-top:0}.status-form select,.status-form button{padding:7px;border:1px solid #d5dfd6;border-radius:7px}.status-form button{background:#0f3d2e;color:#fff;cursor:pointer}.status{white-space:nowrap}.empty{padding:25px;text-align:center}
</style></head><body><header class="topbar"><div class="wrap topbar-inner"><a class="brand" href="../index.php">AedesZero</a><span style="color:#fff">Olá, <?= e($_SESSION['admin_usuario'] ?? 'admin') ?></span><span style="display:flex;gap:10px"><a class="logout" href="administradores.php">Administradores</a><a class="logout" href="logout.php">Sair</a></span></div></header>
<main class="panel"><div class="panel-head"><div><h1>Painel de denúncias</h1><p>Consulte os registros e atualize o andamento.</p></div><strong>Total: <?= count($denuncias) ?></strong></div>
<?php if($mensagem): ?><p class="notice"><?= e($mensagem) ?></p><?php endif; ?>
<?php if(!$denuncias): ?><p class="empty">Nenhuma denúncia encontrada.</p><?php else: ?>
<table><thead><tr><th>ID</th><th>Data</th><th>Bairro / endereço</th><th>Tipo</th><th>Descrição</th><th>Foto</th><th>Status</th></tr></thead><tbody>
<?php foreach($denuncias as $d): ?><tr><td><?= (int)$d['id'] ?></td><td><?= e($d['criado_em'] ?? '') ?></td><td><?= e($d['bairro']) ?><br><?= e($d['endereco'] ?? '') ?></td><td><?= e($d['tipoFoco']) ?></td><td><?= e($d['descricao'] ?? '') ?></td><td><?php if(!empty($d['foto'])): ?><a target="_blank" rel="noopener" href="../uploads/<?= rawurlencode($d['foto']) ?>"><img src="../uploads/<?= rawurlencode($d['foto']) ?>" alt="Foto enviada"></a><?php else: ?>—<?php endif; ?></td><td><form class="status-form" method="post"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><select name="status"><option value="pendente" <?= $d['status']==='pendente'?'selected':'' ?>>Pendente</option><option value="em_andamento" <?= $d['status']==='em_andamento'?'selected':'' ?>>Em andamento</option><option value="resolvida" <?= $d['status']==='resolvida'?'selected':'' ?>>Resolvida</option></select><button type="submit">Salvar</button></form></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?></main></body></html>
