<?php
// includes/cabecalho.php — <head>, topo (logo, busca, conta, carrinho, categorias) e menu do celular.
// Antes de incluir, a página pode definir:
//   $titulo  (string)  título da aba
//   $estilos (array)   css extras COMPARTILHADOS de public/css, ex.: ['formulario.css']
//   (o CSS só da página fica em paginas/<nome>/estilo.css e é carregado sozinho)

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/ui.php';

$nomeLoja = 'FAKE STORE';
$titulo   = isset($titulo) ? $titulo . ' | ' . $nomeLoja : $nomeLoja;
$estilos  = $estilos ?? [];
$usuario  = usuarioLogado();
$ehAdmin  = ($usuario['perfil'] ?? '') === 'admin';

function e(string $texto): string
{
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

// Logo da loja: imagem em public/img. Três versões, conforme o fundo onde aparece:
// '' (topo, fundo claro), 'menu' (menu do celular, fundo azul) e 'rodape' (fundo preto).
function logoLoja(string $nomeLoja, string $variante = ''): string
{
    $arquivo = ['menu' => 'logo-menu.png', 'rodape' => 'logo-rodape.png'][$variante] ?? 'logo.png';
    return '<a class="logo" href="/">'
        . '<img class="logo__img" src="/img/' . $arquivo . '" alt="' . e($nomeLoja) . ' - página inicial" width="560" height="133">'
        . '</a>';
}
// Links da faixa de categorias: [texto, href, categoria (slug) ou '' , classe]
$caminhoAtual = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$categoriaAtual = (string) ($_GET['categoria'] ?? '');
$linksNav = [
    ['Todos os produtos', '/produtos',                        '',                '',               true],
    ['Calçados',          '/produtos?categoria=calcados',        'calcados',        '',               false],
    ['Camisas de time',   '/produtos?categoria=camisas-de-time', 'camisas-de-time', '',               false],
    ['Fitness',           '/produtos?categoria=fitness',         'fitness',         '',               false],
    ['Acessórios',        '/produtos?categoria=acessorios',      'acessorios',      '',               false],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?></title>
<?php if (modoDemo()): ?>
  <meta name="modo-demo" content="1">
<?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:ital,wght@0,500;0,600;0,700;0,800;1,700;1,800;1,900&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/css/base.css">
  <link rel="stylesheet" href="/css/componentes.css">
  <link rel="stylesheet" href="/css/paginas.css">
<?php foreach ($estilos as $css): ?>
  <link rel="stylesheet" href="/css/<?= e($css) ?>">
<?php endforeach; ?>
  <link rel="stylesheet" href="/css/tipografia.css">
<?php if ($cssPagina = cssDaPagina()): ?>
  <link rel="stylesheet" href="<?= e($cssPagina) ?>">
<?php endif; ?>
</head>
<body>
  <a class="pular-conteudo" href="#conteudo">Pular para o conteúdo</a>
<?php if (modoDemo()): ?>
  <a class="selo-demo" href="/?demo=sair" title="Sair do modo demo">Modo demo &#10005;</a>
<?php endif; ?>

  <header class="topo">
    <div class="container topo__linha">
      <button class="btn btn--escuro topo__menu" type="button" id="menu-abrir" aria-expanded="false" aria-controls="menu-tela">Menu</button>

      <?= logoLoja($nomeLoja) ?>

      <form class="busca" action="/produtos" method="get" role="search">
        <label class="so-leitor-de-tela" for="busca-topo">Buscar produtos</label>
        <input id="busca-topo" type="search" name="busca" placeholder="O que você procura?" maxlength="80" autocomplete="off">
        <button type="submit">Buscar</button>
      </form>

      <?php if ($usuario): ?>
        <a class="topo__conta" href="/conta">
          <small>Olá, <?= e(explode(' ', trim($usuario['nome'] ?? 'Conta'))[0]) ?></small>
          <strong>Minha conta</strong>
        </a>
      <?php else: ?>
        <a class="topo__conta" href="/login">
          <small>Entre ou cadastre-se</small>
          <strong>Acompanhe seu pedido</strong>
        </a>
      <?php endif; ?>

      <a class="btn carrinho-link" href="/carrinho" aria-label="Carrinho">
        Carrinho <span class="contador" id="carrinho-contagem">0</span>
      </a>
    </div>

    <nav class="topo__nav" aria-label="Categorias">
      <div class="container">
        <ul>
          <?php foreach ($linksNav as [$texto, $href, $slug, $classe, $ehTodos]):
            $atual = $ehTodos
              ? ($caminhoAtual === '/produtos' && $categoriaAtual === '')
              : ($slug !== '' && $caminhoAtual === '/produtos' && $categoriaAtual === $slug);
          ?>
            <li><a class="<?= e($classe) ?>" href="<?= e($href) ?>"<?= $atual ? ' aria-current="page"' : '' ?>><?= e($texto) ?></a></li>
          <?php endforeach; ?>
          <?php if ($ehAdmin): ?>
            <li><a href="/admin">Painel admin</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </nav>
  </header>

  <!-- Menu em tela cheia (celular) -->
  <div class="menu-tela" id="menu-tela" role="dialog" aria-modal="true" aria-label="Menu principal" hidden>
    <div class="container menu-tela__barra">
      <?= logoLoja($nomeLoja, 'menu') ?>
      <button class="btn btn--branco" type="button" id="menu-fechar">Fechar</button>
    </div>

    <nav class="menu-tela__links" aria-label="Navegação">
      <a href="/">Início</a>
      <a href="/produtos">Produtos</a>
      <a href="/produtos?categoria=calcados">Calçados</a>
      <a href="/produtos?categoria=camisas-de-time">Camisas de time</a>
      <a href="/produtos?categoria=fitness">Fitness</a>
      <a href="/produtos?categoria=acessorios">Acessórios</a>
      <?php if ($usuario): ?>
        <a href="/meus-pedidos">Meus pedidos</a>
        <a href="/conta">Minha conta</a>
        <?php if ($ehAdmin): ?><a href="/admin">Painel admin</a><?php endif; ?>
        <a href="/sair">Sair</a>
      <?php else: ?>
        <a href="/login">Entrar</a>
        <a href="/cadastro">Criar conta</a>
      <?php endif; ?>
    </nav>

    <p class="menu-tela__rodape">Treine. Jogue. Ven&ccedil;a.</p>
  </div>

  <main id="conteudo">
