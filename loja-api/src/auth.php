<?php
// src/auth.php — sessão e proteção das rotas da API.
// A sessão é a mesma que o front lê: $_SESSION['usuario'] = ['id', 'nome', 'perfil'].

function iniciarSessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function usuarioAtual(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

// 401: não está logado.
function exigirLoginApi(): array
{
    $usuario = usuarioAtual();
    if ($usuario === null) {
        throw new ErroApi(401, 'Faça login para continuar.');
    }
    return $usuario;
}

// 401 sem login, 403 com login mas sem o perfil. A conferência é sempre no servidor.
function exigirPerfilApi(string $perfil): array
{
    $usuario = exigirLoginApi();
    if (($usuario['perfil'] ?? '') !== $perfil) {
        throw new ErroApi(403, 'Você não tem permissão para fazer isso.');
    }
    return $usuario;
}

// Pepper opcional (config.php). Vazio = a senha vai direto para o password_hash.
function prepararSenha(string $senha): string
{
    if (PEPPER === '') {
        return $senha;
    }
    return base64_encode(hash_hmac('sha256', $senha, PEPPER, true));
}

function gerarHashSenha(string $senha): string
{
    return password_hash(prepararSenha($senha), PASSWORD_DEFAULT);
}

function conferirSenha(string $senha, string $hash): bool
{
    return password_verify(prepararSenha($senha), $hash);
}
