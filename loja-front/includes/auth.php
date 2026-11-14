<?php
// includes/auth.php — sessão e proteção das páginas do front.
//
// A sessão é criada pela API (/api/auth/login) e lida aqui, porque as duas
// aplicações rodam no mesmo domínio e enxergam o mesmo cookie.
// Convenção: a API grava $_SESSION['usuario'] = ['id' => 1, 'nome' => '...', 'perfil' => 'cliente'|'admin'].

// MODO DEMO: permite navegar por todas as telas sem a API (dados de mentira no navegador).
//   /?demo=1      entra como cliente      /?demo=admin   entra como administrador
//   /?demo=sair   desliga o modo demo
// ATENÇÃO: coloque false antes de entregar o trabalho. Com false, nada disso existe.
const MODO_DEMO = false;

require_once __DIR__ . '/paginas.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (MODO_DEMO && isset($_GET['demo'])) {
    if ($_GET['demo'] === 'sair') {
        unset($_SESSION['demo'], $_SESSION['usuario']);
    } else {
        $_SESSION['demo'] = true;
        $_SESSION['usuario'] = $_GET['demo'] === 'admin'
            ? ['id' => 1, 'nome' => 'Admin Demo', 'perfil' => 'admin']
            : ['id' => 2, 'nome' => 'Cliente Demo', 'perfil' => 'cliente'];
    }
}

function modoDemo(): bool
{
    return MODO_DEMO && !empty($_SESSION['demo']);
}

function usuarioLogado(): ?array
{
    return $_SESSION['usuario'] ?? null;
}

function exigirLogin(): void
{
    if (usuarioLogado() === null) {
        header('Location: /login?voltar=' . urlencode($_SERVER['REQUEST_URI'] ?? '/'));
        exit;
    }
}

function exigirPerfil(string $perfil): void
{
    exigirLogin();
    if ((usuarioLogado()['perfil'] ?? '') !== $perfil) {
        http_response_code(403);
        paginaAtual('erro-403');
        require __DIR__ . '/../paginas/erro-403/pagina.php';
        exit;
    }
}
