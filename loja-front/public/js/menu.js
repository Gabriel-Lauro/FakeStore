// js/menu.js — abre e fecha o menu em tela cheia.

(() => {
  const menu = document.getElementById('menu-tela');
  const abrir = document.getElementById('menu-abrir');
  const fechar = document.getElementById('menu-fechar');
  if (!menu || !abrir || !fechar) return;

  function abrirMenu() {
    menu.hidden = false;
    document.body.classList.add('menu-aberto');
    abrir.setAttribute('aria-expanded', 'true');
    fechar.focus();
  }

  function fecharMenu() {
    menu.hidden = true;
    document.body.classList.remove('menu-aberto');
    abrir.setAttribute('aria-expanded', 'false');
    abrir.focus();
  }

  abrir.addEventListener('click', abrirMenu);
  fechar.addEventListener('click', fecharMenu);
  document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape' && !menu.hidden) fecharMenu();
  });
  // Clicar num link do menu que aponta para a mesma página (#destaques) também fecha.
  menu.querySelectorAll('nav a').forEach((a) => a.addEventListener('click', () => {
    menu.hidden = true;
    document.body.classList.remove('menu-aberto');
  }));
})();
