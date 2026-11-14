<?php
// paginas/conta/pagina.php — dados do cliente e troca de senha (GET/PUT /api/conta, PUT /api/conta/senha).
// Protegida no includes/rotas.php (acesso: logado). Script: js/conta.js
$titulo = 'Minha conta';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Minha conta', 'Seus dados', 'f-bola');
?>
<section class="container secao">
  <div class="conta">
    <?php menuConta('/conta'); ?>
    <div>
      <div id="aviso" hidden></div>
      <div class="caixa">
        <h2 class="sub-titulo">Meus dados</h2>
        <form id="form-dados" novalidate>
          <div class="campo">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" type="text" maxlength="100" autocomplete="name" required>
          </div>
          <div class="campo">
            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" disabled>
            <span class="campo__dica">O e-mail não pode ser alterado.</span>
          </div>
          <div class="campo">
            <label for="telefone">Telefone</label>
            <input id="telefone" name="telefone" type="tel" inputmode="numeric" maxlength="15" autocomplete="tel" placeholder="(00) 00000-0000">
          </div>
          <div class="form-acoes"><button class="btn btn--escuro" type="submit">Salvar dados</button></div>
        </form>
      </div>
      <div class="caixa">
        <h2 class="sub-titulo">Trocar senha</h2>
        <form id="form-senha" novalidate>
          <div class="campo">
            <label for="senha">Nova senha</label>
            <input id="senha" name="senha" type="password" minlength="8" autocomplete="new-password">
            <span class="campo__dica">Pelo menos 8 caracteres.</span>
          </div>
          <div class="campo">
            <label for="senha2">Repita a nova senha</label>
            <input id="senha2" name="senha2" type="password" autocomplete="new-password">
          </div>
          <div class="form-acoes"><button class="btn" type="submit">Trocar senha</button></div>
        </form>
      </div>
    </div>
  </div>
</section>
<?php
$scripts = ['conta.js'];
require __DIR__ . '/../../includes/rodape.php';
