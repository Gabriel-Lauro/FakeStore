// js/conta.js — dados da conta e troca de senha.
// GET /api/conta, PUT /api/conta, PUT /api/conta/senha.

(() => {
  const formDados = document.getElementById('form-dados');
  const formSenha = document.getElementById('form-senha');
  const aviso = document.getElementById('aviso');
  if (!formDados || !formSenha) return;

  async function carregar() {
    try {
      const c = await api('/conta');
      formDados.elements.nome.value = c.nome || '';
      formDados.elements.email.value = c.email || '';
      formDados.elements.telefone.value = c.telefone || '';
    } catch (erro) {
      if (erro.status !== 401) mostrarAviso(aviso, 'erro', erro.message);
    }
  }

  formDados.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const botao = formDados.querySelector('button[type="submit"]');
    const nome = formDados.elements.nome.value.trim();
    const telefone = soDigitos(formDados.elements.telefone.value);
    if (nome.length < 2) {
      mostrarAviso(aviso, 'erro', 'Informe seu nome.');
      return;
    }
    if (telefone && (telefone.length < 10 || telefone.length > 11)) {
      mostrarAviso(aviso, 'erro', 'O telefone deve ter 10 ou 11 números, com DDD.');
      return;
    }
    esconderAviso(aviso);
    ocupado(botao, true, 'Salvando...');
    try {
      await api('/conta', { metodo: 'PUT', corpo: { nome, telefone } });
      mostrarAviso(aviso, 'ok', 'Dados salvos.');
    } catch (erro) {
      if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
    } finally {
      ocupado(botao, false);
    }
  });

  formSenha.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const botao = formSenha.querySelector('button[type="submit"]');
    const { senha, senha2 } = formSenha.elements;
    if (senha.value.length < 8) {
      mostrarAviso(aviso, 'erro', 'A nova senha precisa ter pelo menos 8 caracteres.');
      return;
    }
    if (senha.value !== senha2.value) {
      mostrarAviso(aviso, 'erro', 'As senhas não são iguais.');
      return;
    }
    esconderAviso(aviso);
    ocupado(botao, true, 'Trocando...');
    try {
      await api('/conta/senha', { metodo: 'PUT', corpo: { senha: senha.value } });
      formSenha.reset();
      mostrarAviso(aviso, 'ok', 'Senha trocada.');
    } catch (erro) {
      if (erro.status !== 401) mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
    } finally {
      ocupado(botao, false);
    }
  });

  carregar();
})();
