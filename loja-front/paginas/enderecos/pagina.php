<?php
// paginas/enderecos/pagina.php — lista de endereços do cliente (GET /api/enderecos, DELETE /api/enderecos/{id}).
// Protegida no includes/rotas.php (acesso: logado). Script: js/enderecos.js
$titulo = 'Meus endereços';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Endereços', 'Minha conta', 'f-arco');
?>
<section class="container secao">
  <div class="conta">
    <?php menuConta('/conta/enderecos'); ?>
    <div>
      <div id="aviso" hidden></div>
      <div class="admin-barra">
        <h2 class="sub-titulo" style="margin:0">Onde entregamos</h2>
        <a class="btn" href="/conta/enderecos/novo">+ Novo endereço</a>
      </div>
      <div class="lista" id="enderecos-lista" aria-busy="true"></div>
    </div>
  </div>
</section>
<?php
$scripts = ['enderecos.js'];
require __DIR__ . '/../../includes/rodape.php';
