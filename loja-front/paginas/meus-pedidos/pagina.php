<?php
// paginas/meus-pedidos/pagina.php — lista dos pedidos do cliente (GET /api/pedidos). Script: js/pedidos.js
$titulo = 'Meus pedidos';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Meus pedidos', 'Minha conta', 'f-meia');
?>
<section class="container secao">
  <div class="conta">
    <?php menuConta('/meus-pedidos'); ?>
    <div class="lista" id="pedidos-lista" aria-busy="true"></div>
  </div>
</section>
<?php
$scripts = ['pedidos.js'];
require __DIR__ . '/../../includes/rodape.php';
