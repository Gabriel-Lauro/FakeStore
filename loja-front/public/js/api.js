// js/api.js — fetch central: sempre JSON, trata 401 e erro de rede.
//
//   api('/produtos?destaque=1')
//   api('/pedidos', { metodo: 'POST', corpo: {...} })
//   api('/admin/produtos/3/imagens', { metodo: 'POST', form: formData })   // upload
//   api('/auth/login', { metodo: 'POST', corpo: {...}, redirecionar401: false })
//
// No modo demo (meta "modo-demo" no <head>), as chamadas vão para o js/demo.js.

const API_BASE = '/api';
const EM_MODO_DEMO = !!document.querySelector('meta[name="modo-demo"]');

class ApiErro extends Error {
  constructor(status, mensagem, erros = []) {
    super(mensagem);
    this.status = status;
    this.erros = erros; // lista do 422: {"erros": [...]}
  }
}

async function api(caminho, { metodo = 'GET', corpo = null, form = null, redirecionar401 = true } = {}) {
  if (EM_MODO_DEMO && typeof Demo !== 'undefined') {
    return Demo.responder(caminho, metodo, form || corpo);
  }

  const opcoes = {
    method: metodo,
    credentials: 'same-origin',
    headers: { Accept: 'application/json' },
  };
  if (form !== null) {
    opcoes.body = form; // o navegador define o Content-Type com o boundary
  } else if (corpo !== null) {
    opcoes.headers['Content-Type'] = 'application/json';
    opcoes.body = JSON.stringify(corpo);
  }

  let resposta;
  try {
    resposta = await fetch(API_BASE + caminho, opcoes);
  } catch (_) {
    throw new ApiErro(0, 'Não foi possível falar com o servidor. Confira sua conexão.');
  }

  // Sem login: leva para a tela de entrada (menos no próprio login).
  if (resposta.status === 401 && redirecionar401) {
    window.location.href = '/login?voltar=' + encodeURIComponent(window.location.pathname);
    throw new ApiErro(401, 'Faça login para continuar.');
  }

  if (resposta.status === 204) {
    return null;
  }

  let dados = null;
  try {
    dados = await resposta.json();
  } catch (_) {
    throw new ApiErro(resposta.status, 'Resposta inesperada do servidor.');
  }

  if (!resposta.ok) {
    const erros = Array.isArray(dados.erros) ? dados.erros : [];
    throw new ApiErro(resposta.status, dados.erro || erros[0] || 'Algo deu errado.', erros);
  }
  return dados;
}
