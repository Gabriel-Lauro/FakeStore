<?php
// paginas/produto/pagina.php — detalhe: fotos, preço de/por, descrição, tamanho, adicionar ao carrinho.
// O id vem de $parametros[0] (ver public/index.php). Script: js/produto.js
$titulo = 'Produto';
$produtoId = (int) ($parametros[0] ?? 0);
require __DIR__ . '/../../includes/cabecalho.php';
?>
<section class="container secao" id="produto-raiz" data-id="<?= $produtoId ?>">
  <p class="migalhas"><a href="/">Início</a> / <a href="/produtos">Produtos</a> / <span id="migalha-nome">...</span></p>
  <div id="produto-conteudo" class="grade-produtos" style="display:block" aria-busy="true">
    <div class="produto">
      <div class="esqueleto"></div>
      <div><div class="esqueleto-linha"></div><div class="esqueleto-linha esqueleto-linha--curta"></div></div>
    </div>
  </div>
</section>
<?php
$scripts = ['produto.js'];
require __DIR__ . '/../../includes/rodape.php';
