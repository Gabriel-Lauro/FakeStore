<?php
// paginas/endereco-form/pagina.php — criar (/conta/enderecos/novo) ou editar (/conta/enderecos/{id}) um endereço.
// POST /api/enderecos ou PUT /api/enderecos/{id}. O id, se houver, vem de $parametros[0]. Script: js/enderecos.js
$enderecoId = isset($parametros[0]) ? (int) $parametros[0] : 0;
$titulo = $enderecoId ? 'Editar endereço' : 'Novo endereço';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina($enderecoId ? 'Editar endereço' : 'Novo endereço', 'Minha conta', 'f-arco');
?>
<section class="container secao">
  <div class="conta">
    <?php menuConta('/conta/enderecos'); ?>
    <div class="caixa">
      <div id="aviso" hidden></div>
      <form id="form-endereco" data-id="<?= $enderecoId ?>" novalidate>
        <div class="form-grade">
          <div class="campo campo--3">
            <label for="rotulo">Apelido</label>
            <input id="rotulo" name="rotulo" type="text" maxlength="40" placeholder="Casa, Trabalho...">
          </div>
          <div class="campo campo--3">
            <label for="cep">CEP</label>
            <input id="cep" name="cep" type="text" inputmode="numeric" maxlength="9" autocomplete="postal-code" placeholder="00000-000" required>
          </div>
          <div class="campo campo--4">
            <label for="logradouro">Rua</label>
            <input id="logradouro" name="logradouro" type="text" maxlength="120" autocomplete="address-line1" required>
          </div>
          <div class="campo campo--2">
            <label for="numero">Número</label>
            <input id="numero" name="numero" type="text" maxlength="10" required>
          </div>
          <div class="campo campo--3">
            <label for="complemento">Complemento</label>
            <input id="complemento" name="complemento" type="text" maxlength="60" placeholder="Apto, bloco...">
          </div>
          <div class="campo campo--3">
            <label for="bairro">Bairro</label>
            <input id="bairro" name="bairro" type="text" maxlength="80" required>
          </div>
          <div class="campo campo--4">
            <label for="cidade">Cidade</label>
            <input id="cidade" name="cidade" type="text" maxlength="80" autocomplete="address-level2" required>
          </div>
          <div class="campo campo--2">
            <label for="uf">UF</label>
            <input id="uf" name="uf" type="text" maxlength="2" autocomplete="address-level1" style="text-transform:uppercase" required>
          </div>
        </div>
        <div class="campo--check">
          <input id="principal" name="principal" type="checkbox">
          <label for="principal">Usar como endereço principal</label>
        </div>
        <div class="form-acoes">
          <button class="btn btn--escuro" type="submit">Salvar endereço</button>
          <a class="btn btn--branco" href="/conta/enderecos">Cancelar</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php
$scripts = ['enderecos.js'];
require __DIR__ . '/../../includes/rodape.php';
