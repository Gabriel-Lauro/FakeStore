// js/home.js — carrega os produtos em destaque (GET /api/produtos?destaque=1).
// Estados tratados: carregando, lista vazia e erro (com "tentar de novo").
// Usa as funções de js/ui.js. Para ver sem a API, abra /?demo=1.

(() => {
  const grade = document.getElementById('destaques-grade');
  if (!grade) return;

  const QUANTIDADE_MAXIMA = 8;

  async function carregar() {
    mostrarCarregandoGrade(grade, 4);
    try {
      const dados = await api('/produtos?destaque=1');
      const lista = (Array.isArray(dados) ? dados : dados.produtos || []).slice(0, QUANTIDADE_MAXIMA);

      if (lista.length === 0) {
        mostrarEstado(grade, {
          titulo: 'Nada em destaque por enquanto',
          texto: 'Estamos montando a vitrine. Veja todos os produtos enquanto isso.',
          acao: { texto: 'Ver todos os produtos', href: '/produtos' },
        });
        return;
      }
      grade.replaceChildren(...lista.map(criarCard));
      grade.setAttribute('aria-busy', 'false');
    } catch (erro) {
      if (erro.status === 401) return; // api.js já levou para o /login
      mostrarErroDeApi(grade, erro, carregar);
    }
  }

  carregar();
})();
