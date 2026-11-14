// js/demo.js — API de mentira, só para ver e clicar nas telas sem o back-end.
// Só é carregado quando o modo demo está ligado (/?demo=1). Os dados ficam no sessionStorage.
// Respeita o mesmo contrato da API real (status e formato do JSON).

const Demo = (() => {
  const CHAVE = 'demo-db-v3';

  const CATEGORIAS = [
    { id: 1, nome: 'Camisetas', slug: 'camisetas' },
    { id: 2, nome: 'Calças', slug: 'calcas' },
    { id: 3, nome: 'Vestidos', slug: 'vestidos' },
    { id: 4, nome: 'Jaquetas', slug: 'jaquetas' },
  ];

  // Marcas do demo. Ficam no db (sessionStorage) para o cadastro em /admin/marcas funcionar.
  const MARCAS = [
    { id: 1, nome: 'Adidas', slug: 'adidas' },
    { id: 2, nome: 'Nike', slug: 'nike' },
    { id: 3, nome: 'New Balance', slug: 'new-balance' },
    { id: 4, nome: 'Puma', slug: 'puma' },
  ];
  // Produto -> marca (os produtos 9 e 10 ficam sem marca, para testar "sem marca").
  const MARCA_DO_PRODUTO = { 1: 2, 2: 1, 3: 4, 4: 3, 5: 1, 6: 2, 7: 4, 8: 3 };
  let marcasAtuais = MARCAS; // aponta para db.marcas durante cada chamada (veja responder)

  function prod(id, nome, categoria_id, preco, preco_original, destaque, descricao, vars) {
    return {
      id, nome, categoria_id, marca_id: MARCA_DO_PRODUTO[id] || null, preco, preco_original, destaque, ativo: 1, descricao,
      imagens: [],
      variacoes: vars.map((v, i) => ({ id: id * 10 + i, tamanho: v[0], estoque: v[1] })),
    };
  }

  function inicial() {
    return {
      proximo: { produto: 11, variacao: 500, imagem: 1, endereco: 2, pedido: 1003, marca: 5 },
      marcas: MARCAS.map((m) => ({ ...m })),
      produtos: [
        prod(1, 'Camiseta Básica Retrô', 1, 59.9, 79.9, 1, 'Algodão macio, corte reto e gola reforçada. A camiseta que combina com tudo.', [['P', 8], ['M', 17], ['G', 0]]),
        prod(2, 'Calça Flare Canelada', 2, 129.9, null, 1, 'Canelada, com cintura alta e cordão ajustável. Boca larga estilo anos 70.', [['P', 4], ['M', 6], ['G', 3]]),
        prod(3, 'Jaqueta Corta-Vento', 4, 219.9, 259.9, 1, 'Leve, com zíper até o queixo e bolsos laterais. Ótima para dias de vento.', [['M', 5], ['G', 2], ['GG', 0]]),
        prod(4, 'Vestido Midi Listrado', 3, 149.9, null, 1, 'Malha leve e listras largas, comprimento midi. Fácil de vestir e de combinar.', [['P', 3], ['M', 7], ['G', 2]]),
        prod(5, 'Camiseta Oversized Lima', 1, 69.9, null, 1, 'Modelagem ampla e ombro caído. Cor lima, bem viva.', [['M', 9], ['G', 6]]),
        prod(6, 'Calça Jogger Cinza', 2, 119.9, 139.9, 1, 'Moletom leve com punho na barra e bolsos fundos.', [['P', 5], ['M', 8], ['G', 4]]),
        prod(7, 'Jaqueta Jeans Clássica', 4, 249.9, null, 1, 'Jeans de lavagem média, botões metálicos e dois bolsos no peito.', [['M', 4], ['G', 3]]),
        prod(8, 'Vestido Curto Verde', 3, 109.9, 129.9, 1, 'Curto, com alças finas e tecido que não amassa.', [['P', 6], ['M', 2]]),
        prod(9, 'Camiseta Gráfica Raio', 1, 79.9, null, 0, 'Estampa de raio laranja no peito. Edição limitada.', [['P', 3], ['M', 3], ['G', 3]]),
        prod(10, 'Calça Cargo Verde', 2, 159.9, 189.9, 0, 'Seis bolsos, tecido resistente e ajuste na barra.', [['M', 5], ['G', 5]]),
      ],
      enderecos: [
        { id: 1, rotulo: 'Casa', cep: '01310100', logradouro: 'Avenida Paulista', numero: '1000', complemento: 'Apto 12', bairro: 'Bela Vista', cidade: 'São Paulo', uf: 'SP', principal: 1 },
      ],
      pedidos: [
        { id: 1001, data: '2026-10-02 14:30:00', status: 'entregue', total: 189.8, endereco_id: 1, itens: [{ nome: 'Camiseta Básica Retrô', tamanho: 'M', quantidade: 2, preco_unitario: 59.9 }, { nome: 'Camiseta Oversized Lima', tamanho: 'G', quantidade: 1, preco_unitario: 69.9 }] },
        { id: 1002, data: '2026-11-05 09:12:00', status: 'aguardando_pagamento', total: 219.9, endereco_id: 1, itens: [{ nome: 'Jaqueta Corta-Vento', tamanho: 'M', quantidade: 1, preco_unitario: 219.9 }] },
      ],
      conta: { nome: 'Cliente Demo', email: 'cliente@demo.com', telefone: '11999990000' },
    };
  }

  function lerDb() {
    try {
      const salvo = sessionStorage.getItem(CHAVE);
      if (salvo) return JSON.parse(salvo);
    } catch (_) { /* segue com o inicial */ }
    return inicial();
  }

  function salvarDb(db) {
    try { sessionStorage.setItem(CHAVE, JSON.stringify(db)); } catch (_) { /* ignora */ }
  }

  /* ---------- helpers ---------- */
  const nomeCategoria = (id) => (CATEGORIAS.find((c) => c.id === id) || {}).nome || '';
  const slugCategoria = (id) => (CATEGORIAS.find((c) => c.id === id) || {}).slug || '';
  const marcaPorId = (id) => marcasAtuais.find((m) => m.id === Number(id)) || null;
  const nomeMarca = (id) => (marcaPorId(id) || {}).nome || null;
  const slugMarca = (id) => (marcaPorId(id) || {}).slug || null;
  const arredondar = (n) => Math.round(n * 100) / 100;
  const normalizar = (t) => String(t || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  // marca_id vindo da tela: '' / null / undefined = sem marca.
  const lerMarcaId = (v) => (v === '' || v === null || v === undefined ? null : Number(v));
  // slug igual ao da API: minúsculo, sem acento, com hífens.
  const gerarSlug = (t) => normalizar(t).replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');

  function resumo(p) {
    return {
      id: p.id, nome: p.nome, categoria: nomeCategoria(p.categoria_id), marca: nomeMarca(p.marca_id), preco: p.preco,
      preco_original: p.preco_original, imagem: p.imagens[0] ? p.imagens[0].caminho : null,
    };
  }

  function completo(p) {
    return { ...p, categoria: nomeCategoria(p.categoria_id), marca_id: p.marca_id || null, marca: nomeMarca(p.marca_id), marca_slug: slugMarca(p.marca_id) };
  }

  function filtrar(db, q, soAtivos) {
    let l = db.produtos.filter((p) => !soAtivos || p.ativo);
    if (q.get('categoria')) l = l.filter((p) => slugCategoria(p.categoria_id) === q.get('categoria'));
    if (q.get('marca')) l = l.filter((p) => slugMarca(p.marca_id) === q.get('marca'));
    if (q.get('tamanho')) l = l.filter((p) => p.variacoes.some((v) => v.tamanho === q.get('tamanho')));
    if (q.get('preco_min')) l = l.filter((p) => p.preco >= Number(q.get('preco_min')));
    if (q.get('preco_max')) l = l.filter((p) => p.preco <= Number(q.get('preco_max')));
    if (q.get('busca')) l = l.filter((p) => normalizar(p.nome).includes(normalizar(q.get('busca'))));
    if (q.get('destaque') === '1') l = l.filter((p) => p.destaque);
    return l;
  }

  function achar(db, variacaoId) {
    for (const p of db.produtos) {
      const v = p.variacoes.find((x) => x.id === variacaoId);
      if (v) return { p, v };
    }
    return null;
  }

  function validarItens(db, itens) {
    const erros = [];
    const saida = [];
    if (!Array.isArray(itens) || itens.length === 0) throw new ApiErro(422, 'Carrinho vazio.', ['O carrinho está vazio.']);
    itens.forEach((it) => {
      const a = achar(db, it.variacao_id);
      const qtd = Number(it.quantidade);
      if (!a) { erros.push('Um item do carrinho não está mais à venda. Remova-o para continuar.'); return; }
      if (!(qtd >= 1 && qtd <= 20)) { erros.push('Quantidade inválida para ' + a.p.nome + '.'); return; }
      if (qtd > a.v.estoque) erros.push(a.p.nome + ' (' + a.v.tamanho + '): ' + (a.v.estoque === 0 ? 'sem estoque.' : 'só restam ' + a.v.estoque + '.'));
      saida.push({
        variacao_id: a.v.id, produto_id: a.p.id, nome: a.p.nome, tamanho: a.v.tamanho,
        quantidade: qtd, preco_unitario: a.p.preco, subtotal: arredondar(a.p.preco * qtd),
        imagem: a.p.imagens[0] ? a.p.imagens[0].caminho : null,
      });
    });
    if (erros.length) throw new ApiErro(422, erros[0], erros);
    return { itens: saida, total: arredondar(saida.reduce((s, i) => s + i.subtotal, 0)) };
  }

  function validarProduto(db, c) {
    const erros = [];
    if (!c.nome || String(c.nome).trim().length < 2) erros.push('Informe o nome do produto.');
    if (!CATEGORIAS.some((x) => x.id === Number(c.categoria_id))) erros.push('Escolha uma categoria.');
    if (lerMarcaId(c.marca_id) !== null && !marcaPorId(c.marca_id)) erros.push('Marca não encontrada.');
    if (!(Number(c.preco) > 0)) erros.push('O preço deve ser maior que zero.');
    if (c.preco_original !== null && c.preco_original !== '' && c.preco_original !== undefined && Number(c.preco_original) < Number(c.preco)) {
      erros.push('O preço original não pode ser menor que o preço.');
    }
    if (erros.length) throw new ApiErro(422, erros[0], erros);
  }

  function pedidoCompleto(db, ped) {
    const e = db.enderecos.find((x) => x.id === ped.endereco_id) || ped.endereco || {};
    return { id: ped.id, data: ped.data, status: ped.status, total: ped.total, itens: ped.itens, endereco: e };
  }

  const ERRO404 = () => new ApiErro(404, 'Não encontrado.');

  /* ---------- rotas: [método, regex, função(match, query, corpo, db)] ---------- */
  const rotas = [
    ['GET', /^\/categorias$/, () => CATEGORIAS],
    ['GET', /^\/marcas$/, (m, q, c, db) => db.marcas.map((x) => ({ ...x, total_produtos: db.produtos.filter((p) => p.ativo && p.marca_id === x.id).length })).sort((a, b) => a.nome.localeCompare(b.nome))],
    ['GET', /^\/produtos$/, (m, q, c, db) => { const l = filtrar(db, q, true); return { produtos: l.map(resumo), total: l.length, pagina: 1, total_paginas: 1 }; }],
    ['GET', /^\/produtos\/(\d+)$/, (m, q, c, db) => { const p = db.produtos.find((x) => x.id === +m[1] && x.ativo); if (!p) throw ERRO404(); return completo(p); }],
    ['POST', /^\/carrinho\/validar$/, (m, q, c, db) => validarItens(db, c && c.itens)],

    ['POST', /^\/auth\/login$/, (m, q, c) => { if (!c || !c.email || !c.senha) throw new ApiErro(401, 'E-mail ou senha inválidos.'); return { usuario: { nome: 'Cliente Demo', perfil: 'cliente' } }; }],
    ['POST', /^\/auth\/cadastro$/, (m, q, c) => {
      const erros = [];
      if (!c.nome) erros.push('Informe seu nome.');
      if (!c.email || !/^\S+@\S+\.\S+$/.test(c.email)) erros.push('Informe um e-mail válido.');
      if (!c.senha || c.senha.length < 8) erros.push('A senha precisa ter pelo menos 8 caracteres.');
      if (erros.length) throw new ApiErro(422, erros[0], erros);
      if (String(c.email).toLowerCase() === 'cliente@demo.com') throw new ApiErro(409, 'Este e-mail já está cadastrado.');
      return { id: 3 };
    }],
    ['GET', /^\/auth\/me$/, () => ({ nome: 'Cliente Demo', perfil: 'cliente' })],

    ['GET', /^\/conta$/, (m, q, c, db) => db.conta],
    ['PUT', /^\/conta$/, (m, q, c, db) => {
      if (!c.nome || String(c.nome).trim().length < 2) throw new ApiErro(422, 'Informe seu nome.', ['Informe seu nome.']);
      db.conta.nome = String(c.nome).trim();
      db.conta.telefone = soDigitos(c.telefone);
      return db.conta;
    }],
    ['PUT', /^\/conta\/senha$/, (m, q, c) => {
      if (!c.senha || c.senha.length < 8) throw new ApiErro(422, 'A senha precisa ter pelo menos 8 caracteres.', ['A senha precisa ter pelo menos 8 caracteres.']);
      return null;
    }],

    ['GET', /^\/enderecos$/, (m, q, c, db) => ({ enderecos: db.enderecos })],
    ['POST', /^\/enderecos$/, (m, q, c, db) => {
      const e = validarEndereco(c);
      e.id = db.proximo.endereco++;
      if (e.principal || db.enderecos.length === 0) { db.enderecos.forEach((x) => { x.principal = 0; }); e.principal = 1; }
      db.enderecos.push(e);
      return e;
    }],
    ['PUT', /^\/enderecos\/(\d+)$/, (m, q, c, db) => {
      const i = db.enderecos.findIndex((x) => x.id === +m[1]);
      if (i < 0) throw ERRO404();
      const e = { ...validarEndereco(c), id: +m[1] };
      if (e.principal) db.enderecos.forEach((x) => { x.principal = 0; });
      db.enderecos[i] = e;
      return e;
    }],
    ['DELETE', /^\/enderecos\/(\d+)$/, (m, q, c, db) => {
      const i = db.enderecos.findIndex((x) => x.id === +m[1]);
      if (i < 0) throw ERRO404();
      db.enderecos.splice(i, 1);
      return null;
    }],

    ['POST', /^\/pedidos$/, (m, q, c, db) => {
      const e = db.enderecos.find((x) => x.id === Number(c.endereco_id));
      if (!e) throw new ApiErro(422, 'Escolha um endereço de entrega.', ['Escolha um endereço de entrega.']);
      const v = validarItens(db, c.itens);
      v.itens.forEach((i) => { achar(db, i.variacao_id).v.estoque -= i.quantidade; });
      const ped = {
        id: db.proximo.pedido++, data: new Date().toISOString().slice(0, 19).replace('T', ' '), status: 'aguardando_pagamento',
        total: v.total, endereco_id: e.id, endereco: e,
        itens: v.itens.map((i) => ({ nome: i.nome, tamanho: i.tamanho, quantidade: i.quantidade, preco_unitario: i.preco_unitario })),
      };
      db.pedidos.unshift(ped);
      return { id: ped.id, total: ped.total };
    }],
    ['GET', /^\/pedidos$/, (m, q, c, db) => ({ pedidos: db.pedidos.map((p) => ({ id: p.id, data: p.data, status: p.status, total: p.total })) })],
    ['GET', /^\/pedidos\/(\d+)$/, (m, q, c, db) => { const p = db.pedidos.find((x) => x.id === +m[1]); if (!p) throw ERRO404(); return pedidoCompleto(db, p); }],
    ['PUT', /^\/pedidos\/(\d+)\/cancelar$/, (m, q, c, db) => {
      const p = db.pedidos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      if (!['aguardando_pagamento', 'pago'].includes(p.status)) throw new ApiErro(422, 'Este pedido não pode mais ser cancelado.', ['Este pedido não pode mais ser cancelado.']);
      p.status = 'cancelado';
      p.itens.forEach((i) => { const a = db.produtos.flatMap((x) => x.variacoes).find((v) => v.tamanho === i.tamanho); if (a) a.estoque += i.quantidade; });
      return pedidoCompleto(db, p);
    }],

    /* ----- admin ----- */
    ['GET', /^\/admin\/produtos$/, (m, q, c, db) => {
      const PAGINA = 10;
      let l = db.produtos.slice().sort((a, b) => b.id - a.id);
      if (q.get('busca')) l = l.filter((p) => normalizar(p.nome).includes(normalizar(q.get('busca'))));
      const porPagina = Math.max(1, Math.min(50, Number(q.get('por_pagina')) || PAGINA));
      const totalPaginas = Math.max(1, Math.ceil(l.length / porPagina));
      const pagina = Math.max(1, Math.min(totalPaginas, Number(q.get('pagina')) || 1));
      const fatia = l.slice((pagina - 1) * porPagina, pagina * porPagina);
      return {
        produtos: fatia.map((p) => ({
          id: p.id, nome: p.nome, categoria: nomeCategoria(p.categoria_id), marca: nomeMarca(p.marca_id), preco: p.preco, ativo: p.ativo ? 1 : 0,
          imagem: p.imagens[0] ? p.imagens[0].caminho : null,
          estoque_total: p.variacoes.reduce((s, v) => s + v.estoque, 0),
        })),
        total: l.length, pagina, por_pagina: porPagina, total_paginas: totalPaginas,
        total_geral: db.produtos.length, total_ativos: db.produtos.filter((p) => p.ativo).length,
      };
    }],
    ['GET', /^\/admin\/produtos\/(\d+)$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      return completo(p);
    }],
    ['POST', /^\/admin\/produtos$/, (m, q, c, db) => {
      validarProduto(db, c);
      const p = {
        id: db.proximo.produto++, nome: String(c.nome).trim(), categoria_id: Number(c.categoria_id), marca_id: lerMarcaId(c.marca_id), descricao: c.descricao || '',
        preco: Number(c.preco), preco_original: c.preco_original === '' || c.preco_original == null ? null : Number(c.preco_original),
        destaque: c.destaque ? 1 : 0, ativo: c.ativo ? 1 : 0, imagens: [], variacoes: [],
      };
      db.produtos.push(p);
      return completo(p);
    }],
    ['PUT', /^\/admin\/produtos\/(\d+)$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      validarProduto(db, c);
      Object.assign(p, {
        nome: String(c.nome).trim(), categoria_id: Number(c.categoria_id), marca_id: lerMarcaId(c.marca_id), descricao: c.descricao || '', preco: Number(c.preco),
        preco_original: c.preco_original === '' || c.preco_original == null ? null : Number(c.preco_original),
        destaque: c.destaque ? 1 : 0, ativo: c.ativo ? 1 : 0,
      });
      return completo(p);
    }],
    ['PATCH', /^\/admin\/produtos\/(\d+)$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      const campos = ['nome', 'categoria_id', 'marca_id', 'descricao', 'preco', 'preco_original', 'destaque', 'ativo'];
      const novo = {};
      campos.forEach((k) => { if (c && Object.prototype.hasOwnProperty.call(c, k)) novo[k] = c[k]; });
      if (Object.keys(novo).length === 0) throw new ApiErro(422, 'Envie ao menos um campo para alterar.', ['Envie ao menos um campo para alterar.']);
      const junto = { ...p, ...novo };
      validarProduto(db, junto);
      Object.assign(p, {
        nome: String(junto.nome).trim(), categoria_id: Number(junto.categoria_id), marca_id: lerMarcaId(junto.marca_id), descricao: junto.descricao || '', preco: Number(junto.preco),
        preco_original: junto.preco_original === '' || junto.preco_original == null ? null : Number(junto.preco_original),
        destaque: junto.destaque ? 1 : 0, ativo: junto.ativo ? 1 : 0,
      });
      return completo(p);
    }],
    ['DELETE', /^\/admin\/produtos\/(\d+)$/, (m, q, c, db) => {
      const i = db.produtos.findIndex((x) => x.id === +m[1]);
      if (i < 0) throw ERRO404();
      db.produtos.splice(i, 1);
      return null;
    }],
    ['POST', /^\/admin\/produtos\/(\d+)\/imagens$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      const img = { id: db.proximo.imagem++, caminho: 'uploads/demo-' + Date.now() + '.jpg', ordem: p.imagens.length + 1 };
      p.imagens.push(img);
      return img;
    }],
    ['DELETE', /^\/admin\/imagens\/(\d+)$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.imagens.some((i) => i.id === +m[1]));
      if (!p) throw ERRO404();
      p.imagens = p.imagens.filter((i) => i.id !== +m[1]);
      return null;
    }],
    ['POST', /^\/admin\/produtos\/(\d+)\/variacoes$/, (m, q, c, db) => {
      const p = db.produtos.find((x) => x.id === +m[1]);
      if (!p) throw ERRO404();
      const erros = [];
      if (!c.tamanho) erros.push('Informe o tamanho.');
      if (!(Number.isInteger(Number(c.estoque)) && Number(c.estoque) >= 0)) erros.push('O estoque deve ser um número inteiro, sem negativo.');
      if (erros.length) throw new ApiErro(422, erros[0], erros);
      if (p.variacoes.some((v) => v.tamanho === c.tamanho)) throw new ApiErro(409, 'Esse tamanho já existe neste produto.');
      const v = { id: db.proximo.variacao++, tamanho: c.tamanho, estoque: Number(c.estoque) };
      p.variacoes.push(v);
      return v;
    }],
    ['PUT', /^\/admin\/variacoes\/(\d+)$/, (m, q, c, db) => {
      const a = achar(db, +m[1]);
      if (!a) throw ERRO404();
      if (!(Number.isInteger(Number(c.estoque)) && Number(c.estoque) >= 0)) throw new ApiErro(422, 'O estoque deve ser um número inteiro, sem negativo.', ['O estoque deve ser um número inteiro, sem negativo.']);
      a.v.estoque = Number(c.estoque);
      return a.v;
    }],
    ['DELETE', /^\/admin\/variacoes\/(\d+)$/, (m, q, c, db) => {
      const a = achar(db, +m[1]);
      if (!a) throw ERRO404();
      a.p.variacoes = a.p.variacoes.filter((v) => v.id !== +m[1]);
      return null;
    }],
    ['GET', /^\/admin\/pedidos$/, (m, q, c, db) => ({ pedidos: db.pedidos.map((p) => ({ id: p.id, data: p.data, status: p.status, total: p.total })) })],

    ['GET', /^\/admin\/marcas$/, (m, q, c, db) => db.marcas.map((x) => ({ ...x, total_produtos: db.produtos.filter((p) => p.marca_id === x.id).length })).sort((a, b) => a.nome.localeCompare(b.nome))],
    ['POST', /^\/admin\/marcas$/, (m, q, c, db) => {
      const nome = String((c && c.nome) || '').trim();
      const slug = gerarSlug(nome);
      if (nome.length < 2 || nome.length > 50 || !slug) throw new ApiErro(422, 'Informe o nome da marca (de 2 a 50 caracteres).', ['Informe o nome da marca (de 2 a 50 caracteres).']);
      if (db.marcas.some((x) => x.slug === slug || normalizar(x.nome) === normalizar(nome))) throw new ApiErro(409, 'Já existe uma marca com esse nome.');
      const marca = { id: db.proximo.marca++, nome, slug };
      db.marcas.push(marca);
      return marca;
    }],
    ['PUT', /^\/admin\/marcas\/(\d+)$/, (m, q, c, db) => {
      const marca = db.marcas.find((x) => x.id === +m[1]);
      if (!marca) throw ERRO404();
      const nome = String((c && c.nome) || '').trim();
      const slug = gerarSlug(nome);
      if (nome.length < 2 || nome.length > 50 || !slug) throw new ApiErro(422, 'Informe o nome da marca (de 2 a 50 caracteres).', ['Informe o nome da marca (de 2 a 50 caracteres).']);
      if (db.marcas.some((x) => x.id !== marca.id && (x.slug === slug || normalizar(x.nome) === normalizar(nome)))) throw new ApiErro(409, 'Já existe uma marca com esse nome.');
      marca.nome = nome;
      marca.slug = slug;
      return { ...marca };
    }],
    ['DELETE', /^\/admin\/marcas\/(\d+)$/, (m, q, c, db) => {
      const i = db.marcas.findIndex((x) => x.id === +m[1]);
      if (i < 0) throw ERRO404();
      if (db.produtos.some((p) => p.marca_id === db.marcas[i].id)) throw new ApiErro(409, 'Esta marca ainda tem produtos. Troque a marca deles antes de excluir.');
      db.marcas.splice(i, 1);
      return null;
    }],
  ];

  function validarEndereco(c) {
    const erros = [];
    const cep = soDigitos(c.cep);
    if (cep.length !== 8) erros.push('O CEP deve ter 8 números.');
    if (!c.logradouro) erros.push('Informe a rua.');
    if (!c.numero) erros.push('Informe o número.');
    if (!c.bairro) erros.push('Informe o bairro.');
    if (!c.cidade) erros.push('Informe a cidade.');
    if (!/^[A-Za-z]{2}$/.test(c.uf || '')) erros.push('A UF deve ter 2 letras.');
    if (erros.length) throw new ApiErro(422, erros[0], erros);
    return {
      rotulo: c.rotulo || 'Endereço', cep, logradouro: c.logradouro, numero: c.numero, complemento: c.complemento || '',
      bairro: c.bairro, cidade: c.cidade, uf: String(c.uf).toUpperCase(), principal: c.principal ? 1 : 0,
    };
  }

  async function responder(caminho, metodo, corpo) {
    await new Promise((r) => setTimeout(r, 250));
    const url = new URL(caminho, 'http://demo.local');
    const db = lerDb();
    marcasAtuais = db.marcas || MARCAS;
    for (const [met, regex, fn] of rotas) {
      if (met !== metodo && !(met === 'PUT' && metodo === 'PATCH' && /^\/admin\/variacoes\//.test(url.pathname))) continue;
      const m = url.pathname.match(regex);
      if (!m) continue;
      const resultado = fn(m, url.searchParams, corpo, db);
      salvarDb(db);
      return resultado === undefined ? null : resultado;
    }
    throw new ApiErro(404, 'Rota não encontrada no modo demo.');
  }

  return { responder };
})();
