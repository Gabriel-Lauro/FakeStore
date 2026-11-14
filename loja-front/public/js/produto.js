// js/produto.js — detalhe do produto (GET /api/produtos/{id}) e "adicionar ao carrinho".
// Os tamanhos viram botões; a variação escolhida define o estoque e o id que vai para o carrinho.

(() => {
  const raiz = document.getElementById('produto-raiz');
  const conteudo = document.getElementById('produto-conteudo');
  if (!raiz || !conteudo) return;

  const id = parseInt(raiz.dataset.id, 10);
  const migalhaNome = document.getElementById('migalha-nome');

  async function carregar() {
    conteudo.style.display = 'block';
    try {
      const p = await api('/produtos/' + id);
      desenhar(p);
    } catch (erro) {
      if (erro.status === 401) return;
      migalhaNome.textContent = '';
      if (erro.status === 404) {
        mostrarEstado(conteudo, {
          titulo: 'Produto não encontrado',
          texto: 'Ele pode ter saído da loja. Veja o que temos por aqui.',
          acao: { texto: 'Ver produtos', href: '/produtos' },
        });
        return;
      }
      mostrarErroDeApi(conteudo, erro, carregar);
    }
  }

  function unicos(variacoes, campo) {
    return [...new Set(variacoes.map((v) => v[campo]))];
  }

  function desenhar(p) {
    document.title = p.nome + ' | FAKE STORE';
    migalhaNome.textContent = p.nome;
    conteudo.setAttribute('aria-busy', 'false');

    const variacoes = Array.isArray(p.variacoes) ? p.variacoes : [];
    const imagens = Array.isArray(p.imagens) ? p.imagens : [];
    const emOferta = p.preco_original && Number(p.preco_original) > Number(p.preco);
    const estado = { tamanho: null, quantidade: 1 };

    /* ----- galeria ----- */
    const galeria = el('div', 'galeria');
    const principal = el('div', 'galeria__principal');
    const miniaturas = el('div', 'galeria__miniaturas');

    function mostrarImagem(indice) {
      principal.replaceChildren();
      principal.classList.remove('card__img--vazia');
      colocarImagem(principal, imagens[indice] ? imagens[indice].caminho : null, p.nome, p.id);
      miniaturas.querySelectorAll('.miniatura').forEach((m, i) => m.setAttribute('aria-pressed', String(i === indice)));
    }

    if (imagens.length > 1) {
      imagens.forEach((img, i) => {
        const b = el('button', 'miniatura');
        b.type = 'button';
        b.setAttribute('aria-label', 'Ver foto ' + (i + 1));
        const mini = document.createElement('img');
        mini.src = urlImagem(img.caminho);
        mini.alt = '';
        b.appendChild(mini);
        b.addEventListener('click', () => mostrarImagem(i));
        miniaturas.appendChild(b);
      });
    }
    galeria.append(principal, miniaturas);

    /* ----- informações ----- */
    const info = el('div', 'produto__info');
    const rotuloProduto = [p.marca, p.categoria].filter(Boolean).join(' · ');
    if (rotuloProduto) info.appendChild(el('p', 'produto__categoria', rotuloProduto));
    info.appendChild(el('h1', 'titulo produto__nome', p.nome));

    const precos = el('div', 'produto__precos');
    precos.appendChild(el('span', 'preco', preco(p.preco)));
    if (emOferta) {
      precos.appendChild(el('s', 'preco-antigo', preco(p.preco_original)));
      precos.appendChild(el('span', 'etiqueta', '-' + Math.round((1 - Number(p.preco) / Number(p.preco_original)) * 100) + '%'));
    }
    info.appendChild(precos);
    if (p.descricao) info.appendChild(el('p', 'produto__descricao', p.descricao));

    /* ----- opções ----- */
    const blocoTamanho = el('div');
    const estoqueTexto = el('p', 'produto__estoque');
    estoqueTexto.setAttribute('aria-live', 'polite');
    const aviso = el('div');
    aviso.hidden = true;

    const escolhida = () => (estado.tamanho ? variacoes.find((v) => v.tamanho === estado.tamanho) || null : null);

    function temEstoque(valor) {
      return variacoes.some((v) => v.tamanho === valor && v.estoque > 0);
    }

    function desenharOpcoes(bloco, rotulo, campo) {
      bloco.replaceChildren(el('p', 'opcoes__rotulo', rotulo));
      const lista = el('div', 'opcoes__lista');
      lista.setAttribute('role', 'group');
      lista.setAttribute('aria-label', rotulo);
      unicos(variacoes, campo).forEach((valor) => {
        const b = el('button', 'chip', valor);
        b.type = 'button';
        b.setAttribute('aria-pressed', String(estado[campo] === valor));
        b.disabled = !temEstoque(valor);
        b.addEventListener('click', () => {
          estado[campo] = estado[campo] === valor ? null : valor;
          estado.quantidade = 1;
          atualizar();
        });
        lista.appendChild(b);
      });
      bloco.appendChild(lista);
    }

    /* ----- quantidade e botão ----- */
    const saida = el('output', '', '1');
    const menos = el('button', '', '−');
    const mais = el('button', '', '+');
    menos.type = mais.type = 'button';
    menos.setAttribute('aria-label', 'Diminuir quantidade');
    mais.setAttribute('aria-label', 'Aumentar quantidade');
    const qtd = el('div', 'qtd');
    qtd.append(menos, saida, mais);
    const comprar = el('button', 'btn btn--grande', 'Adicionar ao carrinho');
    comprar.type = 'button';

    const limite = () => {
      const v = escolhida();
      return v ? Math.min(Carrinho.MAX_POR_ITEM, v.estoque) : 1;
    };

    function atualizar() {
      desenharOpcoes(blocoTamanho, 'Tamanho', 'tamanho');
      const v = escolhida();
      estado.quantidade = Math.min(estado.quantidade, limite());
      saida.textContent = String(estado.quantidade);
      menos.disabled = estado.quantidade <= 1;
      mais.disabled = !v || estado.quantidade >= limite();
      comprar.disabled = !v || v.estoque === 0;

      if (!v) {
        estoqueTexto.textContent = variacoes.length ? 'Escolha o tamanho.' : 'Sem variações disponíveis no momento.';
      } else if (v.estoque === 0) {
        estoqueTexto.textContent = 'Sem estoque nesse tamanho.';
      } else if (v.estoque <= 3) {
        estoqueTexto.textContent = 'Últimas ' + v.estoque + ' unidades!';
      } else {
        estoqueTexto.textContent = 'Em estoque.';
      }
    }

    menos.addEventListener('click', () => { estado.quantidade -= 1; atualizar(); });
    mais.addEventListener('click', () => { estado.quantidade += 1; atualizar(); });

    comprar.addEventListener('click', () => {
      const v = escolhida();
      if (!v || v.estoque === 0) return;
      Carrinho.adicionar(v.id, estado.quantidade, {
        nome: p.nome, tamanho: v.tamanho, preco: Number(p.preco), imagem: imagens[0] ? imagens[0].caminho : null,
      });
      mostrarAviso(aviso, 'ok', 'Adicionado ao carrinho!');
      const ir = el('a', '', ' Ver carrinho');
      ir.href = '/carrinho';
      ir.style.fontWeight = '700';
      aviso.appendChild(ir);
    });

    const compra = el('div', 'produto__compra');
    compra.append(qtd, comprar);
    info.append(blocoTamanho, estoqueTexto, compra, aviso);

    const produto = el('div', 'produto');
    produto.append(galeria, info);
    conteudo.replaceChildren(produto);
    mostrarImagem(0);
    atualizar();
  }

  carregar();
})();
