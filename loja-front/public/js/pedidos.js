// js/pedidos.js — "Meus pedidos" (lista) e "Pedido" (detalhe + cancelar).
// GET /api/pedidos, GET /api/pedidos/{id}, PUT /api/pedidos/{id}/cancelar.

(() => {
  /* ---------- lista ---------- */
  const lista = document.getElementById('pedidos-lista');
  if (lista) {
    const carregarLista = async () => {
      mostrarCarregandoGrade(lista, 3);
      try {
        const dados = await api('/pedidos');
        const pedidos = Array.isArray(dados) ? dados : dados.pedidos || [];
        if (pedidos.length === 0) {
          mostrarEstado(lista, {
            titulo: 'Nenhum pedido ainda',
            texto: 'Quando você comprar, seus pedidos aparecem aqui.',
            acao: { texto: 'Ver produtos', href: '/produtos' },
          });
          return;
        }
        lista.replaceChildren(...pedidos.map((p) => {
          const a = el('a', 'linha-cartao');
          a.href = '/meus-pedidos/' + encodeURIComponent(p.id);
          const esq = el('div');
          esq.appendChild(el('p', 'linha-cartao__titulo', 'Pedido #' + p.id));
          esq.appendChild(el('p', 'linha-cartao__sub', formatarData(p.data) + ' · ' + preco(p.total)));
          a.append(esq, seloStatus(p.status));
          return a;
        }));
        lista.setAttribute('aria-busy', 'false');
      } catch (erro) {
        if (erro.status === 401) return;
        mostrarErroDeApi(lista, erro, carregarLista);
      }
    };
    carregarLista();
  }

  /* ---------- detalhe ---------- */
  const detalhe = document.getElementById('pedido-detalhe');
  if (detalhe) {
    const conteudo = document.getElementById('pedido-conteudo');
    const aviso = document.getElementById('aviso');
    const id = parseInt(detalhe.dataset.id, 10);

    const carregarDetalhe = async () => {
      mostrarCarregandoGrade(conteudo, 2);
      try {
        desenhar(await api('/pedidos/' + id));
      } catch (erro) {
        if (erro.status === 401) return;
        if (erro.status === 404) {
          mostrarEstado(conteudo, {
            titulo: 'Pedido não encontrado',
            texto: 'Esse pedido não existe ou não é seu.',
            acao: { texto: 'Meus pedidos', href: '/meus-pedidos' },
          });
          return;
        }
        mostrarErroDeApi(conteudo, erro, carregarDetalhe);
      }
    };

    function desenhar(p) {
      detalhe.setAttribute('aria-busy', 'false');
      const cabeca = el('div', 'caixa');
      const topo = el('div', 'admin-barra');
      const dados = el('div');
      dados.appendChild(el('p', 'linha-cartao__titulo', 'Feito em ' + formatarData(p.data)));
      dados.appendChild(el('p', 'linha-cartao__sub', 'Total: ' + preco(p.total)));
      topo.append(dados, seloStatus(p.status));
      cabeca.appendChild(topo);

      const tabela = el('table', 'tabela-itens');
      const thead = el('thead');
      const trh = el('tr');
      ['Produto', 'Qtd', 'Subtotal'].forEach((t) => trh.appendChild(el('th', '', t)));
      thead.appendChild(trh);
      const tbody = el('tbody');
      (p.itens || []).forEach((i) => {
        const tr = el('tr');
        const nome = el('td');
        nome.append(el('strong', '', i.nome), el('br'), document.createTextNode('Tamanho ' + i.tamanho));
        tr.append(nome, el('td', '', String(i.quantidade)), el('td', '', preco(i.preco_unitario * i.quantidade)));
        tbody.appendChild(tr);
      });
      tabela.append(thead, tbody);
      const rolagem = el('div', 'rolagem-x');
      rolagem.appendChild(tabela);
      cabeca.appendChild(rolagem);

      const blocoEnd = el('div', 'caixa');
      blocoEnd.appendChild(el('h2', 'sub-titulo', 'Entrega'));
      textoEndereco(p.endereco || {}).forEach((linha) => blocoEnd.appendChild(el('p', '', linha)));

      conteudo.replaceChildren(cabeca, blocoEnd);

      if (['aguardando_pagamento', 'pago'].includes(p.status)) {
        const cancelar = el('button', 'btn btn--perigo', 'Cancelar pedido');
        cancelar.type = 'button';
        cancelar.addEventListener('click', async () => {
          if (!window.confirm('Cancelar o pedido #' + p.id + '?')) return;
          ocupado(cancelar, true, 'Cancelando...');
          try {
            await api('/pedidos/' + id + '/cancelar', { metodo: 'PUT' });
            mostrarAviso(aviso, 'ok', 'Pedido cancelado.');
            carregarDetalhe();
          } catch (erro) {
            ocupado(cancelar, false);
            if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
          }
        });
        const acoes = el('div', 'form-acoes');
        acoes.style.marginTop = '20px';
        acoes.appendChild(cancelar);
        conteudo.appendChild(acoes);
      }
    }

    carregarDetalhe();
  }
})();
