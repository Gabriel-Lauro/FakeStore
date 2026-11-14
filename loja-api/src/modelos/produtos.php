<?php
// src/modelos/produtos.php — SQL das tabelas produtos (e leitura de categorias, imagens e variações junto).

const POR_PAGINA_PADRAO = 12;
const POR_PAGINA_ADMIN = 10;
const POR_PAGINA_MAXIMO = 50;

// "ordenar" só aceita estas chaves; o valor da URL nunca entra direto no ORDER BY.
const ORDENACOES_PRODUTO = [
    'recentes'  => 'p.id DESC',
    'preco_asc'  => 'p.preco ASC, p.id',
    'preco_desc' => 'p.preco DESC, p.id',
    'nome'      => 'p.nome ASC',
];

function precoOuNulo($valor): ?float
{
    return $valor === null ? null : (float) $valor;
}

function produtoParaJson(array $p): array
{
    return [
        'id'             => (int) $p['id'],
        'nome'           => $p['nome'],
        'categoria_id'   => (int) $p['categoria_id'],
        'categoria'      => $p['categoria'],
        'marca_id'       => $p['marca_id'] === null ? null : (int) $p['marca_id'],
        'marca'          => $p['marca'],
        'marca_slug'     => $p['marca_slug'],
        'descricao'      => $p['descricao'],
        'preco'          => (float) $p['preco'],
        'preco_original' => precoOuNulo($p['preco_original']),
        'destaque'       => (int) $p['destaque'],
        'ativo'          => (int) $p['ativo'],
    ];
}

// Versão curta, para os cards da home e da listagem.
function produtoResumoParaJson(array $p): array
{
    return [
        'id'             => (int) $p['id'],
        'nome'           => $p['nome'],
        'categoria'      => $p['categoria'],
        'marca'          => $p['marca'],
        'preco'          => (float) $p['preco'],
        'preco_original' => precoOuNulo($p['preco_original']),
        'imagem'         => $p['imagem'],
    ];
}

// $filtros já vem limpo do controlador: categoria (slug), marca (slug), tamanho, preco_min, preco_max, busca,
// destaque, ordenar, pagina, por_pagina.
function listarProdutos(array $filtros, bool $soAtivos): array
{
    $onde = [];
    $params = [];
    if ($soAtivos) {
        $onde[] = 'p.ativo = 1';
    }
    if (!empty($filtros['categoria'])) {
        $onde[] = 'c.slug = ?';
        $params[] = $filtros['categoria'];
    }
    if (!empty($filtros['marca'])) {
        $onde[] = 'm.slug = ?';
        $params[] = $filtros['marca'];
    }

    if (!empty($filtros['tamanho'])) {
        $onde[] = 'EXISTS (SELECT 1 FROM produto_variacoes v WHERE v.produto_id = p.id AND v.tamanho = ?)';
        $params[] = $filtros['tamanho'];
    }

    if (isset($filtros['preco_min'])) {
        $onde[] = 'p.preco >= ?';
        $params[] = $filtros['preco_min'];
    }
    if (isset($filtros['preco_max'])) {
        $onde[] = 'p.preco <= ?';
        $params[] = $filtros['preco_max'];
    }
    if (!empty($filtros['busca'])) {
        $onde[] = 'p.nome LIKE ?';
        $params[] = '%' . addcslashes($filtros['busca'], '%_\\') . '%';
    }
    if (!empty($filtros['destaque'])) {
        $onde[] = 'p.destaque = 1';
    }
    $filtroSql = $onde ? ' WHERE ' . implode(' AND ', $onde) : '';

    $st = pdo()->prepare('SELECT COUNT(*) FROM produtos p JOIN categorias c ON c.id = p.categoria_id LEFT JOIN marcas m ON m.id = p.marca_id' . $filtroSql);
    $st->execute($params);
    $total = (int) $st->fetchColumn();

    $porPagina = max(1, min(POR_PAGINA_MAXIMO, (int) ($filtros['por_pagina'] ?? POR_PAGINA_PADRAO)));
    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $pagina = max(1, min($totalPaginas, (int) ($filtros['pagina'] ?? 1)));
    $ordem = ORDENACOES_PRODUTO[$filtros['ordenar'] ?? ''] ?? ORDENACOES_PRODUTO['recentes'];
    $deslocamento = ($pagina - 1) * $porPagina;

    // LIMIT/OFFSET são inteiros que o próprio PHP calculou acima.
    $sql = 'SELECT p.id, p.nome, p.preco, p.preco_original, c.nome AS categoria, m.nome AS marca,
                   (SELECT i.caminho FROM produto_imagens i WHERE i.produto_id = p.id ORDER BY i.ordem, i.id LIMIT 1) AS imagem
            FROM produtos p JOIN categorias c ON c.id = p.categoria_id LEFT JOIN marcas m ON m.id = p.marca_id' . $filtroSql .
           ' ORDER BY ' . $ordem . ' LIMIT ' . $porPagina . ' OFFSET ' . $deslocamento;
    $st = pdo()->prepare($sql);
    $st->execute($params);

    return [
        'produtos'      => array_map('produtoResumoParaJson', $st->fetchAll()),
        'total'         => $total,
        'pagina'        => $pagina,
        'por_pagina'    => $porPagina,
        'total_paginas' => $totalPaginas,
    ];
}

