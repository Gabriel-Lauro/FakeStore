<?php
// src/controladores/conta.php — dados da conta do usuário logado.

function verConta(array $p): void
{
    $u = usuarioPorId(usuarioAtual()['id']);
    if ($u === null) { // conta apagada com a sessão ainda aberta
        throw new ErroApi(401, 'Faça login para continuar.');
    }
    responder(200, ['nome' => $u['nome'], 'email' => $u['email'], 'telefone' => $u['telefone']]);
}

function editarConta(array $p): void
{
    $usuarioId = usuarioAtual()['id'];
    [$erros, $d] = validarUsuario(lerJson(), false);
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    atualizarConta($usuarioId, $d['nome'], $d['telefone']);
    $_SESSION['usuario']['nome'] = $d['nome']; // o menu do front mostra este nome

    $u = usuarioPorId($usuarioId);
    responder(200, ['nome' => $u['nome'], 'email' => $u['email'], 'telefone' => $u['telefone']]);
}

function trocarSenha(array $p): void
{
    $senha = lerJson()['senha'] ?? null;
    $erros = validarSenha($senha);
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    atualizarSenha(usuarioAtual()['id'], gerarHashSenha($senha));
    responder(204);
}
