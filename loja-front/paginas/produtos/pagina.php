<?php
// paginas/produtos/pagina.php — grade de produtos com filtros (categoria, tamanho, preço) e busca.
// Script: js/produtos.js (GET /api/produtos e /api/categorias).
$titulo = 'Produtos';
require __DIR__ . '/../../includes/cabecalho.php';
topoPagina('Todos os produtos', 'Loja', 'f-raio');
?>
<section class="container secao">
  <div class="loja">
    <form class="filtros" id="filtros" role="search" aria-label="Filtros de produtos">
      <h2 class="filtros__titulo">Filtrar</h2>
      <button class="filtros__toggle" type="button" id="filtros-toggle" aria-expanded="false" aria-controls="filtros-corpo" hidden>
        <span>Filtros</span><span class="filtros__badge" id="filtros-ativos" hidden>0</span>
      </button>
      <div class="filtros__corpo" id="filtros-corpo">

      <div class="campo">
        <label for="f-busca">Buscar</label>
        <input id="f-busca" name="busca" type="search" placeholder="Nome do produto" maxlength="80">
      </div>
      <div class="campo">
        <label for="f-categoria">Categoria</label>
        <select id="f-categoria" name="categoria">
          <option value="">Todas</option>
        </select>
      </div>
      <div class="campo">
        <label for="f-marca">Marca</label>
        <select id="f-marca" name="marca">
          <option value="">Todas</option>
        </select>
      </div>
      <div class="campo">
        <label for="f-tamanho">Tamanho</label>
        <select id="f-tamanho" name="tamanho">
          <option value="">Todos</option>
          <option>PP</option><option>P</option><option>M</option><option>G</option><option>GG</option><option>U</option><option>34</option><option>35</option><option>36</option><option>37</option><option>38</option><option>39</option><option>40</option><option>41</option><option>42</option><option>43</option><option>44</option><option>45</option><option>46</option>
        </select>
      </div>
      <div class="filtros__duplo">
        <div class="campo">
          <label for="f-min">Preço mín.</label>
          <input id="f-min" name="preco_min" type="number" min="0" step="1" inputmode="numeric" placeholder="0">
        </div>
        <div class="campo">
          <label for="f-max">Preço máx.</label>
          <input id="f-max" name="preco_max" type="number" min="0" step="1" inputmode="numeric" placeholder="500">
        </div>
      </div>

      <div class="filtros__acoes">
        <button class="btn btn--escuro" type="submit">Aplicar filtros</button>
      <button class="btn btn--branco" type="button" id="limpar-filtros">Limpar</button>
      </div>
      </div>
    </form>

    <div>
      <div class="loja__barra">
        <p id="contagem" aria-live="polite">Carregando...</p>
      </div>
      <div class="grade-produtos" id="produtos-grade" aria-busy="true"></div>
      <nav class="paginacao" id="paginacao" aria-label="Paginação" hidden></nav>
    </div>
  </div>
</section>
<?php
$scripts = ['produtos.js'];
require __DIR__ . '/../../includes/rodape.php';
