<?php
// src/controladores/enderecos.php — endereços do usuário logado.
// Sempre filtra pelo id da SESSÃO; o id da URL só diz qual endereço, nunca de quem.

function listarMeusEnderecos(array $p): void
{
    responder(200, ['enderecos' => listarEnderecos(usuarioAtual()['id'])]);
}

function criarMeuEndereco(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    [$erros, $d] = validarEndereco(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    $id = criarEndereco($usuarioId, $d);
    responder(201, buscarEndereco($id, $usuarioId));
}

function editarMeuEndereco(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    $id = (int) $p[0];
    if (buscarEndereco($id, $usuarioId) === null) {
        throw new ErroApi(404, 'Endereço não encontrado.');
    }
    [$erros, $d] = validarEndereco(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    atualizarEndereco($id, $usuarioId, $d);
    responder(200, buscarEndereco($id, $usuarioId));
}

function removerMeuEndereco(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    $id = (int) $p[0];
    if (buscarEndereco($id, $usuarioId) === null) {
        throw new ErroApi(404, 'Endereço não encontrado.');
    }
    removerEndereco($id, $usuarioId);
    responder(204);
}
