<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function criar_denuncia(array $dados): int
{
    $db = conectar_banco();
    $sql = 'INSERT INTO denuncias (bairro, endereco, tipoFoco, descricao, foto, status)
            VALUES (:bairro, :endereco, :tipo, :descricao, :foto, :status)';
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':bairro' => $dados['bairro'],
        ':endereco' => $dados['endereco'] !== '' ? $dados['endereco'] : null,
        ':tipo' => $dados['tipo'],
        ':descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
        ':foto' => $dados['foto'],
        ':status' => 'pendente',
    ]);

    return (int) $db->lastInsertId();
}

function listar_denuncias(): array
{
    $stmt = conectar_banco()->query(
        'SELECT id, bairro, endereco, tipoFoco, descricao, foto, status, criado_em
         FROM denuncias ORDER BY criado_em DESC, id DESC'
    );
    return $stmt->fetchAll();
}

function atualizar_status_denuncia(int $id, string $status): bool
{
    $permitidos = ['pendente', 'em_andamento', 'resolvida'];
    if (!in_array($status, $permitidos, true)) {
        return false;
    }

    $stmt = conectar_banco()->prepare('UPDATE denuncias SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => $id]);
    return $stmt->rowCount() > 0;
}
