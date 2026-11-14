<?php
// src/modelos/pedidos.php — SQL de pedidos e itens_pedido, e a conta do carrinho.
// Preço, total e estoque SEMPRE saem do banco; o que o navegador mandou de preço é ignorado.

function pedidoResumoParaJson(array $p): array
{
    return ['id' => (int) $p['id'], 'data' => $p['criado_em'], 'status' => $p['status'], 'total' => (float) $p['total']];
}

// Confere cada item do carrinho contra o banco. Lança 422 com TODOS os problemas de uma vez.
// $itens: lista limpa de [variacao_id, quantidade] (vem de validarItens).
function montarCarrinho(array $itens, bool $travar = false): array
{
    $variacoes = variacoesParaCarrinho(array_column($itens, 'variacao_id'), $travar);
    $erros = [];
    $saida = [];
    $totalCentavos = 0;

    foreach ($itens as $item) {
        $v = $variacoes[$item['variacao_id']] ?? null;
        if ($v === null) {
            $erros[] = 'Um item do carrinho não está mais à venda. Remova-o para continuar.';
            continue;
        }
        $descricao = $v['nome'] . ' (' . $v['tamanho'] . ')';
        $estoque = (int) $v['estoque'];
        if ($estoque < $item['quantidade']) {
            $erros[] = $descricao . ': ' . ($estoque === 0 ? 'sem estoque.' : 'só restam ' . $estoque . '.');
            continue;
        }
        $unitario = centavos($v['preco']);
        $subtotal = $unitario * $item['quantidade'];
        $totalCentavos += $subtotal;
        $saida[] = [
            'variacao_id'    => (int) $v['id'],
            'produto_id'     => (int) $v['produto_id'],
            'nome'           => $v['nome'],
            'tamanho'        => $v['tamanho'],
            'quantidade'     => $item['quantidade'],
            'preco_unitario' => centavosParaNumero($unitario),
            'subtotal'       => centavosParaNumero($subtotal),
            'imagem'         => $v['imagem'],
            'centavos'       => $unitario,
        ];
    }
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    return ['itens' => $saida, 'total' => centavosParaNumero($totalCentavos), 'total_centavos' => $totalCentavos];
}

// Checkout: recalcula, baixa o estoque, grava o pedido e os itens. Tudo ou nada (uma transação).
function finalizarCompra(int $usuarioId, array $endereco, array $itens): array
{
    $pdo = pdo();
    $pdo->beginTransaction();
    try {
        $carrinho = montarCarrinho($itens, true);

        foreach ($carrinho['itens'] as $i) {
            // UPDATE ... WHERE estoque >= qtd: o estoque nunca fica negativo, nem com duas compras ao mesmo tempo.
            if (!baixarEstoque($i['variacao_id'], $i['quantidade'])) {
                throw new ErroApi(422, $i['nome'] . ' ficou sem estoque.', [$i['nome'] . ' ficou sem estoque.']);
            }
        }

        $st = $pdo->prepare(
            'INSERT INTO pedidos (usuario_id, status, total, end_rotulo, end_cep, end_logradouro, end_numero,
                                  end_complemento, end_bairro, end_cidade, end_uf)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$usuarioId, 'aguardando_pagamento', centavosParaSql($carrinho['total_centavos']),
            $endereco['rotulo'], $endereco['cep'], $endereco['logradouro'], $endereco['numero'],
            $endereco['complemento'], $endereco['bairro'], $endereco['cidade'], $endereco['uf']]);
        $pedidoId = (int) $pdo->lastInsertId();

        $st = $pdo->prepare(
            'INSERT INTO itens_pedido (pedido_id, variacao_id, nome_produto, tamanho, quantidade, preco_unitario)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        foreach ($carrinho['itens'] as $i) {
            $st->execute([$pedidoId, $i['variacao_id'], $i['nome'], $i['tamanho'], $i['quantidade'],
                centavosParaSql($i['centavos'])]);
        }

        $pdo->commit();
        return ['id' => $pedidoId, 'total' => $carrinho['total']];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}

