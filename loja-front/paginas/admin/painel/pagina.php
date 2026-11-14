<?php
// paginas/admin/painel/pagina.php — visão geral e atalhos do administrador.
// Protegida no includes/rotas.php (acesso: admin). Script: js/admin.js
$titulo = 'Painel admin';
require __DIR__ . '/../../../includes/cabecalho.php';
topoPagina('Painel', 'Administração', 'f-raio');
?>
<section class="container secao">
  <div class="conta">
    <?php menuAdmin('/admin'); ?>
    <div>
      <div id="aviso" hidden></div>
      <div class="admin-cartoes" id="admin-numeros" aria-busy="true"></div>
      <div class="admin-atalhos">
        <a class="btn btn--grande" href="/admin/produtos/novo">+ Novo produto</a>
        <a class="btn btn--grande btn--branco" href="/admin/produtos">Gerenciar produtos</a>
        <a class="btn btn--grande btn--branco" href="/admin/marcas">Gerenciar marcas</a>
      </div>
      <h2 class="sub-titulo">Pedidos recentes</h2>
      <div id="admin-pedidos" aria-busy="true"></div>
    </div>
  </div>
</section>
<?php
$scripts = ['admin.js'];
require __DIR__ . '/../../../includes/rodape.php';
