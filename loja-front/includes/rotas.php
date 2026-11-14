<?php
// includes/rotas.php — mapa URL -> página e quem pode abrir.
//
// A página é o nome da pasta em paginas/ (o arquivo carregado é paginas/<nome>/pagina.php).
// acesso: 'publico' (qualquer pessoa) | 'logado' (cliente ou admin) | 'admin'
// O padrão da URL é uma expressão regular; os grupos () viram $parametros.
// Aqui a proteção é decidida num lugar só, então nenhuma página nova fica aberta por esquecimento.
//
// Para criar uma página nova: crie paginas/<nome>/pagina.php (copie uma existente),
// opcionalmente estilo.css e img/ ao lado, e acrescente uma linha abaixo.

return [
    // Públicas
    ['/',                           'home',                  'publico'],
    ['/produtos',                   'produtos',              'publico'],
    ['/produtos/(\d+)',             'produto',               'publico'],
    ['/carrinho',                   'carrinho',              'publico'],
    ['/login',                      'login',                 'publico'],
    ['/cadastro',                   'cadastro',              'publico'],
    ['/403',                        'erro-403',              'publico'],
    ['/404',                        'erro-404',              'publico'],

    // Cliente logado
    ['/checkout',                   'checkout',              'logado'],
    ['/pedido-realizado',           'pedido-realizado',      'logado'],
    ['/meus-pedidos',               'meus-pedidos',          'logado'],
    ['/meus-pedidos/(\d+)',         'pedido',                'logado'],
    ['/conta',                      'conta',                 'logado'],
    ['/conta/enderecos',            'enderecos',             'logado'],
    ['/conta/enderecos/novo',       'endereco-form',         'logado'],
    ['/conta/enderecos/(\d+)',      'endereco-form',         'logado'],

    // Administrador
    ['/admin',                      'admin/painel',          'admin'],
    ['/admin/produtos',             'admin/produtos',        'admin'],
    ['/admin/produtos/novo',        'admin/produto-form',    'admin'],
    ['/admin/produtos/(\d+)',       'admin/produto-form',    'admin'],
    ['/admin/marcas',               'admin/marcas',          'admin'],
];
