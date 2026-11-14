<?php
// src/modelos/marcas.php — SQL da tabela marcas (mesmo molde de categorias).
// Um produto tem no máximo uma marca (produtos.marca_id, opcional). Uma marca tem vários produtos.

function marcaParaJson(array $m): array
{
    $saida = ['id' => (int) $m['id'], 'nome' => $m['nome'], 'slug' => $m['slug']];
    if (isset($m['total_produtos'])) {
        $saida['total_produtos'] = (int) $m['total_produtos'];
    }
    return $saida;
}

// $soAtivos = true na loja (conta só produtos visíveis); false no admin (conta todos, que é o que trava a exclusão).
function listarMarcas(bool $soAtivos): array
{
    $sql = 'SELECT m.id, m.nome, m.slug, COUNT(p.id) AS total_produtos
            FROM marcas m LEFT JOIN produtos p ON p.marca_id = m.id' . ($soAtivos ? ' AND p.ativo = 1' : '') . '
            GROUP BY m.id, m.nome, m.slug
            ORDER BY m.nome';
    return array_map('marcaParaJson', pdo()->query($sql)->fetchAll());
}

function buscarMarca(int $id): ?array
{
    $st = pdo()->prepare('SELECT id, nome, slug FROM marcas WHERE id = ?');
    $st->execute([$id]);
    $m = $st->fetch();
    return $m ? marcaParaJson($m) : null;
}

// Já existe outra marca com esse nome/slug? ($exceto = id que está sendo renomeado)
function marcaRepetida(string $nome, string $slug, int $exceto = 0): bool
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM marcas WHERE (nome = ? OR slug = ?) AND id <> ?');
    $st->execute([$nome, $slug, $exceto]);
    return (int) $st->fetchColumn() > 0;
}

function criarMarca(string $nome, string $slug): int
{
    $st = pdo()->prepare('INSERT INTO marcas (nome, slug) VALUES (?, ?)');
    $st->execute([$nome, $slug]);
    return (int) pdo()->lastInsertId();
}

function renomearMarca(int $id, string $nome, string $slug): void
{
    $st = pdo()->prepare('UPDATE marcas SET nome = ?, slug = ? WHERE id = ?');
    $st->execute([$nome, $slug, $id]);
}

function contarProdutosDaMarca(int $id): int
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM produtos WHERE marca_id = ?');
    $st->execute([$id]);
    return (int) $st->fetchColumn();
}

function excluirMarca(int $id): void
{
    $st = pdo()->prepare('DELETE FROM marcas WHERE id = ?');
    $st->execute([$id]);
}
