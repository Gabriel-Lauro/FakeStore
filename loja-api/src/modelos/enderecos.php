<?php
// src/modelos/enderecos.php — SQL da tabela enderecos. Toda consulta filtra pelo usuario_id da sessão.

const COLUNAS_ENDERECO = 'id, rotulo, cep, logradouro, numero, complemento, bairro, cidade, uf, principal';

function enderecoParaJson(array $e): array
{
    $e['id'] = (int) $e['id'];
    $e['principal'] = (int) $e['principal'];
    return $e;
}

function listarEnderecos(int $usuarioId): array
{
    $st = pdo()->prepare('SELECT ' . COLUNAS_ENDERECO . ' FROM enderecos WHERE usuario_id = ? ORDER BY principal DESC, id');
    $st->execute([$usuarioId]);
    return array_map('enderecoParaJson', $st->fetchAll());
}

// Só acha se o endereço for deste usuário (de outra pessoa = não existe).
function buscarEndereco(int $id, int $usuarioId): ?array
{
    $st = pdo()->prepare('SELECT ' . COLUNAS_ENDERECO . ' FROM enderecos WHERE id = ? AND usuario_id = ?');
    $st->execute([$id, $usuarioId]);
    $e = $st->fetch();
    return $e ? enderecoParaJson($e) : null;
}

function limparEnderecoPrincipal(int $usuarioId): void
{
    $st = pdo()->prepare('UPDATE enderecos SET principal = 0 WHERE usuario_id = ?');
    $st->execute([$usuarioId]);
}

function criarEndereco(int $usuarioId, array $d): int
{
    $pdo = pdo();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT COUNT(*) FROM enderecos WHERE usuario_id = ?');
        $st->execute([$usuarioId]);
        // O primeiro endereço da pessoa já nasce como principal.
        $principal = $d['principal'] === 1 || (int) $st->fetchColumn() === 0;
        if ($principal) {
            limparEnderecoPrincipal($usuarioId);
        }
        $st = $pdo->prepare(
            'INSERT INTO enderecos (usuario_id, rotulo, cep, logradouro, numero, complemento, bairro, cidade, uf, principal)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$usuarioId, $d['rotulo'], $d['cep'], $d['logradouro'], $d['numero'], $d['complemento'],
            $d['bairro'], $d['cidade'], $d['uf'], $principal ? 1 : 0]);
        $id = (int) $pdo->lastInsertId();
        $pdo->commit();
        return $id;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function atualizarEndereco(int $id, int $usuarioId, array $d): void
{
    $pdo = pdo();
    $pdo->beginTransaction();
    try {
        if ($d['principal'] === 1) {
            limparEnderecoPrincipal($usuarioId);
        }
        $st = $pdo->prepare(
            'UPDATE enderecos SET rotulo = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?, bairro = ?,
                    cidade = ?, uf = ?, principal = ? WHERE id = ? AND usuario_id = ?'
        );
        $st->execute([$d['rotulo'], $d['cep'], $d['logradouro'], $d['numero'], $d['complemento'], $d['bairro'],
            $d['cidade'], $d['uf'], $d['principal'], $id, $usuarioId]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function removerEndereco(int $id, int $usuarioId): void
{
    $pdo = pdo();
    $st = $pdo->prepare('DELETE FROM enderecos WHERE id = ? AND usuario_id = ?');
    $st->execute([$id, $usuarioId]);

    // Se sobrou algum endereço e nenhum é o principal, o mais recente assume.
    $st = $pdo->prepare('SELECT COUNT(*) FROM enderecos WHERE usuario_id = ? AND principal = 1');
    $st->execute([$usuarioId]);
    if ((int) $st->fetchColumn() === 0) {
        $st = $pdo->prepare('UPDATE enderecos SET principal = 1 WHERE usuario_id = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$usuarioId]);
    }
}
