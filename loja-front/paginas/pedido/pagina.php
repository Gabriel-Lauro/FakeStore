<?php
// paginas/pedido/pagina.php — detalhe de um pedido: itens, endereço, status e cancelamento
// (GET /api/pedidos/{id}, PUT /api/pedidos/{id}/cancelar). O id vem de $parametros[0]. Script: js/pedidos.js
$titulo = 'Pedido';
$pedidoId = (int) ($parametros[0] ?? 0);
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Pedido #' . $pedidoId, 'Minha conta', 'f-meia');
?>
<section class="container secao">
  <div class="conta">
    <?php menuConta('/meus-pedidos'); ?>
    <div id="pedido-detalhe" data-id="<?= $pedidoId ?>" aria-busy="true">
      <div id="aviso" hidden></div>
      <div id="pedido-conteudo"></div>
    </div>
  </div>
</section>
<?php
$scripts = ['pedidos.js'];
require __DIR__ . '/../../includes/rodape.php';
