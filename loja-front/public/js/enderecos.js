// js/enderecos.js — lista, cadastro e edição de endereços.
// GET/POST /api/enderecos, PUT/DELETE /api/enderecos/{id}.

(() => {
  const aviso = document.getElementById('aviso');
  const params = new URLSearchParams(window.location.search);

  function formatarCep(valor) {
    const d = soDigitos(valor).slice(0, 8);
    return d.length > 5 ? d.slice(0, 5) + '-' + d.slice(5) : d;
  }

  /* ---------- lista ---------- */
  const lista = document.getElementById('enderecos-lista');
  if (lista) {
    const carregar = async () => {
      mostrarCarregandoGrade(lista, 2);
      try {
        const dados = await api('/enderecos');
        const itens = Array.isArray(dados) ? dados : dados.enderecos || [];
        if (itens.length === 0) {
          mostrarEstado(lista, {
            titulo: 'Nenhum endereço',
            texto: 'Cadastre um endereço para agilizar suas compras.',
            acao: { texto: 'Cadastrar endereço', href: '/conta/enderecos/novo' },
          });
          return;
        }
        lista.replaceChildren(...itens.map(cartao));
        lista.setAttribute('aria-busy', 'false');
      } catch (erro) {
        if (erro.status !== 401) mostrarErroDeApi(lista, erro, carregar);
      }
    };

    const cartao = (e) => {
      const caixa = el('article', 'linha-cartao');
      const texto = el('div');
      texto.appendChild(el('p', 'linha-cartao__titulo', (e.rotulo || 'Endereço') + (e.principal ? ' · principal' : '')));
      textoEndereco(e).forEach((l) => texto.appendChild(el('p', 'linha-cartao__sub', l)));

      const acoes = el('div', 'linha-cartao__acoes');
      const editar = el('a', 'btn btn--pequeno', 'Editar');
      editar.href = '/conta/enderecos/' + encodeURIComponent(e.id);
      const excluir = el('button', 'btn btn--pequeno btn--perigo', 'Excluir');
      excluir.type = 'button';
      excluir.addEventListener('click', async () => {
        if (!window.confirm('Excluir este endereço?')) return;
        ocupado(excluir, true, '...');
        try {
          await api('/enderecos/' + e.id, { metodo: 'DELETE' });
          mostrarAviso(aviso, 'ok', 'Endereço excluído.');
          carregar();
        } catch (erro) {
          ocupado(excluir, false);
          if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
        }
      });
      acoes.append(editar, excluir);
      caixa.append(texto, acoes);
      return caixa;
    };

    carregar();
  }

  /* ---------- formulário ---------- */
  const form = document.getElementById('form-endereco');
  if (form) {
    const id = parseInt(form.dataset.id, 10) || 0;
    const f = form.elements;
    f.cep.addEventListener('input', () => { f.cep.value = formatarCep(f.cep.value); });

    async function preencher() {
      try {
        const dados = await api('/enderecos');
        const itens = Array.isArray(dados) ? dados : dados.enderecos || [];
        const e = itens.find((x) => Number(x.id) === id);
        if (!e) {
          mostrarAviso(aviso, 'erro', 'Endereço não encontrado.');
          form.hidden = true;
          return;
        }
        ['rotulo', 'logradouro', 'numero', 'complemento', 'bairro', 'cidade', 'uf'].forEach((c) => { f[c].value = e[c] || ''; });
        f.cep.value = formatarCep(e.cep);
        f.principal.checked = !!e.principal;
      } catch (erro) {
        if (erro.status !== 401) mostrarAviso(aviso, 'erro', erro.message);
      }
    }
    if (id) preencher();

    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const botao = form.querySelector('button[type="submit"]');
      const corpo = {
        rotulo: f.rotulo.value.trim(),
        cep: soDigitos(f.cep.value),
        logradouro: f.logradouro.value.trim(),
        numero: f.numero.value.trim(),
        complemento: f.complemento.value.trim(),
        bairro: f.bairro.value.trim(),
        cidade: f.cidade.value.trim(),
        uf: f.uf.value.trim().toUpperCase(),
        principal: f.principal.checked,
      };
      const erros = [];
      if (corpo.cep.length !== 8) erros.push('O CEP deve ter 8 números.');
      if (!corpo.logradouro) erros.push('Informe a rua.');
      if (!corpo.numero) erros.push('Informe o número.');
      if (!corpo.bairro) erros.push('Informe o bairro.');
      if (!corpo.cidade) erros.push('Informe a cidade.');
      if (!/^[A-Z]{2}$/.test(corpo.uf)) erros.push('A UF deve ter 2 letras.');
      if (erros.length) {
        mostrarAviso(aviso, 'erro', erros);
        return;
      }
      esconderAviso(aviso);
      ocupado(botao, true, 'Salvando...');
      try {
        await api(id ? '/enderecos/' + id : '/enderecos', { metodo: id ? 'PUT' : 'POST', corpo });
        window.location.href = destinoSeguro(params.get('voltar'), '/conta/enderecos');
      } catch (erro) {
        ocupado(botao, false);
        if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
      }
    });
  }
})();
