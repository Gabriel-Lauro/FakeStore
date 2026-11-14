// js/carrinho.js — carrinho guardado no navegador (localStorage).
//
// Cada item: { variacao_id, quantidade, nome, tamanho, preco, imagem }
// Nome, preço e imagem são só um resumo para mostrar a tela na hora. Preço e estoque
// valem os que a API devolve em POST /api/carrinho/validar; o navegador nunca decide o total.

const Carrinho = (() => {
  const CHAVE = 'carrinho';
  const MAX_POR_ITEM = 20;

  function ler() {
    try {
      const itens = JSON.parse(localStorage.getItem(CHAVE) || '[]');
      return Array.isArray(itens) ? itens : [];
    } catch (_) {
      return [];
    }
  }

  function salvar(itens) {
    try {
      localStorage.setItem(CHAVE, JSON.stringify(itens));
    } catch (_) {
      /* armazenamento bloqueado: o carrinho só não persiste */
    }
    atualizarContador();
  }

  function adicionar(variacaoId, quantidade, resumo = {}) {
    const itens = ler();
    const existente = itens.find((i) => i.variacao_id === variacaoId);
    if (existente) {
      existente.quantidade = Math.min(MAX_POR_ITEM, existente.quantidade + quantidade);
      Object.assign(existente, resumo);
    } else {
      itens.push({ variacao_id: variacaoId, quantidade: Math.min(MAX_POR_ITEM, quantidade), ...resumo });
    }
    salvar(itens);
  }

  function definirQuantidade(variacaoId, quantidade) {
    const q = Math.max(1, Math.min(MAX_POR_ITEM, Math.floor(quantidade) || 1));
    const itens = ler();
    const item = itens.find((i) => i.variacao_id === variacaoId);
    if (item) {
      item.quantidade = q;
      salvar(itens);
    }
  }

  function remover(variacaoId) {
    salvar(ler().filter((i) => i.variacao_id !== variacaoId));
  }

  function limpar() {
    salvar([]);
  }

  // O que vai para a API: só id e quantidade.
  function paraApi() {
    return ler().map((i) => ({ variacao_id: i.variacao_id, quantidade: i.quantidade }));
  }

  function totalDeItens() {
    return ler().reduce((soma, i) => soma + i.quantidade, 0);
  }

  function atualizarContador() {
    const el = document.getElementById('carrinho-contagem');
    if (el) el.textContent = String(totalDeItens());
  }

  document.addEventListener('DOMContentLoaded', atualizarContador);

  return { ler, adicionar, definirQuantidade, remover, limpar, paraApi, totalDeItens, atualizarContador, MAX_POR_ITEM };
})();
