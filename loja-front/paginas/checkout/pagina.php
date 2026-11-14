<?php
// paginas/checkout/pagina.php — escolha do endereço, revisão dos itens e confirmação (POST /api/pedidos).
// Protegida no includes/rotas.php (acesso: logado). Script: js/checkout.js
$titulo = 'Finalizar compra';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Finalizar compra', 'Quase lá', 'f-raio');
?>
<section class="container secao">
  <div id="aviso" hidden></div>
  <div class="duas-colunas" id="checkout-layout">
    <div>
      <div class="caixa">
        <h2 class="sub-titulo">1. Endereço de entrega</h2>
        <div class="opcoes-endereco" id="checkout-enderecos" aria-busy="true"></div>
        <p style="margin-top:16px"><a class="btn btn--branco btn--pequeno" href="/conta/enderecos/novo?voltar=/checkout">+ Novo endereço</a></p>
      </div>
      <div class="caixa">
        <h2 class="sub-titulo">2. Revise os itens</h2>
        <div class="rolagem-x">
          <table class="tabela-itens">
            <thead><tr><th>Produto</th><th>Qtd</th><th>Subtotal</th></tr></thead>
            <tbody id="checkout-itens"></tbody>
          </table>
        </div>
      </div>
    </div>
    <aside class="resumo" aria-label="Resumo do pedido">
      <h2 class="resumo__titulo">Resumo</h2>
      <div class="resumo__linha"><span>Itens</span><span id="checkout-qtd">0</span></div>
      <div class="resumo__linha"><span>Frete</span><span>Grátis</span></div>
      <div class="resumo__total"><span>Total</span><span id="checkout-total">R$ 0,00</span></div>
      <button class="btn btn--escuro btn--grande" type="button" id="confirmar" disabled>Confirmar pedido</button>
      <a class="btn btn--branco" href="/carrinho">Voltar ao carrinho</a>
      <p class="resumo__nota">Projeto acadêmico: nenhum pagamento é cobrado de verdade.</p>
    </aside>
  </div>
</section>
<?php
$scripts = ['checkout.js'];
require __DIR__ . '/../../includes/rodape.php';
