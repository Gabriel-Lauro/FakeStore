<?php
// paginas/cadastro/pagina.php — nome, e-mail e senha (POST /api/auth/cadastro). Script: js/auth.js
$titulo = 'Criar conta';
if (usuarioLogado() !== null) {
    header('Location: /conta');
    exit;
}
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Criar conta', 'Bem-vindo', 'f-arco');
?>
<section class="container auth">
  <div class="caixa auth__caixa">
    <div id="aviso" hidden></div>
    <form id="form-cadastro" novalidate>
      <div class="campo">
        <label for="nome">Nome</label>
        <input id="nome" name="nome" type="text" autocomplete="name" maxlength="100" required>
      </div>
      <div class="campo">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" autocomplete="email" maxlength="150" required>
      </div>
      <div class="campo">
        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" autocomplete="new-password" minlength="8" required>
        <span class="campo__dica">Pelo menos 8 caracteres.</span>
      </div>
      <div class="campo">
        <label for="senha2">Repita a senha</label>
        <input id="senha2" name="senha2" type="password" autocomplete="new-password" required>
      </div>
      <button class="btn btn--escuro btn--grande" type="submit" style="width:100%">Criar conta</button>
    </form>
    <p class="auth__rodape">Já tem conta? <a href="/login">Entrar</a></p>
  </div>
</section>
<?php
$scripts = ['auth.js'];
require __DIR__ . '/../../includes/rodape.php';
