<?php
// includes/paginas.php — como as páginas são organizadas.
//
// Cada página mora numa pasta em paginas/<nome>/ :
//   pagina.php   o PHP/HTML da página (obrigatório)
//   estilo.css   CSS só desta página (opcional). Carrega DEPOIS do CSS padrão do site.
//   img/         imagens só desta página (opcional)
//
// Essas pastas ficam fora de public/ (o PHP nunca fica acessível direto pelo navegador).
// Só CSS e imagens saem delas, pela rota /assets/<pasta>/<arquivo>
// Ex.: paginas/home/img/hero-principal.jpg  ->  /assets/home/img/hero-principal.jpg

const PAGINAS_DIR = __DIR__ . '/../paginas';

// Pasta da página que está sendo aberta (ex.: 'home', 'admin/painel').
// Com argumento, define; sem argumento, só devolve.
function paginaAtual(?string $pasta = null): string
{
    static $atual = '';
    if ($pasta !== null) {
        $atual = $pasta;
    }
    return $atual;
}

// URL de um arquivo dentro da pasta de uma página, com ?v= para o navegador atualizar
// quando o arquivo muda. Devolve null se o arquivo não existe.
function urlAsset(string $pasta, string $arquivo): ?string
{
    $caminho = PAGINAS_DIR . '/' . $pasta . '/' . $arquivo;
    if (!is_file($caminho)) {
        return null;
    }
    return '/assets/' . $pasta . '/' . $arquivo . '?v=' . filemtime($caminho);
}

// URL do estilo.css da página atual. Se não existe, ou se só tem comentários, devolve null
// (assim a página não faz um pedido à toa).
function cssDaPagina(): ?string
{
    $pasta = paginaAtual();
    if ($pasta === '') {
        return null;
    }
    $caminho = PAGINAS_DIR . '/' . $pasta . '/estilo.css';
    if (!is_file($caminho)) {
        return null;
    }
    $semComentarios = preg_replace('~/\*.*?\*/~s', '', (string) file_get_contents($caminho));
    if (trim((string) $semComentarios) === '') {
        return null;
    }
    return urlAsset($pasta, 'estilo.css');
}

// Entrega um arquivo de paginas/ pela rota /assets/... Só css e imagens; nunca php.
// $relativo é o que vem depois de /assets/ (ex.: 'home/img/hero-principal.jpg').
function servirAsset(string $relativo): void
{
    $tipos = [
        'css'  => 'text/css; charset=utf-8',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    $relativo = rawurldecode($relativo);
    $ext      = strtolower(pathinfo($relativo, PATHINFO_EXTENSION));
    $base     = realpath(PAGINAS_DIR);
    $arquivo  = $base === false ? false : realpath($base . '/' . $relativo);

    $valido = isset($tipos[$ext])
        && $arquivo !== false
        && strncmp($arquivo, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) === 0
        && is_file($arquivo);

    if (!$valido) {
        http_response_code(404);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Arquivo não encontrado.';
        exit;
    }

    header('Content-Type: ' . $tipos[$ext]);
    header('Content-Length: ' . filesize($arquivo));
    header('Cache-Control: public, max-age=3600');
    header('X-Content-Type-Options: nosniff');
    readfile($arquivo);
    exit;
}
