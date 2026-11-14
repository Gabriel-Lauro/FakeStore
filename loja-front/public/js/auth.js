// js/auth.js — formulários de login e cadastro.
// Login: POST /api/auth/login (a API grava a sessão). Erro sempre genérico, para não revelar se o e-mail existe.
// Cadastro: POST /api/auth/cadastro, depois leva ao login.

(() => {
  const aviso = document.getElementById('aviso');
  const params = new URLSearchParams(window.location.search);

  // No modo demo a API é de mentira e não grava a sessão do PHP: entra pelo atalho /?demo=1.
  function irPara(destino) {
    if (EM_MODO_DEMO) {
      destino += (destino.includes('?') ? '&' : '?') + 'demo=1';
    }
    window.location.href = destino;
  }

  const formLogin = document.getElementById('form-login');
  if (formLogin) {
    formLogin.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const botao = formLogin.querySelector('button[type="submit"]');
      const email = formLogin.elements.email.value.trim();
      const senha = formLogin.elements.senha.value;
      if (!email || !senha) {
        mostrarAviso(aviso, 'erro', 'Informe e-mail e senha.');
        return;
      }
      esconderAviso(aviso);
      ocupado(botao, true, 'Entrando...');
      try {
        const dados = await api('/auth/login', { metodo: 'POST', corpo: { email, senha }, redirecionar401: false });
        const voltar = params.get('voltar');
        const padrao = dados && dados.usuario && dados.usuario.perfil === 'admin' ? '/admin' : '/';
        irPara(destinoSeguro(voltar, padrao));
      } catch (erro) {
        ocupado(botao, false);
        mostrarAviso(aviso, 'erro', erro.status === 401 || erro.status === 422 ? 'E-mail ou senha inválidos.' : erro.message);
      }
    });
  }

  const formCadastro = document.getElementById('form-cadastro');
  if (formCadastro) {
    formCadastro.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const botao = formCadastro.querySelector('button[type="submit"]');
      const f = formCadastro.elements;
      const erros = [];
      if (f.nome.value.trim().length < 2) erros.push('Informe seu nome.');
      if (!/^\S+@\S+\.\S+$/.test(f.email.value.trim())) erros.push('Informe um e-mail válido.');
      if (f.senha.value.length < 8) erros.push('A senha precisa ter pelo menos 8 caracteres.');
      if (f.senha.value !== f.senha2.value) erros.push('As senhas não são iguais.');
      if (erros.length) {
        mostrarAviso(aviso, 'erro', erros);
        return;
      }
      esconderAviso(aviso);
      ocupado(botao, true, 'Criando...');
      try {
        await api('/auth/cadastro', {
          metodo: 'POST',
          corpo: { nome: f.nome.value.trim(), email: f.email.value.trim(), senha: f.senha.value },
          redirecionar401: false,
        });
        window.location.href = '/login?cadastro=ok';
      } catch (erro) {
        ocupado(botao, false);
        mostrarAviso(aviso, 'erro', mensagensDoErro(erro));
      }
    });
  }

  if (formLogin && params.get('cadastro') === 'ok') {
    mostrarAviso(aviso, 'ok', 'Conta criada! Agora é só entrar.');
  }
})();
