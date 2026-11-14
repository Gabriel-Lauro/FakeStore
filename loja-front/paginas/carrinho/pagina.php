<?php
// paginas/carrinho/pagina.php — itens do localStorage, quantidades, total conferido pela API (POST /api/carrinho/validar).
// Script: js/carrinho-pagina.js
$titulo = 'Carrinho';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Seu carrinho', 'Compras', 'f-meia-lima');
?>
<section class="container secao">
  <div id="carrinho-aviso" hidden></div>
  <div class="duas-colunas" id="carrinho-layout">
    <div class="itens" id="carrinho-itens" aria-live="polite"></div>
    <aside class="resumo" id="carrinho-resumo" aria-label="Resumo do pedido"></aside>
  </div>
</section>
<?php
$scripts = ['carrinho-pagina.js'];
require __DIR__ . '/../../includes/rodape.php';
