<?php
// src/controladores/catalogo.php — rotas públicas: categorias, produtos e validação do carrinho.

function listarCategoriasPublico(array $p): void
{
    responder(200, listarCategorias());
}

// Só marcas, com a contagem de produtos ativos (a home usa para montar a faixa de marcas).
function listarMarcasPublico(array $p): void
{
    responder(200, listarMarcas(true));
}

// Lê e limpa os filtros da query string (?categoria=...&marca=...&tamanho=...&pagina=2 ...).
function lerFiltrosDeProdutos(): array
{
    $f = [];
    foreach (['categoria', 'marca', 'tamanho', 'busca', 'ordenar'] as $campo) {
        $valor = textoLimpo($_GET[$campo] ?? '');
        if ($valor !== '') {
            $f[$campo] = mb_substr($valor, 0, 60, 'UTF-8');
        }
    }
    foreach (['preco_min', 'preco_max'] as $campo) {
        $valor = valorMonetario($_GET[$campo] ?? null);
        if ($valor !== null) {
            $f[$campo] = $valor;
        }
    }
    $f['destaque'] = ($_GET['destaque'] ?? '') === '1';
    $f['pagina'] = max(1, inteiro($_GET['pagina'] ?? null) ?? 1);
    $f['por_pagina'] = inteiro($_GET['por_pagina'] ?? null) ?? POR_PAGINA_PADRAO;
    return $f;
}

function listarProdutosPublico(array $p): void
{
    responder(200, listarProdutos(lerFiltrosDeProdutos(), true));
}

function detalharProduto(array $p): void
{
    $produto = produtoCompleto((int) $p[0], true);
    if ($produto === null) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    responder(200, $produto);
}

// O carrinho fica no navegador; aqui ele é conferido com os preços e estoques de agora.
function validarCarrinho(array $p): void
{
    [$erros, $itens] = validarItens(lerJson()['itens'] ?? null);
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    $carrinho = montarCarrinho($itens, false);
    foreach ($carrinho['itens'] as &$item) {
        unset($item['centavos']); // detalhe interno, não faz parte da resposta
    }
    unset($item);
    responder(200, ['itens' => $carrinho['itens'], 'total' => $carrinho['total']]);
}
