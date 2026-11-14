<?php
// src/modelos/imagens.php — SQL da tabela produto_imagens (o banco guarda só o caminho do arquivo).

function imagemParaJson(array $i): array
{
    return ['id' => (int) $i['id'], 'caminho' => $i['caminho'], 'ordem' => (int) $i['ordem']];
}

function imagensDoProduto(int $produtoId): array
{
    $st = pdo()->prepare('SELECT id, caminho, ordem FROM produto_imagens WHERE produto_id = ? ORDER BY ordem, id');
    $st->execute([$produtoId]);
    return array_map('imagemParaJson', $st->fetchAll());
}

function buscarImagem(int $id): ?array
{
    $st = pdo()->prepare('SELECT id, produto_id, caminho, ordem FROM produto_imagens WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

// A nova foto entra no fim da fila (a ordem 1 é a principal).
function criarImagem(int $produtoId, string $caminho): array
{
    $st = pdo()->prepare('SELECT COALESCE(MAX(ordem), 0) + 1 FROM produto_imagens WHERE produto_id = ?');
    $st->execute([$produtoId]);
    $ordem = (int) $st->fetchColumn();

    $st = pdo()->prepare('INSERT INTO produto_imagens (produto_id, caminho, ordem) VALUES (?, ?, ?)');
    $st->execute([$produtoId, $caminho, $ordem]);
    return ['id' => (int) pdo()->lastInsertId(), 'caminho' => $caminho, 'ordem' => $ordem];
}

function removerImagem(int $id): void
{
    $st = pdo()->prepare('DELETE FROM produto_imagens WHERE id = ?');
    $st->execute([$id]);
}

function caminhosDasImagens(int $produtoId): array
{
    $st = pdo()->prepare('SELECT caminho FROM produto_imagens WHERE produto_id = ?');
    $st->execute([$produtoId]);
    return $st->fetchAll(PDO::FETCH_COLUMN);
}
