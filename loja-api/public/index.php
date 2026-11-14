<?php
// public/index.php — porta de entrada única da API (roteador).
// O .htaccess manda para cá toda requisição que não é um arquivo real (como as fotos em /uploads).

// A API só devolve JSON: nenhum aviso do PHP pode ir para a resposta. Erros vão para o log do Apache.
ini_set('display_errors', '0');
ini_set('html_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);
ob_start();

const PASTA_UPLOADS = __DIR__ . '/uploads';
$raiz = dirname(__DIR__);

require_once $raiz . '/config/conexao.php';
require_once $raiz . '/src/resposta.php';
require_once $raiz . '/src/auth.php';
require_once $raiz . '/src/validacao.php';
foreach (['usuarios', 'enderecos', 'categorias', 'marcas', 'imagens', 'variacoes', 'produtos', 'pedidos'] as $modelo) {
    require_once $raiz . '/src/modelos/' . $modelo . '.php';
}
foreach (['auth', 'catalogo', 'conta', 'enderecos', 'pedidos', 'admin'] as $controlador) {
    require_once $raiz . '/src/controladores/' . $controlador . '.php';
}
require_once $raiz . '/src/rotas.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

try {
    iniciarSessao();

    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $caminho = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $caminho = preg_replace('#^/api(?=/|$)#', '', $caminho); // o Alias do Apache põe a API sob /api
    $caminho = '/' . trim($caminho, '/');

    $metodosDoCaminho = [];
    foreach (rotasDaApi() as [$metodoDaRota, $padrao, $funcao, $acesso]) {
        if (!preg_match($padrao, $caminho, $achou)) {
            continue;
        }
        if ($metodoDaRota !== $metodo) {
            $metodosDoCaminho[] = $metodoDaRota;
            continue;
        }

        // Quem pode chamar é decidido aqui, antes da função da rota rodar.
        if ($acesso === ACESSO_LOGIN) {
            exigirLoginApi();
        } elseif ($acesso === ACESSO_ADMIN) {
            exigirPerfilApi('admin');
        }

        array_shift($achou);
        $funcao($achou); // cada função termina chamando responder()
        throw new RuntimeException('A rota ' . $funcao . ' não enviou resposta.');
    }

    if ($metodosDoCaminho) {
        header('Allow: ' . implode(', ', array_unique($metodosDoCaminho)));
        throw new ErroApi(405, 'Método não permitido nesta rota.');
    }
    throw new ErroApi(404, 'Rota não encontrada.');
} catch (ErroApi $e) {
    responderErro($e);
} catch (Throwable $e) {
    error_log('[loja-api] ' . get_class($e) . ': ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    responder(500, ['erro' => 'Erro interno do servidor.']);
}
