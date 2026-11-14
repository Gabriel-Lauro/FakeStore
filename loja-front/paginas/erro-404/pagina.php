<?php
// paginas/erro-404/pagina.php — página ou produto não encontrado.
$titulo = 'Página não encontrada';
require __DIR__ . '/../../includes/cabecalho.php';
?>
<section class="container" style="padding-block:96px;text-align:center">
  <h1 class="titulo" style="font-size:clamp(90px,22vw,240px)">404</h1>
  <p style="margin:12px 0 28px;font-size:18px">Essa página não existe ou foi removida.</p>
  <a class="btn btn--grande" href="/">Voltar ao início</a>
</section>
<?php require __DIR__ . '/../../includes/rodape.php'; ?>
