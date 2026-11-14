<?php
// paginas/admin/produtos/pagina.php — tabela de produtos com editar e excluir (GET/DELETE /api/admin/produtos).
// Protegida no includes/rotas.php (acesso: admin). Script: js/admin.js
$titulo = 'Produtos (admin)';
require __DIR__ . '/../../../includes/cabecalho.php';
topoPagina('Produtos', 'Administração', 'f-meia');
?>
<section class="container secao">
  <div class="conta">
    <?php menuAdmin('/admin/produtos'); ?>
    <div>
      <div id="aviso" hidden></div>
      <div class="admin-barra">
        <h2 class="sub-titulo" style="margin:0">Catálogo</h2>
        <a class="btn" href="/admin/produtos/novo">+ Novo produto</a>
      </div>
      <form id="admin-busca" class="admin-busca" role="search" novalidate>
        <input id="admin-busca-texto" type="search" maxlength="60" placeholder="Buscar produto pelo nome" aria-label="Buscar produto pelo nome">
        <button class="btn btn--escuro" type="submit">Buscar</button>
      </form>
      <p id="admin-contagem" class="campo__dica" aria-live="polite"></p>
      <div id="admin-produtos" aria-busy="true"></div>
      <div class="paginacao" id="admin-paginacao" hidden></div>
    </div>
  </div>
</section>
<?php
$scripts = ['admin.js'];
require __DIR__ . '/../../../includes/rodape.php';
