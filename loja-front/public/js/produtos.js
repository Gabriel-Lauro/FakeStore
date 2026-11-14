// js/produtos.js — grade, filtros e busca de /produtos (GET /api/produtos).
// Os filtros ficam na URL (?categoria=camisetas&tamanho=M), então dá para compartilhar o link
// e o botão "voltar" do navegador funciona. Usa as funções de js/ui.js.

(() => {
  const grade = document.getElementById('produtos-grade');
  const form = document.getElementById('filtros');
  if (!grade || !form) return;

  const contagem = document.getElementById('contagem');
  const paginacao = document.getElementById('paginacao');
  const CAMPOS = ['busca', 'categoria', 'marca', 'tamanho', 'preco_min', 'preco_max'];

  function lerUrl() {
    const q = new URLSearchParams(window.location.search);
    CAMPOS.forEach((nome) => {
      if (form.elements[nome]) form.elements[nome].value = q.get(nome) || '';
    });
    return Math.max(1, parseInt(q.get('pagina'), 10) || 1);
  }

  function consultaAtual(pagina) {
    const q = new URLSearchParams();
    CAMPOS.forEach((nome) => {
      const valor = (form.elements[nome]?.value || '').trim();
      if (valor !== '') q.set(nome, valor);
    });
    if (pagina > 1) q.set('pagina', String(pagina));
    return q;
  }

  function desenharPaginacao(atual, total) {
    paginacao.replaceChildren();
    paginacao.hidden = total <= 1;
    if (total <= 1) return;
    const botao = (texto, pagina, desativado) => {
      const b = el('button', 'btn btn--branco', texto);
      b.type = 'button';
      b.disabled = desativado;
      b.addEventListener('click', () => ir(pagina));
      return b;
    };
    paginacao.append(
      botao('Anterior', atual - 1, atual <= 1),
      el('span', 'btn btn--escuro', 'Página ' + atual + ' de ' + total),
      botao('Próxima', atual + 1, atual >= total)
    );
  }

  async function carregar(pagina) {
    const q = consultaAtual(pagina);
    mostrarCarregandoGrade(grade, 6);
    contagem.textContent = 'Carregando...';
    atualizarBadge();
    paginacao.hidden = true;
    try {
      const dados = await api('/produtos' + (q.toString() ? '?' + q.toString() : ''));
      const lista = Array.isArray(dados) ? dados : dados.produtos || [];
      const total = Array.isArray(dados) ? lista.length : dados.total ?? lista.length;

      contagem.replaceChildren(el('strong', '', String(total)), document.createTextNode(total === 1 ? ' produto encontrado' : ' produtos encontrados'));

      if (lista.length === 0) {
        mostrarEstado(grade, {
          titulo: 'Nada por aqui',
          texto: 'Nenhum produto bate com esses filtros. Tente mudar ou limpar a busca.',
          acao: { texto: 'Limpar filtros', aoClicar: limpar },
        });
        return;
      }
      grade.replaceChildren(...lista.map(criarCard));
      grade.setAttribute('aria-busy', 'false');
      desenharPaginacao(dados.pagina || pagina, dados.total_paginas || 1);
    } catch (erro) {
      if (erro.status === 401) return;
      contagem.textContent = '';
      mostrarErroDeApi(grade, erro, () => carregar(pagina));
    }
  }

  function ir(pagina) {
    const q = consultaAtual(pagina).toString();
    history.pushState(null, '', window.location.pathname + (q ? '?' + q : ''));
    carregar(pagina);
    if (toggle) abrirFiltros(false);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function limpar() {
    form.reset();
    ir(1);
  }

  async function carregarCategorias() {
    const select = form.elements.categoria;
    try {
      const cats = await api('/categorias');
      const atual = new URLSearchParams(window.location.search).get('categoria') || '';
      (Array.isArray(cats) ? cats : cats.categorias || []).forEach((c) => {
        const o = el('option', '', c.nome);
        o.value = c.slug;
        select.appendChild(o);
      });
      select.value = atual;
    } catch (_) {
      /* sem categorias o filtro fica só com "Todas"; mas se a URL já trazia uma categoria,
         mantém essa opção para o filtro não se perder */
      const atual = new URLSearchParams(window.location.search).get('categoria') || '';
      if (atual) {
        const o = el('option', '', atual);
        o.value = atual;
        select.appendChild(o);
        select.value = atual;
      }
    }
  }

  // Marcas cadastradas no admin. Se a API falhar, mantém só a marca que já veio na URL.
  async function carregarMarcas() {
    const select = form.elements.marca;
    const atual = new URLSearchParams(window.location.search).get('marca') || '';
    try {
      const marcas = await api('/marcas');
      (Array.isArray(marcas) ? marcas : marcas.marcas || []).forEach((m) => {
        const o = el('option', '', m.nome);
        o.value = m.slug;
        select.appendChild(o);
      });
    } catch (_) {
      if (atual) {
        const o = el('option', '', atual);
        o.value = atual;
        select.appendChild(o);
      }
    }
    select.value = atual;
  }

  // Mobile: o painel de filtros fica recolhido; o botao mostra quantos filtros estao ativos.
  // Em telas grandes o CSS ignora tudo isso e o painel aparece sempre aberto.
  const toggle = document.getElementById('filtros-toggle');
  const badge = document.getElementById('filtros-ativos');

  function atualizarBadge() {
    if (!badge) return;
    const n = CAMPOS.filter((nome) => (form.elements[nome]?.value || '').trim() !== '').length;
    badge.textContent = String(n);
    badge.hidden = n === 0;
  }

  function abrirFiltros(abrir) {
    form.classList.toggle('is-aberto', abrir);
    toggle.setAttribute('aria-expanded', String(abrir));
  }

  if (toggle) {
    toggle.hidden = false;
    form.classList.add('filtros--colapsavel');
    toggle.addEventListener('click', () => abrirFiltros(!form.classList.contains('is-aberto')));
  }

  form.addEventListener('submit', (ev) => {
    ev.preventDefault();
    ir(1);
  });
  document.getElementById('limpar-filtros').addEventListener('click', limpar);
  window.addEventListener('popstate', () => carregar(lerUrl()));

  // Espera categorias e marcas entrarem nos <select> ANTES de ler a URL e buscar os produtos.
  // Antes, o select ainda estava vazio e o ?categoria=... da URL era descartado.
  Promise.all([carregarCategorias(), carregarMarcas()]).then(() => carregar(lerUrl()));
})();
