<?php
// paginas/admin/marcas/pagina.php — cadastrar, renomear e excluir marcas (GET/POST/PUT/DELETE /api/admin/marcas).
// Protegida no includes/rotas.php (acesso: admin). Script: js/admin.js
$titulo = 'Marcas (admin)';
require __DIR__ . '/../../../includes/cabecalho.php';
topoPagina('Marcas', 'Administração', 'f-meia');
?>
<section class="container secao">
  <div class="conta">
    <?php menuAdmin('/admin/marcas'); ?>
    <div>
      <div id="aviso" hidden></div>

      <form id="form-marca" class="caixa" novalidate>
        <h2 class="sub-titulo" id="marca-form-titulo">Nova marca</h2>
        <div class="campo">
          <label for="marca-nome">Nome da marca</label>
          <input id="marca-nome" type="text" maxlength="50" placeholder="Ex.: Under Armour" autocomplete="off" required>
          <span class="campo__dica">Depois de cadastrar, escolha a marca no formulário de cada produto.</span>
        </div>
        <div class="form-acoes">
          <button class="btn btn--escuro" type="submit" id="marca-salvar">Cadastrar marca</button>
          <button class="btn btn--branco" type="button" id="marca-cancelar" hidden>Cancelar</button>
        </div>
      </form>

      <h2 class="sub-titulo" style="margin-top:28px">Marcas cadastradas</h2>
      <p class="campo__dica">Uma marca só pode ser excluída quando não tem nenhum produto.</p>
      <div id="admin-marcas" aria-busy="true"></div>
    </div>
  </div>
</section>
<?php
$scripts = ['admin.js'];
require __DIR__ . '/../../../includes/rodape.php';
