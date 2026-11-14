<?php
// includes/ui.php — pedaços de HTML repetidos nas páginas.

// Compatibilidade: antes existiam formas decorativas. Não desenha mais nada.
function forma(string $id = '', string $estilo = '', string $classe = ''): void
{
}

// Lugar de uma imagem colocada à mão.
// Procura {nome}.(jpg|jpeg|png|webp|avif|svg) primeiro na pasta img/ da própria página
// (paginas/<pagina>/img/) e depois em public/img/ (imagens usadas em várias páginas).
// Se achar, mostra a foto. Se não achar, mostra um bloco ROSA com o nome do arquivo
// e a medida sugerida.
//   slotImagem('hero-principal', 'Banner principal', '1200x560');
// Para trocar: salve a foto numa dessas pastas com esse nome. Não precisa mexer no código.
function slotImagem(string $nome, string $rotulo, string $medida, string $alt = ''): void
{
    $pastaPagina = paginaAtual();
    $onde = [];
    if ($pastaPagina !== '') {
        $onde[] = [PAGINAS_DIR . '/' . $pastaPagina . '/img/', '/assets/' . $pastaPagina . '/img/'];
    }
    $onde[] = [__DIR__ . '/../public/img/', '/img/'];

    foreach ($onde as [$pasta, $url]) {
        foreach (['jpg', 'jpeg', 'png', 'webp', 'avif', 'svg'] as $ext) {
            $arquivo = $pasta . $nome . '.' . $ext;
            if (is_file($arquivo)) {
                echo '<img class="slot-img" src="' . e($url . $nome . '.' . $ext) . '?v=' . filemtime($arquivo)
                    . '" alt="' . e($alt) . '" loading="lazy">';
                return;
            }
        }
    }
    echo '<div class="slot-img slot-img--vazio" role="img" aria-label="Imagem pendente: ' . e($rotulo) . '">'
        . '<span class="slot-img__nome">' . e($nome) . '</span>'
        . '</div>';
}

// Faixa azul do topo de cada página, com o título grande.
// O 3º parâmetro existe só por compatibilidade e é ignorado.
function topoPagina(string $titulo, string $rotulo = '', string $forma = ''): void
{
    echo '<section class="pagina-topo"><div class="container pagina-topo__conteudo">';
    if ($rotulo !== '') {
        echo '<span class="etiqueta">' . e($rotulo) . '</span>';
    }
    echo '<h1 class="titulo pagina-topo__titulo">' . e($titulo) . '</h1>';
    echo '</div></section>';
}

// Menu lateral da área do cliente. $atual = caminho da página aberta.
function menuConta(string $atual): void
{
    $links = [
        '/conta'            => 'Meus dados',
        '/conta/enderecos'  => 'Endereços',
        '/meus-pedidos'     => 'Meus pedidos',
    ];
    echo '<nav class="menu-lateral" aria-label="Minha conta">';
    foreach ($links as $href => $texto) {
        $ativo = $atual === $href ? ' aria-current="page"' : '';
        echo '<a href="' . e($href) . '"' . $ativo . '>' . e($texto) . '</a>';
    }
    echo '<a href="/sair">Sair</a></nav>';
}

// Menu lateral do painel admin.
function menuAdmin(string $atual): void
{
    $links = [
        '/admin'          => 'Visão geral',
        '/admin/produtos' => 'Produtos',
        '/admin/marcas'   => 'Marcas',
    ];
    echo '<nav class="menu-lateral" aria-label="Painel admin">';
    foreach ($links as $href => $texto) {
        $ativo = $atual === $href ? ' aria-current="page"' : '';
        echo '<a href="' . e($href) . '"' . $ativo . '>' . e($texto) . '</a>';
    }
    echo '<a href="/">Ver a loja</a></nav>';
}
