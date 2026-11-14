<?php
// public/index.php — porta de entrada do front.
// Descobre a página pela URL, aplica a proteção do mapa de rotas e carrega o HTML.
// Cada página é uma pasta em paginas/<nome>/ (veja includes/paginas.php).

require_once __DIR__ . '/../includes/paginas.php';

$caminho = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/', '/');
if ($caminho === '') {
    $caminho = '/';
}

// CSS e imagens das pastas das páginas. Vem antes da sessão: não precisa dela.
if (strncmp($caminho, '/assets/', 8) === 0) {
    servirAsset(substr($caminho, 8));
}

require_once __DIR__ . '/../includes/auth.php';

$rotas = require __DIR__ . '/../includes/rotas.php';

// Logout: não é uma página. Encerra a sessão e volta para o login.
if ($caminho === '/sair') {
    $_SESSION = [];
    session_destroy();
    header('Location: /login');
    exit;
}

foreach ($rotas as [$padrao, $pagina, $acesso]) {
    if (!preg_match('#^' . $padrao . '$#', $caminho, $encontrados)) {
        continue;
    }

    $parametros = array_slice($encontrados, 1); // ex.: [3] em /produtos/3

    if ($acesso === 'logado') {
        exigirLogin();
    } elseif ($acesso === 'admin') {
        exigirPerfil('admin');
    }

    paginaAtual($pagina);
    require PAGINAS_DIR . '/' . $pagina . '/pagina.php';
    exit;
}

http_response_code(404);
paginaAtual('erro-404');
require PAGINAS_DIR . '/erro-404/pagina.php';
