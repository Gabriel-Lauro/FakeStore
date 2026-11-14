// js/admin.js — painel, lista de produtos e formulário de produto (variações e imagens).
// Todas as rotas /api/admin/* exigem perfil admin (a API confere de novo; aqui é só a tela).

(() => {
  const aviso = document.getElementById('aviso');

  function tabela(colunas, linhas) {
    const t = el('table', 'tabela');
    const thead = el('thead');
    const tr = el('tr');
    colunas.forEach((c) => tr.appendChild(el('th', '', c)));
    thead.appendChild(tr);
    const tbody = el('tbody');
    linhas.forEach((l) => tbody.appendChild(l));
    t.append(thead, tbody);
    const rolagem = el('div', 'rolagem-x');
    rolagem.appendChild(t);
    return rolagem;
  }

  const celula = (conteudo) => {
    const td = el('td');
    if (conteudo instanceof Node) td.appendChild(conteudo);
    else td.textContent = conteudo;
    return td;
  };

  /* ================= painel ================= */
  const numeros = document.getElementById('admin-numeros');
  const pedidosBox = document.getElementById('admin-pedidos');
  if (numeros && pedidosBox) {
    const carregar = async () => {
      try {
        const [prod, ped] = await Promise.all([api('/admin/produtos?por_pagina=1'), api('/admin/pedidos')]);
        const pedidos = Array.isArray(ped) ? ped : ped.pedidos || [];

        const cartao = (valor, rotulo) => {
          const c = el('div', 'numero-cartao');
          c.append(el('p', 'numero-cartao__valor', String(valor)), el('p', 'numero-cartao__rotulo', rotulo));
          return c;
        };
        numeros.replaceChildren(
          cartao(prod.total_geral || 0, 'Produtos'),
          cartao(prod.total_ativos || 0, 'Produtos ativos'),
          cartao(pedidos.length, 'Pedidos'),
          cartao(pedidos.filter((p) => p.status === 'aguardando_pagamento').length, 'Aguardando pagamento')
        );
        numeros.setAttribute('aria-busy', 'false');

        if (pedidos.length === 0) {
          mostrarEstado(pedidosBox, { titulo: 'Sem pedidos', texto: 'Os pedidos dos clientes aparecem aqui.' });
          return;
        }
        pedidosBox.replaceChildren(
          tabela(['Pedido', 'Data', 'Status', 'Total'], pedidos.slice(0, 8).map((p) => {
            const tr = el('tr');
            tr.append(celula('#' + p.id), celula(formatarData(p.data)), celula(seloStatus(p.status)), celula(preco(p.total)));
            return tr;
          }))
        );
        pedidosBox.setAttribute('aria-busy', 'false');
      } catch (erro) {
        if (erro.status === 401) return;
        mostrarErroDeApi(pedidosBox, erro, carregar);
        numeros.replaceChildren();
      }
    };
    carregar();
  }

  /* ================= lista de produtos ================= */
  const listaProdutos = document.getElementById('admin-produtos');
  if (listaProdutos) {
    const paginacao = document.getElementById('admin-paginacao');
    const contagem = document.getElementById('admin-contagem');
    const formBusca = document.getElementById('admin-busca');
    let pagina = 1;
    let busca = '';

    // Foto pequena da linha; sem foto (ou foto quebrada) mostra um quadrado cinza com "sem foto".
    const miniatura = (caminho) => {
      const caixa = el('div', 'miniatura');
      const vazia = () => {
        caixa.classList.add('miniatura--vazia');
        caixa.replaceChildren(document.createTextNode('sem foto'));
      };
      if (!caminho) {
        vazia();
        return caixa;
      }
      const img = document.createElement('img');
      img.src = urlImagem(caminho);
      img.alt = '';
      img.loading = 'lazy';
      img.addEventListener('error', vazia);
      caixa.appendChild(img);
      return caixa;
    };

    function desenharPaginacao(atual, total) {
      paginacao.replaceChildren();
      paginacao.hidden = total <= 1;
      if (total <= 1) return;
      const botao = (texto, destino, desativado) => {
        const b = el('button', 'btn btn--branco', texto);
        b.type = 'button';
        b.disabled = desativado;
        b.addEventListener('click', () => { pagina = destino; carregar(); });
        return b;
      };
      paginacao.append(
        botao('Anterior', atual - 1, atual <= 1),
        el('span', 'btn btn--escuro', 'Página ' + atual + ' de ' + total),
        botao('Próxima', atual + 1, atual >= total)
      );
    }

    const carregar = async () => {
      mostrarCarregandoGrade(listaProdutos, 3);
      paginacao.hidden = true;
      try {
        const q = new URLSearchParams({ pagina: String(pagina) });
        if (busca) q.set('busca', busca);
        const dados = await api('/admin/produtos?' + q.toString());
        const produtos = dados.produtos || [];
        pagina = dados.pagina || pagina;
        const n = dados.total || 0;
        contagem.textContent = n + (busca
          ? (n === 1 ? ' produto encontrado' : ' produtos encontrados')
          : (n === 1 ? ' produto no catálogo' : ' produtos no catálogo'));
        if (produtos.length === 0) {
          if (busca) {
            mostrarEstado(listaProdutos, { titulo: 'Nada encontrado', texto: 'Nenhum produto tem esse nome. Tente outra busca.' });
            return;
          }
          mostrarEstado(listaProdutos, {
            titulo: 'Nenhum produto',
            texto: 'Cadastre o primeiro produto da loja.',
            acao: { texto: 'Novo produto', href: '/admin/produtos/novo' },
          });
          return;
        }
        listaProdutos.replaceChildren(
          tabela(['Foto', 'Produto', 'Preço', 'Estoque', 'Status', 'Ações'], produtos.map((p) => {
            const tr = el('tr');
            const nome = el('div');
            nome.append(el('strong', '', p.nome), el('br'), document.createTextNode((p.categoria || '') + (p.marca ? ' · ' + p.marca : '')));
            const status = el('span', 'selo-status ' + (p.ativo ? 'selo-status--ok' : ''), p.ativo ? 'Ativo' : 'Inativo');

            const editar = el('a', 'btn btn--pequeno', 'Editar');
            editar.href = '/admin/produtos/' + encodeURIComponent(p.id);
            const excluir = el('button', 'btn btn--pequeno btn--perigo', 'Excluir');
            excluir.type = 'button';
            excluir.addEventListener('click', async () => {
              if (!window.confirm('Excluir "' + p.nome + '"? Isso não pode ser desfeito.')) return;
              ocupado(excluir, true, '...');
              try {
                await api('/admin/produtos/' + p.id, { metodo: 'DELETE' });
                mostrarAviso(aviso, 'ok', 'Produto excluído.');
                carregar();
              } catch (erro) {
                ocupado(excluir, false);
                if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
              }
            });
            const acoes = el('div', 'tabela__acoes');
            acoes.append(editar, excluir);

            tr.append(celula(miniatura(p.imagem)), celula(nome), celula(preco(p.preco)), celula(String(p.estoque_total || 0)), celula(status), celula(acoes));
            return tr;
          }))
        );
        listaProdutos.setAttribute('aria-busy', 'false');
        desenharPaginacao(dados.pagina || 1, dados.total_paginas || 1);
      } catch (erro) {
        if (erro.status !== 401) mostrarErroDeApi(listaProdutos, erro, carregar);
      }
    };
    if (formBusca) {
      formBusca.addEventListener('submit', (ev) => {
        ev.preventDefault();
        busca = document.getElementById('admin-busca-texto').value.trim();
        pagina = 1;
        carregar();
      });
    }
    carregar();
  }

  /* ================= formulário de produto ================= */
  // Um formulário só: dados, tamanhos e fotos, com um único botão "Salvar".
  // Criar: POST do produto e depois tamanhos e fotos.
  // Editar: PATCH só com os campos que mudaram + o que foi adicionado, alterado ou removido
  // nos tamanhos e nas fotos. Nada é enviado antes de clicar em Salvar.
  const form = document.getElementById('form-produto');
  if (form) {
    const id = parseInt(form.dataset.id, 10) || 0;
    const f = form.elements;
    let original = null; // dados do produto como estão no servidor (só na edição)
    let vars = [];       // { id, tamanho, estoque, orig, apagar }
    let imgs = [];       // { id, caminho, arquivo, url, apagar }
    const texto = (e) => [].concat(mensagensDoErro(e)).join(' ');
    const ligado = (x) => x === true || x === 1 || x === '1';

    async function carregarCategorias() {
      try {
        const cats = await api('/categorias');
        (Array.isArray(cats) ? cats : cats.categorias || []).forEach((c) => {
          const o = el('option', '', c.nome);
          o.value = String(c.id);
          f.categoria_id.appendChild(o);
        });
      } catch (_) {
        mostrarAviso(aviso, 'erro', 'Não deu para carregar as categorias.');
      }
    }

    async function carregarMarcas() {
      try {
        const marcas = await api('/marcas');
        (Array.isArray(marcas) ? marcas : marcas.marcas || []).forEach((m) => {
          const o = el('option', '', m.nome);
          o.value = String(m.id);
          f.marca_id.appendChild(o);
        });
      } catch (_) {
        mostrarAviso(aviso, 'erro', 'Não deu para carregar as marcas.');
      }
    }

    // Os mesmos campos, no mesmo formato, vindos do servidor ou da tela (para comparar o que mudou).
    const dadosDe = (p) => ({
      nome: String(p.nome || '').trim(),
      categoria_id: Number(p.categoria_id) || null,
      marca_id: Number(p.marca_id) || null, // vazio = produto sem marca
      descricao: String(p.descricao || '').trim(),
      preco: Number(p.preco),
      preco_original: p.preco_original === null || p.preco_original === undefined || String(p.preco_original).trim() === '' ? null : Number(p.preco_original),
      destaque: ligado(p.destaque),
      ativo: ligado(p.ativo),
    });

    function lerCampos() {
      return dadosDe({
        nome: f.nome.value,
        categoria_id: f.categoria_id.value,
        marca_id: f.marca_id.value,
        descricao: f.descricao.value,
        preco: f.preco.value,
        preco_original: f.preco_original.value,
        destaque: f.destaque.checked,
        ativo: f.ativo.checked,
      });
    }

    function preencher(p) {
      f.nome.value = p.nome || '';
      f.categoria_id.value = String(p.categoria_id || '');
      f.marca_id.value = String(p.marca_id || '');
      f.descricao.value = p.descricao || '';
      f.preco.value = p.preco ?? '';
      f.preco_original.value = p.preco_original ?? '';
      f.destaque.checked = ligado(p.destaque);
      f.ativo.checked = ligado(p.ativo);
      original = dadosDe(p);
    }

    /* ----- tamanhos ----- */
    const varLista = document.getElementById('variacoes-lista');
    function desenharVariacoes() {
      if (!varLista) return;
      varLista.setAttribute('aria-busy', 'false');
      if (vars.length === 0) {
        varLista.replaceChildren(el('p', 'campo__dica', 'Nenhum tamanho ainda. Adicione abaixo.'));
        return;
      }
      varLista.replaceChildren(
        tabela(['Tamanho', 'Estoque', ''], vars.map((v) => {
          const tr = el('tr');
          if (v.apagar) tr.style.opacity = '0.45';
          const entrada = document.createElement('input');
          entrada.type = 'number';
          entrada.min = '0';
          entrada.step = '1';
          entrada.value = String(v.estoque);
          entrada.style.width = '90px';
          entrada.disabled = v.apagar;
          entrada.setAttribute('aria-label', 'Estoque de ' + v.tamanho);
          entrada.addEventListener('input', () => { v.estoque = Number(entrada.value); });

          const nome = el('span', '', v.tamanho + (v.id ? '' : ' (novo)') + (v.apagar ? ' (será excluído)' : ''));
          if (v.apagar) nome.style.textDecoration = 'line-through';

          const botao = el('button', 'btn btn--pequeno ' + (v.apagar ? 'btn--branco' : 'btn--perigo'), v.apagar ? 'Desfazer' : 'Excluir');
          botao.type = 'button';
          botao.addEventListener('click', () => {
            if (v.id) v.apagar = !v.apagar;
            else vars = vars.filter((x) => x !== v);
            desenharVariacoes();
          });
          tr.append(celula(nome), celula(entrada), celula(botao));
          return tr;
        }))
      );
    }

    const botaoAddVar = document.getElementById('v-add');
    if (botaoAddVar) {
      botaoAddVar.addEventListener('click', () => {
        const tamanho = document.getElementById('v-tamanho').value;
        const campoEstoque = document.getElementById('v-estoque');
        const estoque = Number(campoEstoque.value);
        if (!Number.isInteger(estoque) || estoque < 0) {
          mostrarAviso(aviso, 'erro', 'O estoque deve ser um número inteiro, sem negativo.');
          return;
        }
        if (vars.some((x) => !x.apagar && x.tamanho === tamanho)) {
          mostrarAviso(aviso, 'erro', 'O tamanho ' + tamanho + ' já está na lista.');
          return;
        }
        vars.push({ id: 0, tamanho, estoque, orig: estoque, apagar: false });
        campoEstoque.value = '0';
        esconderAviso(aviso);
        desenharVariacoes();
      });
    }

    /* ----- fotos ----- */
    const imgLista = document.getElementById('imagens-lista');
    function desenharImagens() {
      if (!imgLista) return;
      imgLista.setAttribute('aria-busy', 'false');
      if (imgs.length === 0) {
        imgLista.replaceChildren(el('p', 'campo__dica', 'Nenhuma imagem ainda.'));
        return;
      }
      imgLista.replaceChildren(...imgs.map((img) => {
        const caixa = el('div', 'imagem-admin');
        if (img.apagar) caixa.style.opacity = '0.45';
        const foto = el('div', 'item__foto');
        if (img.arquivo) {
          const previa = document.createElement('img');
          previa.src = img.url;
          previa.alt = '';
          foto.appendChild(previa);
        } else {
          colocarImagem(foto, img.caminho, '', img.id);
        }
        const botao = el('button', 'btn btn--pequeno ' + (img.apagar ? 'btn--branco' : 'btn--perigo'), img.apagar ? 'Desfazer' : 'Excluir');
        botao.type = 'button';
        botao.addEventListener('click', () => {
          if (img.id) {
            img.apagar = !img.apagar;
          } else {
            URL.revokeObjectURL(img.url);
            imgs = imgs.filter((x) => x !== img);
          }
          desenharImagens();
        });
        caixa.append(foto, botao);
        if (!img.id) caixa.appendChild(el('p', 'campo__dica', 'nova'));
        return caixa;
      }));
    }

    const campoArquivos = document.getElementById('arquivo');
    if (campoArquivos) {
      campoArquivos.addEventListener('change', () => {
        const arquivos = Array.from(campoArquivos.files);
        if (arquivos.length === 0) return;
        const erros = [];
        arquivos.forEach((a) => {
          if (!['image/jpeg', 'image/png', 'image/webp'].includes(a.type)) erros.push(a.name + ': use uma imagem JPG, PNG ou WEBP.');
          else if (a.size > 2 * 1024 * 1024) erros.push(a.name + ': a imagem passa de 2 MB.');
          else imgs.push({ id: 0, caminho: null, arquivo: a, url: URL.createObjectURL(a), apagar: false });
        });
        campoArquivos.value = '';
        if (erros.length) mostrarAviso(aviso, 'erro', erros);
        else esconderAviso(aviso);
        desenharImagens();
      });
    }

    /* ----- carregar (edição) ----- */
    async function recarregar() {
      try {
        const p = await api('/admin/produtos/' + id);
        imgs.forEach((i) => { if (i.arquivo) URL.revokeObjectURL(i.url); });
        preencher(p);
        vars = (p.variacoes || []).map((v) => ({ id: v.id, tamanho: v.tamanho, estoque: Number(v.estoque), orig: Number(v.estoque), apagar: false }));
        imgs = (p.imagens || []).map((i) => ({ id: i.id, caminho: i.caminho, arquivo: null, url: null, apagar: false }));
        desenharVariacoes();
        desenharImagens();
        return true;
      } catch (erro) {
        if (erro.status === 404) {
          mostrarAviso(aviso, 'erro', 'Produto não encontrado.');
          form.hidden = true;
        } else if (erro.status !== 401) {
          mostrarAviso(aviso, 'erro', erro.message);
        }
        return false;
      }
    }

    /* ----- salvar (único botão) ----- */
    form.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const botao = form.querySelector('button[type="submit"]');
      const dados = lerCampos();
      const ativas = vars.filter((v) => !v.apagar);

      const erros = [];
      if (dados.nome.length < 2) erros.push('Informe o nome do produto.');
      if (!dados.categoria_id) erros.push('Escolha uma categoria.');
      if (!(dados.preco > 0)) erros.push('O preço deve ser maior que zero.');
      if (dados.preco_original !== null && dados.preco_original < dados.preco) erros.push('O preço original não pode ser menor que o preço.');
      ativas.forEach((v) => {
        if (!Number.isInteger(v.estoque) || v.estoque < 0) erros.push('Estoque inválido no tamanho ' + v.tamanho + ': use um inteiro, sem negativo.');
      });
      if (erros.length) {
        mostrarAviso(aviso, 'erro', erros);
        return;
      }

      // O que mudou? (só importa na edição)
      const mudou = {};
      if (id) Object.keys(dados).forEach((k) => { if (dados[k] !== original[k]) mudou[k] = dados[k]; });
      const apagarVars = vars.filter((v) => v.id && v.apagar);
      const estoques = ativas.filter((v) => v.id && v.estoque !== v.orig);
      const novasVars = ativas.filter((v) => !v.id);
      const apagarImgs = imgs.filter((i) => i.id && i.apagar);
      const novasImgs = imgs.filter((i) => !i.id);

      if (id && Object.keys(mudou).length === 0 && !apagarVars.length && !estoques.length && !novasVars.length && !apagarImgs.length && !novasImgs.length) {
        mostrarAviso(aviso, 'info', 'Nada foi alterado.');
        return;
      }

      esconderAviso(aviso);
      ocupado(botao, true, 'Salvando...');
      const falhas = [];
      const tentar = async (rotulo, acao) => {
        try {
          await acao();
        } catch (e) {
          if (e.status === 401) throw e;
          falhas.push(rotulo + ': ' + texto(e));
        }
      };
      const enviarFoto = (produtoId, im) => {
        const corpo = new FormData();
        corpo.append('imagem', im.arquivo);
        return api('/admin/produtos/' + produtoId + '/imagens', { metodo: 'POST', form: corpo });
      };

      try {
        if (!id) {
          const novo = await api('/admin/produtos', { metodo: 'POST', corpo: dados });
          for (const v of novasVars) {
            await tentar('Tamanho ' + v.tamanho, () => api('/admin/produtos/' + novo.id + '/variacoes', { metodo: 'POST', corpo: { tamanho: v.tamanho, estoque: v.estoque } }));
          }
          for (const im of novasImgs) {
            await tentar('Imagem ' + im.arquivo.name, () => enviarFoto(novo.id, im));
          }
          try {
            if (falhas.length) sessionStorage.setItem('admin-aviso', 'Produto criado, mas nem tudo foi salvo. ' + falhas.join(' | '));
            else sessionStorage.setItem('admin-criado', String(novo.id));
          } catch (_) { /* ignora */ }
          window.location.href = '/admin/produtos/' + encodeURIComponent(novo.id);
          return;
        }

        // Edição: PATCH só com o que mudou. Se o servidor recusar, nada mais é enviado.
        if (Object.keys(mudou).length) {
          await api('/admin/produtos/' + id, { metodo: 'PATCH', corpo: mudou });
        }
        for (const v of apagarVars) {
          await tentar('Excluir tamanho ' + v.tamanho, () => api('/admin/variacoes/' + v.id, { metodo: 'DELETE' }));
        }
        for (const v of estoques) {
          await tentar('Estoque do ' + v.tamanho, () => api('/admin/variacoes/' + v.id, { metodo: 'PATCH', corpo: { estoque: v.estoque } }));
        }
        for (const v of novasVars) {
          await tentar('Tamanho ' + v.tamanho, () => api('/admin/produtos/' + id + '/variacoes', { metodo: 'POST', corpo: { tamanho: v.tamanho, estoque: v.estoque } }));
        }
        for (const im of apagarImgs) {
          await tentar('Excluir imagem', () => api('/admin/imagens/' + im.id, { metodo: 'DELETE' }));
        }
        for (const im of novasImgs) {
          await tentar('Imagem ' + im.arquivo.name, () => enviarFoto(id, im));
        }
        const ok = await recarregar();
        if (!ok) return;
        if (falhas.length) mostrarAviso(aviso, 'erro', ['Salvo, mas nem tudo deu certo:'].concat(falhas));
        else mostrarAviso(aviso, 'ok', 'Alterações salvas.');
      } catch (erro) {
        if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
      } finally {
        ocupado(botao, false);
      }
    });

    // Aviso guardado antes do redirecionamento (produto criado, com ou sem falhas).
    try {
      const guardado = sessionStorage.getItem('admin-aviso');
      if (guardado) {
        sessionStorage.removeItem('admin-aviso');
        mostrarAviso(aviso, 'info', guardado);
      } else if (id && sessionStorage.getItem('admin-criado') === String(id)) {
        sessionStorage.removeItem('admin-criado');
        mostrarAviso(aviso, 'ok', 'Produto criado com tamanhos e fotos.');
      }
    } catch (_) { /* ignora */ }

    desenharVariacoes();
    desenharImagens();
    Promise.all([carregarCategorias(), carregarMarcas()]).then(() => { if (id) recarregar(); });
  }

  /* ================= marcas (cadastro) ================= */
  // Lista, cria, renomeia e exclui marcas. Excluir só funciona se a marca não tem produtos (a API responde 409).
  const marcasBox = document.getElementById('admin-marcas');
  if (marcasBox) {
    const formMarca = document.getElementById('form-marca');
    const campoNome = document.getElementById('marca-nome');
    const botaoMarca = document.getElementById('marca-salvar');
    const cancelar = document.getElementById('marca-cancelar');
    const titulo = document.getElementById('marca-form-titulo');
    let editando = 0; // id da marca em edição (0 = cadastrando uma nova)

    function sair() {
      editando = 0;
      campoNome.value = '';
      botaoMarca.textContent = 'Cadastrar marca';
      titulo.textContent = 'Nova marca';
      cancelar.hidden = true;
    }

    const carregar = async () => {
      marcasBox.setAttribute('aria-busy', 'true');
      try {
        const dados = await api('/admin/marcas');
        const marcas = Array.isArray(dados) ? dados : dados.marcas || [];
        if (marcas.length === 0) {
          mostrarEstado(marcasBox, { titulo: 'Nenhuma marca', texto: 'Cadastre a primeira marca no formulário acima.' });
          return;
        }
        marcasBox.replaceChildren(
          tabela(['Marca', 'Produtos', 'Ações'], marcas.map((m) => {
            const tr = el('tr');
            const ver = el('a', '', m.nome);
            ver.href = '/produtos?marca=' + encodeURIComponent(m.slug);

            const editar = el('button', 'btn btn--pequeno', 'Renomear');
            editar.type = 'button';
            editar.addEventListener('click', () => {
              editando = m.id;
              campoNome.value = m.nome;
              botaoMarca.textContent = 'Salvar nome';
              titulo.textContent = 'Renomear marca';
              cancelar.hidden = false;
              esconderAviso(aviso);
              campoNome.focus();
            });
            const excluir = el('button', 'btn btn--pequeno btn--perigo', 'Excluir');
            excluir.type = 'button';
            excluir.addEventListener('click', async () => {
              if (!window.confirm('Excluir a marca "' + m.nome + '"?')) return;
              ocupado(excluir, true, '...');
              try {
                await api('/admin/marcas/' + m.id, { metodo: 'DELETE' });
                mostrarAviso(aviso, 'ok', 'Marca excluída.');
                if (editando === m.id) sair();
                carregar();
              } catch (erro) {
                ocupado(excluir, false);
                if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
              }
            });
            const acoes = el('div', 'tabela__acoes');
            acoes.append(editar, excluir);
            tr.append(celula(ver), celula(String(m.total_produtos || 0)), celula(acoes));
            return tr;
          }))
        );
        marcasBox.setAttribute('aria-busy', 'false');
      } catch (erro) {
        if (erro.status !== 401) mostrarErroDeApi(marcasBox, erro, carregar);
      }
    };

    formMarca.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const nome = campoNome.value.trim();
      if (nome.length < 2) {
        mostrarAviso(aviso, 'erro', 'Informe o nome da marca (de 2 a 50 caracteres).');
        return;
      }
      esconderAviso(aviso);
      ocupado(botaoMarca, true, 'Salvando...');
      try {
        if (editando) {
          await api('/admin/marcas/' + editando, { metodo: 'PUT', corpo: { nome } });
          mostrarAviso(aviso, 'ok', 'Marca renomeada.');
        } else {
          await api('/admin/marcas', { metodo: 'POST', corpo: { nome } });
          mostrarAviso(aviso, 'ok', 'Marca cadastrada.');
        }
        sair();
        carregar();
      } catch (erro) {
        if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
      } finally {
        ocupado(botaoMarca, false);
        if (!editando) botaoMarca.textContent = 'Cadastrar marca';
      }
    });
    cancelar.addEventListener('click', sair);
    carregar();
  }
})();
