// js/checkout.js — finalizar compra.
// Confere o carrinho (POST /api/carrinho/validar), lista os endereços (GET /api/enderecos)
// e cria o pedido (POST /api/pedidos). Só id e quantidade vão para a API; o total é dela.

(() => {
  const listaEnd = document.getElementById('checkout-enderecos');
  const corpo = document.getElementById('checkout-itens');
  const botao = document.getElementById('confirmar');
  const aviso = document.getElementById('aviso');
  if (!listaEnd || !corpo || !botao) return;

  let validado = null;
  let enderecos = [];

  function desenharItens() {
    corpo.replaceChildren();
    validado.itens.forEach((i) => {
      const tr = el('tr');
      const nome = el('td');
      nome.append(el('strong', '', i.nome), el('br'), document.createTextNode('Tamanho ' + i.tamanho));
      tr.append(nome, el('td', '', String(i.quantidade)), el('td', '', preco(i.subtotal)));
      corpo.appendChild(tr);
    });
    document.getElementById('checkout-qtd').textContent = String(validado.itens.reduce((s, i) => s + i.quantidade, 0));
    document.getElementById('checkout-total').textContent = preco(validado.total);
  }

  function desenharEnderecos() {
    listaEnd.replaceChildren();
    listaEnd.setAttribute('aria-busy', 'false');
    if (enderecos.length === 0) {
      mostrarEstado(listaEnd, {
        titulo: 'Nenhum endereço',
        texto: 'Cadastre um endereço para receber o pedido.',
        acao: { texto: 'Cadastrar endereço', href: '/conta/enderecos/novo?voltar=/checkout' },
      });
      return;
    }
    enderecos.forEach((e, i) => {
      const rotulo = el('label', 'opcao-endereco');
      const radio = document.createElement('input');
      radio.type = 'radio';
      radio.name = 'endereco';
      radio.value = String(e.id);
      radio.checked = e.principal ? true : i === 0 && !enderecos.some((x) => x.principal);
      radio.addEventListener('change', atualizarBotao);
      const texto = el('div');
      texto.appendChild(el('strong', '', (e.rotulo || 'Endereço') + (e.principal ? ' (principal)' : '')));
      textoEndereco(e).forEach((linha) => texto.appendChild(el('span', '', linha)));
      rotulo.append(radio, texto);
      listaEnd.appendChild(rotulo);
    });
  }

  function enderecoEscolhido() {
    const r = listaEnd.querySelector('input[name="endereco"]:checked');
    return r ? Number(r.value) : null;
  }

  function atualizarBotao() {
    botao.disabled = !(validado && enderecoEscolhido());
  }

  async function iniciar() {
    if (Carrinho.ler().length === 0) {
      mostrarEstado(document.getElementById('checkout-layout'), {
        titulo: 'Carrinho vazio',
        texto: 'Adicione produtos antes de finalizar a compra.',
        acao: { texto: 'Ver produtos', href: '/produtos' },
      });
      return;
    }
    try {
      const [v, e] = await Promise.all([
        api('/carrinho/validar', { metodo: 'POST', corpo: { itens: Carrinho.paraApi() } }),
        api('/enderecos'),
      ]);
      validado = v;
      enderecos = Array.isArray(e) ? e : e.enderecos || [];
      desenharItens();
      desenharEnderecos();
      atualizarBotao();
    } catch (erro) {
      if (erro.status === 401) return;
      if (erro.status === 422) {
        mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
        const voltar = el('a', 'btn', 'Ajustar carrinho');
        voltar.href = '/carrinho';
        aviso.appendChild(voltar);
        return;
      }
      mostrarErroDeApi(document.getElementById('checkout-layout'), erro, iniciar);
    }
  }

  botao.addEventListener('click', async () => {
    const enderecoId = enderecoEscolhido();
    if (!enderecoId) {
      mostrarAviso(aviso, 'erro', 'Escolha um endereço de entrega.');
      return;
    }
    esconderAviso(aviso);
    ocupado(botao, true, 'Enviando pedido...');
    try {
      const pedido = await api('/pedidos', { metodo: 'POST', corpo: { endereco_id: enderecoId, itens: Carrinho.paraApi() } });
      Carrinho.limpar();
      window.location.href = '/pedido-realizado?id=' + encodeURIComponent(pedido.id);
    } catch (erro) {
      ocupado(botao, false);
      if (erro.status === 401) return;
      mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
    }
  });

  iniciar();
})();
