<?php
declare(strict_types=1);

const TIPOS_FOCO = ['Dengue', 'Chikungunya', 'Zika'];

function validar_denuncia(array $post, array $file): array
{
    $texto = static fn(string $campo): string => is_string($post[$campo] ?? null) ? $post[$campo] : '';

    $dados = [
        'bairro' => trim($texto('bairro')),
        'endereco' => trim($texto('endereco')),
        'tipo' => $texto('tipo'),
        // o navegador envia quebra de linha como \r\n (2 caracteres); normaliza para contar como o maxlength
        'descricao' => trim(strip_tags(str_replace("\r\n", "\n", $texto('descricao')))),
        'foto' => null,
    ];

    if (mb_strlen($dados['bairro']) < 3 || mb_strlen($dados['bairro']) > 100) {
        throw new InvalidArgumentException('O bairro deve ter entre 3 e 100 caracteres.');
    }
    if (!preg_match('/^[\p{L}\p{N}\s\-\.\'’]+$/u', $dados['bairro'])) {
        throw new InvalidArgumentException('O bairro contém caracteres inválidos.');
    }
    if ($dados['endereco'] !== '' && mb_strlen($dados['endereco']) > 200) {
        throw new InvalidArgumentException('O endereço deve ter no máximo 200 caracteres.');
    }
    if (!in_array($dados['tipo'], TIPOS_FOCO, true)) {
        throw new InvalidArgumentException('Selecione um tipo de foco válido.');
    }
    if (mb_strlen($dados['descricao']) > 500) {
        throw new InvalidArgumentException('A descrição deve ter no máximo 500 caracteres.');
    }

    if (isset($file['foto']) && $file['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($file['foto']['error'] === UPLOAD_ERR_INI_SIZE || $file['foto']['error'] === UPLOAD_ERR_FORM_SIZE) {
            throw new InvalidArgumentException('A imagem deve ter no máximo 2 MB.');
        }
        if ($file['foto']['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Não foi possível receber a imagem enviada.');
        }
        if ($file['foto']['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('A imagem deve ter no máximo 2 MB.');
        }

        $info = new finfo(FILEINFO_MIME_TYPE);
        $mime = $info->file($file['foto']['tmp_name']);
        $extensoes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        if (!isset($extensoes[$mime]) || @getimagesize($file['foto']['tmp_name']) === false) {
            throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WEBP válida.');
        }

        $pasta = __DIR__ . '/../public/uploads';
        if (!is_dir($pasta) && !mkdir($pasta, 0755, true) && !is_dir($pasta)) {
            throw new RuntimeException('Não foi possível preparar a pasta de imagens.');
        }
        $nome = bin2hex(random_bytes(16)) . '.' . $extensoes[$mime];
        if (!move_uploaded_file($file['foto']['tmp_name'], $pasta . '/' . $nome)) {
            throw new RuntimeException('Não foi possível salvar a imagem.');
        }
        $dados['foto'] = $nome;
    }

    return $dados;
}
