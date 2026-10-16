<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/denuncias.php';
require_once __DIR__ . '/../includes/validacao.php';

$mensagem = '';
$tipoMensagem = '';
$valores = ['bairro' => '', 'endereco' => '', 'tipo' => '', 'descricao' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($valores as $campo => $_) {
        $valores[$campo] = is_string($_POST[$campo] ?? null) ? $_POST[$campo] : '';
    }
    $dados = null;
    try {
        $dados = validar_denuncia($_POST, $_FILES);
        criar_denuncia($dados);
        header('Location: form.php?ok=1');
        exit;
    } catch (InvalidArgumentException $e) {
        $mensagem = $e->getMessage();
        $tipoMensagem = 'erro';
    } catch (Throwable $e) {
        error_log((string)$e);
        if (is_array($dados) && !empty($dados['foto'])) {
            @unlink(__DIR__ . '/uploads/' . $dados['foto']);
        }
        $mensagem = 'Não foi possível registrar a denúncia. Verifique a conexão com o banco de dados.';
        $tipoMensagem = 'erro';
    }
}
if (isset($_GET['ok'])) {
    $mensagem = 'Denúncia enviada com sucesso. Obrigado por ajudar!';
    $tipoMensagem = 'sucesso';
}
function e(string $valor): string { return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8'); }
$tipos = ['Dengue', 'Chikungunya', 'Zika'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Denúncia | AedesZero</title><link rel="stylesheet" href="css/form.css">
</head>
<body>
<header class="topbar"><div class="wrap topbar-inner"><a href="index.php" class="brand">AedesZero</a><a href="index.php" class="btn-back">Voltar</a></div></header>
<section class="page-head"><div class="wrap"><p class="eyebrow">Formulário de denúncia</p><h1>Denuncie um <span>foco do mosquito</span></h1><p class="lead">Preencha os campos abaixo para que a equipe responsável possa analisar o relato.</p></div></section>
<main class="container">
<?php if ($mensagem !== ''): ?><div class="mensagem <?= e($tipoMensagem) ?>" role="alert"><?= e($mensagem) ?></div><?php endif; ?>
<form action="form.php" method="post" enctype="multipart/form-data">
<label for="bairro">Bairro *</label><input required minlength="3" maxlength="100" type="text" id="bairro" name="bairro" value="<?= e($valores['bairro']) ?>" placeholder="Nome do bairro">
<label for="endereco">Endereço</label><input maxlength="200" type="text" id="endereco" name="endereco" value="<?= e($valores['endereco']) ?>" placeholder="Rua, número ou referência">
<label for="tipo">Tipo do foco *</label><select required name="tipo" id="tipo"><option value="">Selecione um tipo</option><?php foreach ($tipos as $t): ?><option value="<?= e($t) ?>" <?= $valores['tipo'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?></select>
<label for="descricao">Descrição do problema</label><textarea maxlength="500" rows="6" id="descricao" name="descricao"><?= e($valores['descricao']) ?></textarea>
<label for="foto">Enviar imagem (JPG, PNG ou WEBP; até 2 MB)</label><input type="hidden" name="MAX_FILE_SIZE" value="2097152"><input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" id="foto" name="foto">
<button type="submit">Enviar denúncia</button></form><a href="index.php" class="back-link">Voltar para o início</a>
</main></body></html>
