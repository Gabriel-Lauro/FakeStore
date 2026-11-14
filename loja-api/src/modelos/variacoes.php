<?php
// src/modelos/variacoes.php — SQL da tabela produto_variacoes (tamanho + estoque).

function variacaoParaJson(array $v): array
{
    return ['id' => (int) $v['id'], 'tamanho' => $v['tamanho'], 'estoque' => (int) $v['estoque']];
}

function variacoesDoProduto(int $produtoId): array
{
    $st = pdo()->prepare('SELECT id, tamanho, estoque FROM produto_variacoes WHERE produto_id = ? ORDER BY id');
    $st->execute([$produtoId]);
    return array_map('variacaoParaJson', $st->fetchAll());
}

function buscarVariacao(int $id): ?array
{
    $st = pdo()->prepare('SELECT id, produto_id, tamanho, estoque FROM produto_variacoes WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function variacaoRepetida(int $produtoId, string $tamanho): bool
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM produto_variacoes WHERE produto_id = ? AND tamanho = ?');
    $st->execute([$produtoId, $tamanho]);
    return (int) $st->fetchColumn() > 0;
}

function criarVariacao(int $produtoId, string $tamanho, int $estoque): array
{
    $st = pdo()->prepare('INSERT INTO produto_variacoes (produto_id, tamanho, estoque) VALUES (?, ?, ?)');
    $st->execute([$produtoId, $tamanho, $estoque]);
    return ['id' => (int) pdo()->lastInsertId(), 'tamanho' => $tamanho, 'estoque' => $estoque];
}

function atualizarEstoque(int $id, int $estoque): void
{
    $st = pdo()->prepare('UPDATE produto_variacoes SET estoque = ? WHERE id = ?');
    $st->execute([$estoque, $id]);
}

function removerVariacao(int $id): void
{
    $st = pdo()->prepare('DELETE FROM produto_variacoes WHERE id = ?');
    $st->execute([$id]);
}

// Dados atuais das variações do carrinho, vindos do banco (nome, preço, estoque). Só de produtos ativos.
// $travar = true dentro de uma transação: FOR UPDATE segura as linhas até o commit.
function variacoesParaCarrinho(array $ids, bool $travar = false): array
{
    if ($ids === []) {
        return [];
    }
    $marcas = implode(',', array_fill(0, count($ids), '?'));
    $sql = 'SELECT v.id, v.produto_id, v.tamanho, v.estoque, p.nome, p.preco,
                   (SELECT i.caminho FROM produto_imagens i WHERE i.produto_id = p.id ORDER BY i.ordem, i.id LIMIT 1) AS imagem
            FROM produto_variacoes v
            JOIN produtos p ON p.id = v.produto_id
            WHERE p.ativo = 1 AND v.id IN (' . $marcas . ')' . ($travar ? ' FOR UPDATE' : '');
    $st = pdo()->prepare($sql);
    $st->execute(array_values($ids));
    $saida = [];
    foreach ($st->fetchAll() as $linha) {
        $saida[(int) $linha['id']] = $linha;
    }
    return $saida;
}

// Baixa o estoque só se ainda houver o suficiente. false = não havia (nada foi alterado).
function baixarEstoque(int $variacaoId, int $quantidade): bool
{
    $st = pdo()->prepare('UPDATE produto_variacoes SET estoque = estoque - ? WHERE id = ? AND estoque >= ?');
    $st->execute([$quantidade, $variacaoId, $quantidade]);
    return $st->rowCount() === 1;
}

function devolverEstoque(int $variacaoId, int $quantidade): void
{
    $st = pdo()->prepare('UPDATE produto_variacoes SET estoque = estoque + ? WHERE id = ?');
    $st->execute([$quantidade, $variacaoId]);
}
