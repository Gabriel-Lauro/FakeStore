<?php
// src/rotas.php — o documento de rotas em forma de código.
// Cada linha: [método, padrão do caminho, função, quem pode chamar].
// O index.php aplica o acesso ANTES de executar a função, então nenhuma rota fica aberta por esquecimento.

const ACESSO_PUBLICO = 'publico'; // qualquer pessoa
const ACESSO_LOGIN   = 'login';   // precisa estar logado (401 se não estiver)
const ACESSO_ADMIN   = 'admin';   // logado e com perfil admin (401 / 403)

function rotasDaApi(): array
{
    return [
        // autenticação
        ['POST', '#^/auth/cadastro$#', 'cadastrar',    ACESSO_PUBLICO],
        ['POST', '#^/auth/login$#',    'entrar',       ACESSO_PUBLICO],
        ['POST', '#^/auth/logout$#',   'sair',         ACESSO_PUBLICO],
        ['GET',  '#^/auth/me$#',       'quemSouEu',    ACESSO_LOGIN],

        // catálogo (público)
        ['GET',  '#^/categorias$#',            'listarCategoriasPublico', ACESSO_PUBLICO],
        ['GET',  '#^/marcas$#',                'listarMarcasPublico',     ACESSO_PUBLICO],
        ['GET',  '#^/produtos$#',              'listarProdutosPublico',   ACESSO_PUBLICO],
        ['GET',  '#^/produtos/(\d+)$#',        'detalharProduto',         ACESSO_PUBLICO],
        ['POST', '#^/carrinho/validar$#',      'validarCarrinho',         ACESSO_PUBLICO],

        // cliente logado
        ['GET',    '#^/conta$#',                    'verConta',            ACESSO_LOGIN],
        ['PUT',    '#^/conta$#',                    'editarConta',         ACESSO_LOGIN],
        ['PUT',    '#^/conta/senha$#',              'trocarSenha',         ACESSO_LOGIN],
        ['GET',    '#^/enderecos$#',                'listarMeusEnderecos', ACESSO_LOGIN],
        ['POST',   '#^/enderecos$#',                'criarMeuEndereco',    ACESSO_LOGIN],
        ['PUT',    '#^/enderecos/(\d+)$#',          'editarMeuEndereco',   ACESSO_LOGIN],
        ['DELETE', '#^/enderecos/(\d+)$#',          'removerMeuEndereco',  ACESSO_LOGIN],
        ['POST',   '#^/pedidos$#',                  'criarMeuPedido',      ACESSO_LOGIN],
        ['GET',    '#^/pedidos$#',                  'listarMeusPedidos',   ACESSO_LOGIN],
        ['GET',    '#^/pedidos/(\d+)$#',            'detalharMeuPedido',   ACESSO_LOGIN],
        ['PUT',    '#^/pedidos/(\d+)/cancelar$#',   'cancelarMeuPedido',   ACESSO_LOGIN],

        // administrador
        ['GET',    '#^/admin/produtos$#',                   'adminListarProdutos',    ACESSO_ADMIN],
        ['GET',    '#^/admin/produtos/(\d+)$#',             'adminDetalharProduto',   ACESSO_ADMIN],
        ['POST',   '#^/admin/produtos$#',                   'adminCriarProduto',      ACESSO_ADMIN],
        ['PUT',    '#^/admin/produtos/(\d+)$#',             'adminEditarProduto',     ACESSO_ADMIN],
        ['PATCH',  '#^/admin/produtos/(\d+)$#',             'adminAtualizarProduto',  ACESSO_ADMIN],
        ['DELETE', '#^/admin/produtos/(\d+)$#',             'adminExcluirProduto',    ACESSO_ADMIN],
        ['POST',   '#^/admin/produtos/(\d+)/imagens$#',     'adminEnviarImagem',      ACESSO_ADMIN],
        ['DELETE', '#^/admin/imagens/(\d+)$#',              'adminRemoverImagem',     ACESSO_ADMIN],
        ['POST',   '#^/admin/produtos/(\d+)/variacoes$#',   'adminCriarVariacao',     ACESSO_ADMIN],
        ['PUT',    '#^/admin/variacoes/(\d+)$#',            'adminAjustarEstoque',    ACESSO_ADMIN],
        ['PATCH',  '#^/admin/variacoes/(\d+)$#',            'adminAjustarEstoque',    ACESSO_ADMIN],
        ['DELETE', '#^/admin/variacoes/(\d+)$#',            'adminRemoverVariacao',   ACESSO_ADMIN],
        ['POST',   '#^/admin/categorias$#',                 'adminCriarCategoria',    ACESSO_ADMIN],
        ['PUT',    '#^/admin/categorias/(\d+)$#',           'adminRenomearCategoria', ACESSO_ADMIN],
        ['DELETE', '#^/admin/categorias/(\d+)$#',           'adminExcluirCategoria',  ACESSO_ADMIN],
        ['GET',    '#^/admin/marcas$#',                     'adminListarMarcas',      ACESSO_ADMIN],
        ['POST',   '#^/admin/marcas$#',                     'adminCriarMarca',        ACESSO_ADMIN],
        ['PUT',    '#^/admin/marcas/(\d+)$#',               'adminRenomearMarca',     ACESSO_ADMIN],
        ['DELETE', '#^/admin/marcas/(\d+)$#',               'adminExcluirMarca',      ACESSO_ADMIN],
        ['GET',    '#^/admin/pedidos$#',                    'adminListarPedidos',     ACESSO_ADMIN],
        ['PUT',    '#^/admin/pedidos/(\d+)/status$#',       'adminMudarStatus',       ACESSO_ADMIN],
    ];
}