function listarPedidosDoUsuario(int $usuarioId): array
{
    $st = pdo()->prepare('SELECT id, criado_em, status, total FROM pedidos WHERE usuario_id = ? ORDER BY id DESC');
    $st->execute([$usuarioId]);
    return array_map('pedidoResumoParaJson', $st->fetchAll());
}

// $usuarioId = id da sessão: pedido de outra pessoa não é encontrado. null só para o admin.
function buscarPedido(int $id, ?int $usuarioId): ?array
{
    $sql = 'SELECT id, criado_em, status, total, end_rotulo, end_cep, end_logradouro, end_numero, end_complemento,
                   end_bairro, end_cidade, end_uf
            FROM pedidos WHERE id = ?';
    $params = [$id];
    if ($usuarioId !== null) {
        $sql .= ' AND usuario_id = ?';
        $params[] = $usuarioId;
    }
    $st = pdo()->prepare($sql);
    $st->execute($params);
    $p = $st->fetch();
    if (!$p) {
        return null;
    }

    $st = pdo()->prepare(
        'SELECT nome_produto, tamanho, quantidade, preco_unitario FROM itens_pedido WHERE pedido_id = ? ORDER BY id'
    );
    $st->execute([$id]);
    $itens = [];
    foreach ($st->fetchAll() as $i) {
        $itens[] = [
            'nome'           => $i['nome_produto'],
            'tamanho'        => $i['tamanho'],
            'quantidade'     => (int) $i['quantidade'],
            'preco_unitario' => (float) $i['preco_unitario'],
        ];
    }

    $pedido = pedidoResumoParaJson($p);
    $pedido['itens'] = $itens;
    $pedido['endereco'] = [
        'rotulo'      => $p['end_rotulo'],
        'cep'         => $p['end_cep'],
        'logradouro'  => $p['end_logradouro'],
        'numero'      => $p['end_numero'],
        'complemento' => $p['end_complemento'],
        'bairro'      => $p['end_bairro'],
        'cidade'      => $p['end_cidade'],
        'uf'          => $p['end_uf'],
    ];
    return $pedido;
}

function listarTodosPedidos(): array
{
    $linhas = pdo()->query(
        'SELECT p.id, p.criado_em, p.status, p.total, u.nome AS cliente
         FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id ORDER BY p.id DESC'
    )->fetchAll();
    return array_map(function (array $p): array {
        $pedido = pedidoResumoParaJson($p);
        $pedido['cliente'] = $p['cliente'];
        return $pedido;
    }, $linhas);
}

// Muda o status dentro de uma transação. Ao cancelar, devolve o estoque dos itens.
// $permitidosDe: se não for vazio, só deixa mudar quando o status atual estiver nessa lista.
function alterarStatusPedido(int $id, string $novo, array $permitidosDe = [], string $mensagemBloqueio = 'Este pedido não pode mais ser alterado.'): void
{
    $pdo = pdo();
    $pdo->beginTransaction();
    try {
        $st = $pdo->prepare('SELECT status FROM pedidos WHERE id = ? FOR UPDATE');
        $st->execute([$id]);
        $atual = $st->fetchColumn();
        if ($atual === false) {
            throw new ErroApi(404, 'Pedido não encontrado.');
        }
        if ($permitidosDe && !in_array($atual, $permitidosDe, true)) {
            throw new ErroApi(422, $mensagemBloqueio);
        }
        if ($atual === 'cancelado' && $novo !== 'cancelado') {
            throw new ErroApi(422, 'Um pedido cancelado não pode mudar de status.');
        }

        if ($novo === 'cancelado' && $atual !== 'cancelado') {
            $st = $pdo->prepare('SELECT variacao_id, quantidade FROM itens_pedido WHERE pedido_id = ? AND variacao_id IS NOT NULL');
            $st->execute([$id]);
            foreach ($st->fetchAll() as $item) {
                devolverEstoque((int) $item['variacao_id'], (int) $item['quantidade']);
            }
        }

        $st = $pdo->prepare('UPDATE pedidos SET status = ? WHERE id = ?');
        $st->execute([$novo, $id]);
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $e;
    }
}
