// js/carrinho-pagina.js — tela do carrinho.
// Os itens vêm do localStorage (js/carrinho.js). Preço e estoque valem os da API
// (POST /api/carrinho/validar): o navegador mostra, mas nunca decide o total.

(() => {
  const listaEl = document.getElementById('carrinho-itens');
  const resumoEl = document.getElementById('carrinho-resumo');
  const layout = document.getElementById('carrinho-layout');
  const avisoEl = document.getElementById('carrinho-aviso');
  if (!listaEl || !resumoEl) return;

  let validado = null; // resposta da API: { itens, total }
  let problemas = [];  // mensagens do 422

  function vazio() {
    layout.style.gridTemplateColumns = '1fr';
    resumoEl.hidden = true;
    mostrarEstado(listaEl, {
      titulo: 'Carrinho vazio',
      texto: 'Você ainda não adicionou nada. Que tal dar uma olhada nas novidades?',
      acao: { texto: 'Ver produtos', href: '/produtos' },
    });
  }

  function linhaItem(item, servidor) {
    const caixa = el('article', 'item');
    const foto = el('div', 'item__foto');
    colocarImagem(foto, item.imagem, item.nome, item.variacao_id);

    const meio = el('div');
    meio.appendChild(el('p', 'item__nome', item.nome || 'Produto'));
    meio.appendChild(el('p', 'item__detalhe', 'Tamanho ' + item.tamanho));

    const unitario = servidor ? servidor.preco_unitario : item.preco;
    if (unitario !== undefined) meio.appendChild(el('p', 'item__detalhe', preco(unitario) + ' cada'));

    const linha = el('div', 'item__linha');
    const menos = el('button', '', '−');
    const mais = el('button', '', '+');
    menos.type = mais.type = 'button';
    menos.setAttribute('aria-label', 'Diminuir quantidade de ' + item.nome);
    mais.setAttribute('aria-label', 'Aumentar quantidade de ' + item.nome);
    menos.disabled = item.quantidade <= 1;
    mais.disabled = item.quantidade >= Carrinho.MAX_POR_ITEM;
    menos.addEventListener('click', () => mudar(item.variacao_id, item.quantidade - 1));
    mais.addEventListener('click', () => mudar(item.variacao_id, item.quantidade + 1));
    const qtd = el('div', 'qtd');
    qtd.append(menos, el('output', '', String(item.quantidade)), mais);

    const remover = el('button', 'item__remover', 'Remover');
    remover.type = 'button';
    remover.addEventListener('click', () => {
      Carrinho.remover(item.variacao_id);
      iniciar();
    });
    linha.append(qtd, remover);
    meio.appendChild(linha);

    const total = servidor ? servidor.subtotal : item.preco !== undefined ? item.preco * item.quantidade : null;
    const direita = el('p', 'item__total', total !== null ? preco(total) : '');

    caixa.append(foto, meio, direita);
    return caixa;
  }

  function desenhar() {
    const itens = Carrinho.ler();
    if (itens.length === 0) return vazio();

    layout.style.gridTemplateColumns = '';
    resumoEl.hidden = false;

    const porId = new Map(((validado && validado.itens) || []).map((i) => [i.variacao_id, i]));
    listaEl.replaceChildren(...itens.map((i) => linhaItem(i, porId.get(i.variacao_id))));
    listaEl.setAttribute('aria-busy', 'false');

    const qtdTotal = Carrinho.totalDeItens();
    const total = validado ? validado.total : itens.reduce((s, i) => s + (i.preco || 0) * i.quantidade, 0);

    resumoEl.replaceChildren();
    resumoEl.appendChild(el('h2', 'resumo__titulo', 'Resumo'));
    const linhaQtd = el('div', 'resumo__linha');
    linhaQtd.append(el('span', '', qtdTotal === 1 ? '1 item' : qtdTotal + ' itens'), el('span', '', preco(total)));
    const linhaFrete = el('div', 'resumo__linha');
    linhaFrete.append(el('span', '', 'Frete'), el('span', '', 'Grátis'));
    const linhaTotal = el('div', 'resumo__total');
    linhaTotal.append(el('span', '', 'Total'), el('span', '', preco(total)));
    resumoEl.append(linhaQtd, linhaFrete, linhaTotal);

    const finalizar = el('a', 'btn btn--escuro btn--grande', 'Finalizar compra');
    finalizar.href = '/checkout';
    if (problemas.length) {
      finalizar.setAttribute('aria-disabled', 'true');
      finalizar.style.opacity = '0.5';
      finalizar.style.pointerEvents = 'none';
    }
    const continuar = el('a', 'btn btn--branco', 'Continuar comprando');
    continuar.href = '/produtos';
    resumoEl.append(finalizar, continuar);
    resumoEl.appendChild(el('p', 'resumo__nota', 'O valor final é conferido pela loja ao fechar o pedido.'));
  }

  function mudar(variacaoId, quantidade) {
    Carrinho.definirQuantidade(variacaoId, quantidade);
    iniciar();
  }

  async function iniciar() {
    const itens = Carrinho.ler();
    validado = null;
    problemas = [];
    esconderAviso(avisoEl);
    if (itens.length === 0) return vazio();

    desenhar(); // mostra já o que está salvo, depois confere com a API
    try {
      validado = await api('/carrinho/validar', { metodo: 'POST', corpo: { itens: Carrinho.paraApi() }, redirecionar401: false });
    } catch (erro) {
      if (erro.status === 422) {
        problemas = erro.erros && erro.erros.length ? erro.erros : [erro.message];
        mostrarAviso(avisoEl, 'erro', problemas);
      } else {
        mostrarAviso(avisoEl, 'info', 'Não deu para conferir os preços agora. Os valores abaixo podem estar desatualizados.');
      }
    }
    desenhar();
  }

  iniciar();
})();
