<?php
// src/modelos/usuarios.php — SQL da tabela usuarios.

function usuarioPorEmail(string $email): ?array
{
    $st = pdo()->prepare('SELECT id, nome, email, telefone, senha_hash, perfil FROM usuarios WHERE email = ?');
    $st->execute([$email]);
    return $st->fetch() ?: null;
}

function usuarioPorId(int $id): ?array
{
    $st = pdo()->prepare('SELECT id, nome, email, telefone, perfil FROM usuarios WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// O perfil não é parâmetro: toda conta criada pela API é cliente.
function criarUsuario(string $nome, string $email, string $telefone, string $hash): int
{
    $st = pdo()->prepare("INSERT INTO usuarios (nome, email, telefone, senha_hash, perfil) VALUES (?, ?, ?, ?, 'cliente')");
    $st->execute([$nome, $email, $telefone, $hash]);
    return (int) pdo()->lastInsertId();
}

function atualizarConta(int $id, string $nome, string $telefone): void
{
    $st = pdo()->prepare('UPDATE usuarios SET nome = ?, telefone = ? WHERE id = ?');
    $st->execute([$nome, $telefone, $id]);
}

function atualizarSenha(int $id, string $hash): void
{
    $st = pdo()->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?');
    $st->execute([$hash, $id]);
}
