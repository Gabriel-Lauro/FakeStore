<?php
// paginas/erro-403/pagina.php — sem permissão para acessar.
$titulo = 'Acesso negado';
require __DIR__ . '/../../includes/cabecalho.php';
?>
<section class="container" style="padding-block:96px;text-align:center">
  <h1 class="titulo" style="font-size:clamp(90px,22vw,240px)">403</h1>
  <p style="margin:12px 0 28px;font-size:18px">Você não tem permissão para acessar esta página.</p>
  <a class="btn btn--grande" href="/">Voltar ao início</a>
</section>
<?php require __DIR__ . '/../../includes/rodape.php'; ?>
