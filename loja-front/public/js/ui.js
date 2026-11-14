// js/ui.js — funções de tela usadas por várias páginas.
// Regra do trabalho: texto vindo da API entra na tela só com textContent, nunca com innerHTML.

const STATUS_PEDIDO = {
  aguardando_pagamento: { texto: 'Aguardando pagamento', classe: 'selo-status--espera' },
  pago: { texto: 'Pago', classe: 'selo-status--ok' },
  enviado: { texto: 'Enviado', classe: 'selo-status--info' },
  entregue: { texto: 'Entregue', classe: 'selo-status--ok' },
  cancelado: { texto: 'Cancelado', classe: 'selo-status--erro' },
};

/* ---------- básicos ---------- */
function el(tag, classe, texto) {
  const no = document.createElement(tag);
  if (classe) no.className = classe;
  if (texto !== undefined && texto !== null) no.textContent = texto;
  return no;
}

function preco(valor) {
  return Number(valor).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function formatarData(iso) {
  const d = new Date(String(iso).replace(' ', 'T'));
  return Number.isNaN(d.getTime()) ? String(iso) : d.toLocaleDateString('pt-BR');
}

function soDigitos(texto) {
  return String(texto || '').replace(/\D+/g, '');
}

function seloStatus(status) {
  const info = STATUS_PEDIDO[status] || { texto: String(status), classe: '' };
  return el('span', 'selo-status ' + info.classe, info.texto);
}

function textoEndereco(e) {
  const linha1 = [e.logradouro, e.numero].filter(Boolean).join(', ') + (e.complemento ? ' - ' + e.complemento : '');
  const linha2 = [e.bairro, [e.cidade, e.uf].filter(Boolean).join('/')].filter(Boolean).join(' · ');
  return [linha1, linha2, e.cep ? 'CEP ' + String(e.cep).replace(/^(\d{5})(\d{3})$/, '$1-$2') : ''].filter(Boolean);
}

/* ---------- imagens ---------- */
function urlImagem(caminho) {
  return '/api/' + String(caminho).replace(/^\/+/, '');
}

// Sem foto (ou foto com erro): bloco rosa com o texto "sem imagem".
function aplicarPlaceholder(caixa) {
  caixa.classList.add('card__img--vazia');
  caixa.querySelectorAll('img').forEach((img) => img.remove());
  caixa.appendChild(el('span', 'slot-img__nome', 'sem imagem'));
}
// Põe a foto na caixa; sem foto (ou se falhar), mostra o bloco rosa.
function colocarImagem(caixa, caminho, alt, id) {
  if (!caminho) {
    aplicarPlaceholder(caixa);
    return;
  }
  const img = document.createElement('img');
  img.src = urlImagem(caminho);
  img.alt = alt || '';
  img.loading = 'lazy';
  img.addEventListener('error', () => aplicarPlaceholder(caixa));
  caixa.appendChild(img);
}

/* ---------- card de produto ---------- */
function criarCard(p) {
  const link = el('a', 'card');
  link.href = '/produtos/' + encodeURIComponent(p.id);

  const caixa = el('div', 'card__img');
  const emOferta = p.preco_original && Number(p.preco_original) > Number(p.preco);
  colocarImagem(caixa, p.imagem, p.nome, p.id);

  if (emOferta) {
    const desconto = Math.round((1 - Number(p.preco) / Number(p.preco_original)) * 100);
    caixa.appendChild(el('span', 'card__selo', '-' + desconto + '%'));
  }

  const corpo = el('div', 'card__corpo');
  corpo.appendChild(el('h3', 'card__nome', p.nome));
  const rotuloCard = [p.marca, p.categoria].filter(Boolean).join(' · ');
  if (rotuloCard) corpo.appendChild(el('p', 'card__categoria', rotuloCard));

  const precos = el('div', 'card__precos');
  precos.appendChild(el('span', 'preco', preco(p.preco)));
  if (emOferta) precos.appendChild(el('s', 'preco-antigo', preco(p.preco_original)));
  corpo.appendChild(precos);

  link.append(caixa, corpo);
  return link;
}

/* ---------- estados: carregando, vazio, erro ---------- */
function mostrarCarregandoGrade(container, quantidade = 4) {
  container.replaceChildren();
  container.setAttribute('aria-busy', 'true');
  for (let i = 0; i < quantidade; i++) {
    const item = el('div');
    item.append(el('div', 'esqueleto'), el('div', 'esqueleto-linha'), el('div', 'esqueleto-linha esqueleto-linha--curta'));
    container.appendChild(item);
  }
}

// opcoes: { titulo, texto, erro: bool, acao: { texto, href } | { texto, aoClicar } }
function mostrarEstado(container, { titulo, texto, erro = false, acao = null }) {
  container.replaceChildren();
  container.setAttribute('aria-busy', 'false');
  const caixa = el('div', 'estado' + (erro ? ' estado--erro' : ''));
  if (erro) caixa.setAttribute('role', 'alert');
  caixa.append(el('h3', 'estado__titulo', titulo));
  if (texto) caixa.appendChild(el('p', 'estado__texto', texto));
  if (acao) {
    if (acao.href) {
      const a = el('a', 'btn', acao.texto);
      a.href = acao.href;
      caixa.appendChild(a);
    } else {
      const b = el('button', 'btn btn--escuro', acao.texto);
      b.type = 'button';
      b.addEventListener('click', acao.aoClicar);
      caixa.appendChild(b);
    }
  }
  container.appendChild(caixa);
}

function mostrarErroDeApi(container, erro, tentarDeNovo) {
  mostrarEstado(container, {
    titulo: 'Não deu para carregar',
    texto: erro.message || 'Tente novamente em instantes.',
    erro: true,
    acao: tentarDeNovo ? { texto: 'Tentar de novo', aoClicar: tentarDeNovo } : null,
  });
}

/* ---------- avisos de formulário ---------- */
// tipo: 'erro' | 'ok' | 'info'. mensagens: string ou lista de strings.
function mostrarAviso(container, tipo, mensagens) {
  const lista = Array.isArray(mensagens) ? mensagens : [mensagens];
  container.replaceChildren();
  container.hidden = false;
  container.className = 'aviso aviso--' + tipo;
  container.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');
  if (lista.length === 1) {
    container.textContent = lista[0];
  } else {
    const ul = el('ul');
    lista.forEach((m) => ul.appendChild(el('li', '', m)));
    container.appendChild(ul);
  }
  container.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}

function esconderAviso(container) {
  container.hidden = true;
  container.replaceChildren();
}

function mensagensDoErro(erro) {
  return erro.erros && erro.erros.length ? erro.erros : [erro.message || 'Algo deu errado.'];
}

/* ---------- botão de carregando ---------- */
function ocupado(botao, estaOcupado, textoOcupado = 'Aguarde...') {
  if (estaOcupado) {
    botao.dataset.textoOriginal = botao.textContent;
    botao.textContent = textoOcupado;
    botao.disabled = true;
  } else {
    botao.textContent = botao.dataset.textoOriginal || botao.textContent;
    botao.disabled = false;
  }
}

// Só aceita voltar para caminhos do próprio site (evita redirecionar para outro domínio).
function destinoSeguro(destino, padrao = '/') {
  return typeof destino === 'string' && destino.startsWith('/') && !destino.startsWith('//') ? destino : padrao;
}
