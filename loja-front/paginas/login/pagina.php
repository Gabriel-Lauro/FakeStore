<?php
// paginas/login/pagina.php — e-mail e senha (POST /api/auth/login). Script: js/auth.js
// Mensagem de erro sempre genérica: "E-mail ou senha inválidos."
$titulo = 'Entrar';
if (usuarioLogado() !== null) {
    header('Location: /conta');
    exit;
}
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Entrar', 'Sua conta', 'f-bola');
?>
<section class="container auth">
  <div class="caixa auth__caixa">
    <div id="aviso" hidden></div>
    <form id="form-login" novalidate>
      <div class="campo">
        <label for="email">E-mail</label>
        <input id="email" name="email" type="email" autocomplete="email" placeholder="exemplo@email.com" required>
      </div>
      <div class="campo">
        <label for="senha">Senha</label>
        <input id="senha" name="senha" type="password" autocomplete="current-password" placeholder="*****" required>
      </div>
      <button class="btn btn--escuro btn--grande" type="submit" style="width:100%">Entrar</button>
    </form>
    <p class="auth__rodape">Ainda não tem conta? <a href="/cadastro">Criar conta</a></p>
<?php if (MODO_DEMO): ?>
    <p class="auth__rodape campo__dica">Modo demo: use <a href="/?demo=1">/?demo=1</a> (cliente) ou <a href="/?demo=admin">/?demo=admin</a> (administrador).</p>
<?php endif; ?>
  </div>
</section>
<?php
$scripts = ['auth.js'];
require __DIR__ . '/../../includes/rodape.php';
