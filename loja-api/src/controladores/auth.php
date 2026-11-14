<?php
// src/controladores/auth.php — cadastro, login, logout e "quem sou eu".

function cadastrar(array $p): void
{
    [$erros, $d] = validarUsuario(lerJson(), true);
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (usuarioPorEmail($d['email']) !== null) {
        throw new ErroApi(409, 'Este e-mail já está cadastrado.');
    }
    try {
        $id = criarUsuario($d['nome'], $d['email'], $d['telefone'], gerarHashSenha($d['senha']));
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) === 1062) { // duas contas ao mesmo tempo com o mesmo e-mail
            throw new ErroApi(409, 'Este e-mail já está cadastrado.');
        }
        throw $e;
    }
    responder(201, ['id' => $id]);
}

function entrar(array $p): void
{
    $d = lerJson();
    $email = strtolower(textoLimpo($d['email'] ?? null));
    $senha = is_string($d['senha'] ?? null) ? $d['senha'] : '';

    $usuario = $email !== '' ? usuarioPorEmail($email) : null;
    // Mesma mensagem para e-mail inexistente e senha errada: não revela qual dos dois falhou.
    if ($usuario === null || !conferirSenha($senha, $usuario['senha_hash'])) {
        throw new ErroApi(401, 'E-mail ou senha inválidos.');
    }

    session_regenerate_id(true); // novo id de sessão após o login (evita fixação de sessão)
    $_SESSION['usuario'] = [
        'id'     => (int) $usuario['id'],
        'nome'   => $usuario['nome'],
        'perfil' => $usuario['perfil'],
    ];
    responder(200, ['usuario' => $_SESSION['usuario']]);
}

function sair(array $p): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => $c['path'],
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    session_destroy();
    responder(204);
}

function quemSouEu(array $p): void
{
    responder(200, usuarioAtual());
}
