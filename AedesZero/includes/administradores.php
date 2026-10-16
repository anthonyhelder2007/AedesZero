<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function listar_administradores(): array
{
    $stmt = conectar_banco()->query(
        'SELECT id, usuario, criado_em FROM administradores ORDER BY id ASC'
    );
    return $stmt->fetchAll();
}

function criar_administrador(string $usuario, string $senha): int
{
    $usuario = trim($usuario);

    if (mb_strlen($usuario) < 3 || mb_strlen($usuario) > 80) {
        throw new InvalidArgumentException('O usuário deve ter entre 3 e 80 caracteres.');
    }
    if (strlen($senha) < 10) {
        throw new InvalidArgumentException('A senha deve ter no mínimo 10 caracteres.');
    }

    $db = conectar_banco();
    $stmt = $db->prepare('INSERT INTO administradores (usuario, senha_hash) VALUES (:usuario, :hash)');
    try {
        $stmt->execute([
            ':usuario' => $usuario,
            ':hash' => password_hash($senha, PASSWORD_DEFAULT),
        ]);
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {
            throw new InvalidArgumentException('Já existe um administrador com esse usuário.');
        }
        throw $e;
    }

    return (int) $db->lastInsertId();
}

function apagar_administrador(int $id, int $idLogado): bool
{
    if ($id === $idLogado) {
        throw new InvalidArgumentException('Você não pode apagar a própria conta enquanto está logado.');
    }

    $db = conectar_banco();
    $db->beginTransaction();
    try {
        $total = (int) $db->query('SELECT COUNT(*) FROM administradores FOR UPDATE')->fetchColumn();
        if ($total <= 1) {
            $db->rollBack();
            throw new InvalidArgumentException('Não é possível apagar o único administrador.');
        }

        $stmt = $db->prepare('DELETE FROM administradores WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $apagado = $stmt->rowCount() > 0;
        $db->commit();
        return $apagado;
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

function apagar_administrador_por_credenciais(string $usuario, string $senha, int $idLogado): bool
{
    $usuario = trim($usuario);

    $db = conectar_banco();
    $stmt = $db->prepare('SELECT id, senha_hash FROM administradores WHERE usuario = :usuario LIMIT 1');
    $stmt->execute([':usuario' => $usuario]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
        throw new InvalidArgumentException('Usuário ou senha incorretos.');
    }

    return apagar_administrador((int) $admin['id'], $idLogado);
}
