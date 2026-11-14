<?php
// includes/rodape.php — fecha o <main>, rodapé e scripts.
// Antes de incluir, a página pode definir $scripts (array de arquivos em public/js).
$scripts = $scripts ?? [];
?>
  </main>

  <footer class="rodape">
    <div class="container rodape__grade">
      <div>
        <?= logoLoja($nomeLoja, 'rodape') ?>
        <p class="rodape__texto">Loja de artigos esportivos: calçados, camisas de time, roupas de treino e acessórios. Projeto acadêmico de Linguagem de Programação VIII.</p>
      </div>
      <div>
        <h3>Loja</h3>
        <ul>
          <li><a href="/produtos">Todos os produtos</a></li>
          <li><a href="/produtos?categoria=calcados">Calçados</a></li>
          <li><a href="/produtos?categoria=camisas-de-time">Camisas de time</a></li>
          <li><a href="/produtos?categoria=fitness">Fitness</a></li>
          <li><a href="/produtos?categoria=acessorios">Acessórios</a></li>
        </ul>
      </div>
      <div>
        <h3>Conta</h3>
        <ul>
          <li><a href="/login">Entrar</a></li>
          <li><a href="/cadastro">Criar conta</a></li>
          <li><a href="/meus-pedidos">Meus pedidos</a></li>
          <li><a href="/carrinho">Carrinho</a></li>
        </ul>
      </div>
      <div>
        <h3>Projeto</h3>
        <ul>
          <li>UNIFEG</li>
          <li>Ciência da Computação</li>
          <li>8º Período</li>
        </ul>
      </div>
    </div>
    <div class="container rodape__fim">&copy; <?= date('Y') ?> <?= e($nomeLoja) ?> — trabalho acadêmico, sem vendas reais.</div>
  </footer>

  <script src="/js/api.js"></script>
<?php if (modoDemo()): ?>
  <script src="/js/demo.js?v=<?= (int) @filemtime(__DIR__ . '/../public/js/demo.js') ?>"></script>
<?php endif; ?>
  <script src="/js/carrinho.js"></script>
  <script src="/js/menu.js"></script>
  <script src="/js/ui.js"></script>
<?php foreach ($scripts as $js):
  // ?v=<data de modificação>: quando o .js muda, o navegador baixa de novo em vez de usar o cache antigo.
  $arquivoJs = __DIR__ . '/../public/js/' . $js;
  $versaoJs = is_file($arquivoJs) ? filemtime($arquivoJs) : time();
?>
  <script src="/js/<?= e($js) ?>?v=<?= (int) $versaoJs ?>"></script>
<?php endforeach; ?>
</body>
</html>
