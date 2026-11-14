<?php
// paginas/pedido-realizado/pagina.php — confirmação depois do checkout (?id=NÚMERO).
// Protegida no includes/rotas.php (acesso: logado). Sem script: o número vem da URL.
$titulo = 'Pedido realizado';
$numero = (int) ($_GET['id'] ?? 0);
require __DIR__ . '/../../includes/cabecalho.php';
?>
<section class="container" style="padding-block:clamp(40px,8vw,96px);text-align:center">
  <h1 class="titulo" style="font-size:clamp(44px,9vw,110px)">Pedido feito!</h1>
  <?php if ($numero > 0): ?>
    <p style="margin:14px 0 6px;font-size:18px">Seu pedido <strong>#<?= $numero ?></strong> foi registrado.</p>
  <?php endif; ?>
  <p style="margin-bottom:28px;color:var(--cinza-texto)">Ele aparece em “Meus pedidos” com o status aguardando pagamento.</p>
  <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:14px">
    <?php if ($numero > 0): ?>
      <a class="btn btn--escuro btn--grande" href="/meus-pedidos/<?= $numero ?>">Ver pedido</a>
    <?php endif; ?>
    <a class="btn btn--grande" href="/produtos">Continuar comprando</a>
  </div>
</section>
<?php require __DIR__ . '/../../includes/rodape.php'; ?>
