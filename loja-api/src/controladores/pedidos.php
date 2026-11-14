<?php
// src/controladores/pedidos.php — checkout e pedidos do usuário logado.

function criarMeuPedido(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    $corpo = lerJson();

    [$erros, $itens] = validarItens($corpo['itens'] ?? null);
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }

    // O endereço precisa ser do usuário logado (id de outra pessoa = "não existe").
    $enderecoId = inteiro($corpo['endereco_id'] ?? null);
    $endereco = $enderecoId !== null ? buscarEndereco($enderecoId, $usuarioId) : null;
    if ($endereco === null) {
        throw new ErroApi(422, 'Escolha um endereço de entrega.', ['Escolha um endereço de entrega.']);
    }

    // Preços e total são recalculados lá dentro, a partir do banco.
    responder(201, finalizarCompra($usuarioId, $endereco, $itens));
}

function listarMeusPedidos(array $p): void
{
    responder(200, ['pedidos' => listarPedidosDoUsuario(usuarioAtual()['id'])]);
}

function detalharMeuPedido(array $p): void
{
    $pedido = buscarPedido((int) $p[0], usuarioAtual()['id']);
    if ($pedido === null) {
        throw new ErroApi(404, 'Pedido não encontrado.');
    }
    responder(200, $pedido);
}

// Só dá para cancelar enquanto não foi enviado. O estoque volta para a loja.
function cancelarMeuPedido(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    $id = (int) $p[0];
    if (buscarPedido($id, $usuarioId) === null) {
        throw new ErroApi(404, 'Pedido não encontrado.');
    }
    alterarStatusPedido($id, 'cancelado', ['aguardando_pagamento', 'pago'], 'Este pedido não pode mais ser cancelado.');
    responder(200, buscarPedido($id, $usuarioId));
}
