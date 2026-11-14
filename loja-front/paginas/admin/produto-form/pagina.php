<?php
// paginas/admin/produto-form/pagina.php — criar (/admin/produtos/novo) ou editar (/admin/produtos/{id}) um produto.
// Um formulário só (dados, tamanhos e fotos) e um botão só: criar faz POST; editar faz PATCH só do que mudou.
// O id, se houver, vem de $parametros[0]. Script: js/admin.js
$produtoId = isset($parametros[0]) ? (int) $parametros[0] : 0;
$titulo = $produtoId ? 'Editar produto' : 'Novo produto';
require __DIR__ . '/../../../includes/cabecalho.php';
topoPagina($produtoId ? 'Editar produto' : 'Novo produto', 'Administração', 'f-arco');
?>
<section class="container secao">
  <div class="conta">
    <?php menuAdmin('/admin/produtos'); ?>
    <div>
      <div id="aviso" hidden></div>

      <form id="form-produto" data-id="<?= $produtoId ?>" novalidate>
        <div class="caixa">
          <h2 class="sub-titulo">Dados do produto</h2>
          <div class="form-grade">
            <div class="campo">
              <label for="nome">Nome</label>
              <input id="nome" name="nome" type="text" maxlength="120" required>
            </div>
            <div class="campo campo--3">
              <label for="categoria_id">Categoria</label>
              <select id="categoria_id" name="categoria_id" required><option value="">Escolha...</option></select>
            </div>
            <div class="campo campo--3">
              <label for="marca_id">Marca</label>
              <select id="marca_id" name="marca_id"><option value="">Sem marca</option></select>
              <span class="campo__dica">Opcional. <a href="/admin/marcas">Cadastrar marcas</a></span>
            </div>
            <div class="campo">
              <label for="descricao">Descrição</label>
              <textarea id="descricao" name="descricao" rows="4" maxlength="2000"></textarea>
            </div>
            <div class="campo campo--3">
              <label for="preco">Preço (R$)</label>
              <input id="preco" name="preco" type="number" min="0.01" step="0.01" inputmode="decimal" required>
            </div>
            <div class="campo campo--3">
              <label for="preco_original">Preço original (R$)</label>
              <input id="preco_original" name="preco_original" type="number" min="0" step="0.01" inputmode="decimal">
              <span class="campo__dica">Opcional. Preencha para mostrar o preço riscado e o selo de desconto.</span>
            </div>
          </div>
          <div class="campo--check">
            <input id="destaque" name="destaque" type="checkbox">
            <label for="destaque">Mostrar em destaque na home</label>
          </div>
          <div class="campo--check">
            <input id="ativo" name="ativo" type="checkbox" checked>
            <label for="ativo">Produto ativo (visível na loja)</label>
          </div>
        </div>

        <div class="caixa">
          <h2 class="sub-titulo">Tamanhos e estoque</h2>
          <p class="campo__dica"><?= $produtoId ? 'As mudanças só valem quando você clicar em Salvar.' : 'Adicione os tamanhos agora; eles são salvos junto com o produto.' ?></p>
          <div id="variacoes-lista" aria-busy="true"></div>
          <div class="var-linha" style="margin-top:18px">
            <div class="campo">
              <label for="v-tamanho">Tamanho</label>
              <select id="v-tamanho">
                <option>PP</option><option>P</option><option selected>M</option><option>G</option><option>GG</option><option>U</option><option>34</option><option>35</option><option>36</option><option>37</option><option>38</option><option>39</option><option>40</option><option>41</option><option>42</option><option>43</option><option>44</option><option>45</option><option>46</option>
              </select>
            </div>
            <div class="campo">
              <label for="v-estoque">Estoque</label>
              <input id="v-estoque" type="number" min="0" step="1" value="0">
            </div>
            <button class="btn btn--branco" type="button" id="v-add">+ Tamanho</button>
          </div>
        </div>

        <div class="caixa">
          <h2 class="sub-titulo">Imagens</h2>
          <p class="campo__dica"><?= $produtoId ? 'As fotos novas só são enviadas quando você clicar em Salvar.' : 'As fotos escolhidas aqui são enviadas junto com o produto.' ?></p>
          <div class="imagens-admin" id="imagens-lista" aria-busy="true"></div>
          <div class="campo">
            <label for="arquivo">Adicionar imagens</label>
            <input id="arquivo" type="file" accept="image/jpeg,image/png,image/webp" multiple>
            <span class="campo__dica">JPG, PNG ou WEBP, até 2 MB cada. Pode escolher várias de uma vez.</span>
          </div>
        </div>

        <div class="form-acoes" style="margin-top:24px">
          <button class="btn btn--escuro" type="submit"><?= $produtoId ? 'Salvar alterações' : 'Criar produto' ?></button>
          <a class="btn btn--branco" href="/admin/produtos">Voltar</a>
        </div>
      </form>
    </div>
  </div>
</section>
<?php
$scripts = ['admin.js'];
require __DIR__ . '/../../../includes/rodape.php';
