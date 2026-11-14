<?php
// src/modelos/categorias.php — SQL da tabela categorias.

function categoriaParaJson(array $c): array
{
    return ['id' => (int) $c['id'], 'nome' => $c['nome'], 'slug' => $c['slug']];
}

function listarCategorias(): array
{
    $linhas = pdo()->query('SELECT id, nome, slug FROM categorias ORDER BY id')->fetchAll();
    return array_map('categoriaParaJson', $linhas);
}

function buscarCategoria(int $id): ?array
{
    $st = pdo()->prepare('SELECT id, nome, slug FROM categorias WHERE id = ?');
    $st->execute([$id]);
    $c = $st->fetch();
    return $c ? categoriaParaJson($c) : null;
}

// Já existe outra categoria com esse nome/slug? ($exceto = id que está sendo renomeado)
function categoriaRepetida(string $nome, string $slug, int $exceto = 0): bool
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM categorias WHERE (nome = ? OR slug = ?) AND id <> ?');
    $st->execute([$nome, $slug, $exceto]);
    return (int) $st->fetchColumn() > 0;
}

function criarCategoria(string $nome, string $slug): int
{
    $st = pdo()->prepare('INSERT INTO categorias (nome, slug) VALUES (?, ?)');
    $st->execute([$nome, $slug]);
    return (int) pdo()->lastInsertId();
}

function renomearCategoria(int $id, string $nome, string $slug): void
{
    $st = pdo()->prepare('UPDATE categorias SET nome = ?, slug = ? WHERE id = ?');
    $st->execute([$nome, $slug, $id]);
}

function contarProdutosDaCategoria(int $id): int
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM produtos WHERE categoria_id = ?');
    $st->execute([$id]);
    return (int) $st->fetchColumn();
}

function excluirCategoria(int $id): void
{
    $st = pdo()->prepare('DELETE FROM categorias WHERE id = ?');
    $st->execute([$id]);
}