function produtoExiste(int $id): bool
{
    $st = pdo()->prepare('SELECT COUNT(*) FROM produtos WHERE id = ?');
    $st->execute([$id]);
    return (int) $st->fetchColumn() > 0;
}

// Produto com imagens e variações. $soAtivo = true na loja pública (inativo = não existe).
function produtoCompleto(int $id, bool $soAtivo): ?array
{
    $st = pdo()->prepare(
        'SELECT p.id, p.categoria_id, p.marca_id, p.nome, p.descricao, p.preco, p.preco_original, p.destaque, p.ativo,
                c.nome AS categoria, m.nome AS marca, m.slug AS marca_slug
         FROM produtos p JOIN categorias c ON c.id = p.categoria_id LEFT JOIN marcas m ON m.id = p.marca_id
         WHERE p.id = ?' . ($soAtivo ? ' AND p.ativo = 1' : '')
    );
    $st->execute([$id]);
    $linha = $st->fetch();
    if (!$linha) {
        return null;
    }
    $produto = produtoParaJson($linha);
    $produto['imagens'] = imagensDoProduto($id);
    $produto['variacoes'] = variacoesDoProduto($id);
    return $produto;
}

// Admin: lista paginada (inclusive inativos), só com o que a tabela mostra: foto, preço, estoque e status.
// O detalhe completo de um produto vem de produtoCompleto(). $filtros: busca (nome), pagina, por_pagina.
function listarProdutosAdmin(array $filtros): array
{
    $onde = '';
    $params = [];
    if (!empty($filtros['busca'])) {
        $onde = ' WHERE p.nome LIKE ?';
        $params[] = '%' . addcslashes($filtros['busca'], '%_\\') . '%';
    }

    $st = pdo()->prepare('SELECT COUNT(*) FROM produtos p' . $onde);
    $st->execute($params);
    $total = (int) $st->fetchColumn();

    // Totais da loja inteira (sem a busca), para os cartões do painel.
    $geral = pdo()->query('SELECT COUNT(*) AS total, COALESCE(SUM(ativo = 1), 0) AS ativos FROM produtos')->fetch();

    $porPagina = max(1, min(POR_PAGINA_MAXIMO, (int) ($filtros['por_pagina'] ?? POR_PAGINA_ADMIN)));
    $totalPaginas = max(1, (int) ceil($total / $porPagina));
    $pagina = max(1, min($totalPaginas, (int) ($filtros['pagina'] ?? 1)));
    $deslocamento = ($pagina - 1) * $porPagina;

    // LIMIT/OFFSET são inteiros que o próprio PHP calculou acima.
    $st = pdo()->prepare(
        'SELECT p.id, p.nome, p.preco, p.ativo, c.nome AS categoria, m.nome AS marca,
                (SELECT i.caminho FROM produto_imagens i WHERE i.produto_id = p.id ORDER BY i.ordem, i.id LIMIT 1) AS imagem,
                (SELECT COALESCE(SUM(v.estoque), 0) FROM produto_variacoes v WHERE v.produto_id = p.id) AS estoque_total
         FROM produtos p JOIN categorias c ON c.id = p.categoria_id LEFT JOIN marcas m ON m.id = p.marca_id' . $onde .
        ' ORDER BY p.id DESC LIMIT ' . $porPagina . ' OFFSET ' . $deslocamento
    );
    $st->execute($params);
    $produtos = array_map(static fn (array $l): array => [
        'id'            => (int) $l['id'],
        'nome'          => $l['nome'],
        'categoria'     => $l['categoria'],
        'marca'         => $l['marca'],
        'preco'         => (float) $l['preco'],
        'ativo'         => (int) $l['ativo'],
        'imagem'        => $l['imagem'],
        'estoque_total' => (int) $l['estoque_total'],
    ], $st->fetchAll());

    return [
        'produtos'      => $produtos,
        'total'         => $total,
        'pagina'        => $pagina,
        'por_pagina'    => $porPagina,
        'total_paginas' => $totalPaginas,
        'total_geral'   => (int) $geral['total'],
        'total_ativos'  => (int) $geral['ativos'],
    ];
}

function criarProduto(array $d): int
{
    $st = pdo()->prepare(
        'INSERT INTO produtos (categoria_id, marca_id, nome, descricao, preco, preco_original, destaque, ativo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $st->execute([$d['categoria_id'], $d['marca_id'], $d['nome'], $d['descricao'], $d['preco'], $d['preco_original'], $d['destaque'], $d['ativo']]);
    return (int) pdo()->lastInsertId();
}

function atualizarProduto(int $id, array $d): void
{
    $st = pdo()->prepare(
        'UPDATE produtos SET categoria_id = ?, marca_id = ?, nome = ?, descricao = ?, preco = ?, preco_original = ?, destaque = ?, ativo = ? WHERE id = ?'
    );
    $st->execute([$d['categoria_id'], $d['marca_id'], $d['nome'], $d['descricao'], $d['preco'], $d['preco_original'], $d['destaque'], $d['ativo'], $id]);
}

// Apaga também as imagens e variações (ON DELETE CASCADE). Itens de pedidos antigos ficam, com variacao_id nulo.
function excluirProduto(int $id): void
{
    $st = pdo()->prepare('DELETE FROM produtos WHERE id = ?');
    $st->execute([$id]);
}
