<?php
// paginas/home/pagina.php — página inicial: banner, categorias, coleções e produtos em destaque.
// Os destaques são carregados pelo js/home.js (GET /api/produtos?destaque=1).
// Dica: abra /?demo=1 para ver o layout com produtos de exemplo, sem a API.
//
// IMAGENS: todos os blocos rosa são lugares de foto. Salve o arquivo em paginas/home/img/
// com o nome que aparece no bloco (ex.: hero-principal.jpg). Lista em paginas/home/img/LEIA-ME.txt.
// CSS só desta página: paginas/home/estilo.css (carrega sozinho depois do CSS padrão).

$titulo  = 'Início';
$scripts = ['home.js'];
require __DIR__ . '/../../includes/cabecalho.php';

$categorias = [
    ['Calçados',        'calcados',        'cat-calcados'],
    ['Camisas de time', 'camisas-de-time', 'cat-camisas'],
    ['Fitness',         'fitness',         'cat-fitness'],
    ['Acessórios',      'acessorios',      'cat-acessorios'],
];
// Marcas da vitrine da home: [nome, slug]. O slug é o mesmo da tabela marcas (cadastro em /admin/marcas).
$marcasVitrine = [
    ['ADIDAS',      'adidas'],
    ['NIKE',        'nike'],
    ['NEW BALANCE', 'new-balance'],
    ['PUMA',        'puma'],
];
$faixa = ['Nova temporada 2026', 'Calçados', 'Camisas de time', 'Fitness', 'Acessórios', 'Do P ao GG', 'Do 28 ao 44', 'Pronto pra competir'];
?>

<!-- BANNER -->
<section class="hero" aria-labelledby="hero-titulo">
  <div class="hero__imagem">
    <?php slotImagem('hero-principal', 'Banner principal', '1200 x 560 px', 'Tênis em destaque'); ?>
  </div>
  <div class="hero__painel">
    <p class="hero__kicker">Nova temporada 2026</p>
    <h1 class="titulo hero__titulo" id="hero-titulo">Calçados</h1>
    <p class="hero__oferta"><small>Até</small><strong>50% off</strong></p>
    <a class="btn btn--branco btn--grande" href="/produtos?categoria=calcados">Comprar</a>
  </div>
</section>

<!-- FAIXA -->
<div class="faixa" aria-hidden="true">
  <div class="faixa__trilho">
    <?php for ($i = 0; $i < 4; $i++): foreach ($faixa as $item): ?>
      <span class="faixa__item"><?= e($item) ?></span>
    <?php endforeach; endfor; ?>
  </div>
</div>

<!-- CATEGORIAS -->
<section class="secao container" aria-labelledby="categorias-titulo">
  <h2 class="so-leitor-de-tela" id="categorias-titulo">Compre por categoria</h2>
  <div class="categorias">
    <?php foreach ($categorias as [$nome, $slug, $slot]): ?>
      <a class="categoria" href="/produtos?categoria=<?= e($slug) ?>">
        <span class="categoria__foto"><?php slotImagem($slot, 'Categoria ' . $nome, '440 x 440 px', $nome); ?></span>
        <span class="categoria__nome"><?= e($nome) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- MARCAS -->
<section class="secao container" aria-labelledby="marcas-titulo">
  <div class="secao__cabeca">
    <div>
      <p class="secao__rotulo">As marcas que você ama</p>
      <h2 class="titulo secao__titulo" id="marcas-titulo">Compre por marca</h2>
    </div>
    <a class="link-seta" href="/produtos">Ver todos &rarr;</a>
  </div>
  <div class="colecoes">
    <?php foreach ($marcasVitrine as [$nome, $slug]): ?>
      <a class="colecao marca-card" href="/produtos?marca=<?= e($slug) ?>" aria-label="Ver produtos <?= e($nome) ?>">
        <?php $arquivoLogo = 'marca-' . $slug . '.svg'; $caminhoLogo = PAGINAS_DIR . '/home/img/' . $arquivoLogo; ?>
        <?php if (is_file($caminhoLogo)): ?>
          <span class="marca-card__logo" role="img" aria-label="<?= e($nome) ?>" style="--logo: url('/assets/home/img/<?= e($arquivoLogo) ?>?v=<?= filemtime($caminhoLogo) ?>')"></span>
        <?php else: ?>
          <span class="marca-card__nome"><?= e($nome) ?></span>
        <?php endif; ?>
        <span class="marca-card__acao">Ver produtos &rarr;</span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- DESTAQUES -->
<section class="secao container" id="destaques" aria-labelledby="destaques-titulo">
  <div class="secao__cabeca">
    <div>
      <p class="secao__rotulo">Os mais procurados</p>
      <h2 class="titulo secao__titulo" id="destaques-titulo">Em destaque</h2>
    </div>
    <a class="link-seta" href="/produtos">Ver todos &rarr;</a>
  </div>

  <div class="grade-produtos" id="destaques-grade" aria-live="polite" aria-busy="true">
    <!-- preenchido pelo js/home.js -->
  </div>
</section>

<!-- BENEFÍCIOS -->
<section class="secao container" aria-label="Por que comprar aqui">
  <div class="beneficios">
    <div class="beneficio">
      <p class="beneficio__numero">01</p>
      <h3 class="titulo beneficio__titulo">Feito pra performar</h3>
      <p class="beneficio__texto">Material leve e resistente, pensado para treinar, jogar e competir.</p>
    </div>
    <div class="beneficio">
      <p class="beneficio__numero">02</p>
      <h3 class="titulo beneficio__titulo">Seu tamanho, na hora</h3>
      <p class="beneficio__texto">Escolha o tamanho e veja na hora se tem em estoque.</p>
    </div>
    <div class="beneficio">
      <p class="beneficio__numero">03</p>
      <h3 class="titulo beneficio__titulo">Compra simples</h3>
      <p class="beneficio__texto">Carrinho sem cadastro. Você só faz login na hora de finalizar.</p>
    </div>
  </div>
</section>

<!-- CHAMADA FINAL -->
<section class="secao container" aria-label="Crie sua conta">
  <div class="chamada">
    <div class="chamada__texto-bloco">
      <h2 class="titulo chamada__titulo">Entre pro time</h2>
      <p class="chamada__texto">Crie sua conta, salve seus endereços e acompanhe cada pedido em um só lugar.</p>
      <a class="btn btn--branco btn--grande" href="/cadastro">Quero me cadastrar</a>
    </div>
    <div class="chamada__imagem">
      <?php slotImagem('chamada-final', 'Chamada final', '800 x 600 px', ''); ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/../../includes/rodape.php'; ?>
