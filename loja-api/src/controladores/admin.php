<?php
// src/controladores/admin.php — rotas /api/admin/*. O index.php já exigiu perfil admin antes de chegar aqui.

const TIPOS_DE_IMAGEM = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

/* ---------- produtos ---------- */
function adminListarProdutos(array $p): void
{
    $filtros = [
        'busca'      => mb_substr(textoLimpo($_GET['busca'] ?? ''), 0, 60, 'UTF-8'),
        'pagina'     => max(1, inteiro($_GET['pagina'] ?? null) ?? 1),
        'por_pagina' => inteiro($_GET['por_pagina'] ?? null) ?? POR_PAGINA_ADMIN,
    ];
    responder(200, listarProdutosAdmin($filtros));
}

// Um produto com todas as imagens e variações (inclusive inativo), para a tela de edição.
function adminDetalharProduto(array $p): void
{
    $produto = produtoCompleto((int) $p[0], false);
    if ($produto === null) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    responder(200, $produto);
}

// Valida com a função única validarProduto() e confere se a categoria e a marca existem.
function dadosDeProdutoValidados(): array
{
    [$erros, $d] = validarProduto(lerJson());
    return conferirReferenciasDoProduto($erros, $d);
}

// Categoria é obrigatória; marca é opcional (null = produto sem marca). Lança 422 se algo não existe.
function conferirReferenciasDoProduto(array $erros, array $d): array
{
    if ($d['categoria_id'] !== null && buscarCategoria($d['categoria_id']) === null) {
        $erros[] = 'Categoria não encontrada.';
    }
    if ($d['marca_id'] !== null && buscarMarca($d['marca_id']) === null) {
        $erros[] = 'Marca não encontrada.';
    }
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    return $d;
}

function adminCriarProduto(array $p): void
{
    $id = criarProduto(dadosDeProdutoValidados());
    responder(201, produtoCompleto($id, false));
}

function adminEditarProduto(array $p): void
{
    $id = (int) $p[0];
    if (!produtoExiste($id)) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    atualizarProduto($id, dadosDeProdutoValidados());
    responder(200, produtoCompleto($id, false));
}

// PATCH: o corpo traz só os campos que mudaram. Junta com o que já está salvo e valida o produto inteiro.
function adminAtualizarProduto(array $p): void
{
    $id = (int) $p[0];
    $atual = produtoCompleto($id, false);
    if ($atual === null) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    $corpo = lerJson();
    $campos = ['nome', 'categoria_id', 'marca_id', 'descricao', 'preco', 'preco_original', 'destaque', 'ativo'];
    $alterados = array_intersect_key($corpo, array_flip($campos));
    if ($alterados === []) {
        throw new ErroApi(422, 'Envie ao menos um campo para alterar.');
    }
    $mesclado = array_merge(array_intersect_key($atual, array_flip($campos)), $alterados);
    [$erros, $d] = validarProduto($mesclado);
    atualizarProduto($id, conferirReferenciasDoProduto($erros, $d));
    responder(200, produtoCompleto($id, false));
}

function adminExcluirProduto(array $p): void
{
    $id = (int) $p[0];
    if (!produtoExiste($id)) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    $arquivos = caminhosDasImagens($id);
    excluirProduto($id);
    foreach ($arquivos as $caminho) {
        apagarArquivoDeUpload($caminho);
    }
    responder(204);
}

/* ---------- imagens ---------- */
function apagarArquivoDeUpload(string $caminho): void
{
    $arquivo = PASTA_UPLOADS . '/' . basename($caminho); // basename: nunca sai da pasta de uploads
    if (is_file($arquivo)) {
        @unlink($arquivo);
    }
}

function adminEnviarImagem(array $p): void
{
    $produtoId = (int) $p[0];
    if (!produtoExiste($produtoId)) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }

    $arquivo = $_FILES['imagem'] ?? $_FILES['foto'] ?? null;
    if ($arquivo === null) {
        // Passou do post_max_size do php.ini: o PHP descarta o corpo inteiro e o $_FILES chega vazio.
        if ($_FILES === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            throw new ErroApi(413, 'A imagem é grande demais (máximo 4 MB).');
        }
        throw new ErroApi(422, 'Envie uma imagem no campo "imagem".');
    }

    $erroUpload = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($erroUpload === UPLOAD_ERR_INI_SIZE || $erroUpload === UPLOAD_ERR_FORM_SIZE) {
        throw new ErroApi(413, 'A imagem é grande demais (máximo 4 MB).');
    }
    if ($erroUpload === UPLOAD_ERR_NO_FILE) {
        throw new ErroApi(422, 'Escolha uma imagem.');
    }
    if ($erroUpload !== UPLOAD_ERR_OK || !is_uploaded_file($arquivo['tmp_name'])) {
        throw new ErroApi(422, 'Não foi possível receber a imagem. Tente de novo.');
    }
    if ($arquivo['size'] > UPLOAD_MAX_BYTES) {
        throw new ErroApi(413, 'A imagem é grande demais (máximo 4 MB).');
    }

    // Confere o conteúdo do arquivo; extensão e "type" enviados pelo navegador não valem nada.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($arquivo['tmp_name']);
    if (!isset(TIPOS_DE_IMAGEM[$mime]) || @getimagesize($arquivo['tmp_name']) === false) {
        throw new ErroApi(422, 'Use uma imagem JPG, PNG ou WEBP.');
    }

    $nome = bin2hex(random_bytes(16)) . '.' . TIPOS_DE_IMAGEM[$mime]; // nome aleatório, nunca o original
    if (!move_uploaded_file($arquivo['tmp_name'], PASTA_UPLOADS . '/' . $nome)) {
        throw new RuntimeException('Não foi possível gravar o arquivo em ' . PASTA_UPLOADS);
    }
    responder(201, criarImagem($produtoId, 'uploads/' . $nome));
}

function adminRemoverImagem(array $p): void
{
    $imagem = buscarImagem((int) $p[0]);
    if ($imagem === null) {
        throw new ErroApi(404, 'Imagem não encontrada.');
    }
    removerImagem((int) $imagem['id']);
    apagarArquivoDeUpload($imagem['caminho']);
    responder(204);
}

/* ---------- variações ---------- */
function adminCriarVariacao(array $p): void
{
    $produtoId = (int) $p[0];
    if (!produtoExiste($produtoId)) {
        throw new ErroApi(404, 'Produto não encontrado.');
    }
    [$erros, $d] = validarVariacao(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (variacaoRepetida($produtoId, $d['tamanho'])) {
        throw new ErroApi(409, 'Esse tamanho já existe neste produto.');
    }
    try {
        $variacao = criarVariacao($produtoId, $d['tamanho'], $d['estoque']);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? 0) === 1062) {
            throw new ErroApi(409, 'Esse tamanho já existe neste produto.');
        }
        throw $e;
    }
    responder(201, $variacao);
}

function adminAjustarEstoque(array $p): void
{
    $variacao = buscarVariacao((int) $p[0]);
    if ($variacao === null) {
        throw new ErroApi(404, 'Variação não encontrada.');
    }
    [$erros, $estoque] = validarEstoque(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    atualizarEstoque((int) $variacao['id'], $estoque);
    responder(200, variacaoParaJson(buscarVariacao((int) $variacao['id'])));
}

function adminRemoverVariacao(array $p): void
{
    if (buscarVariacao((int) $p[0]) === null) {
        throw new ErroApi(404, 'Variação não encontrada.');
    }
    removerVariacao((int) $p[0]);
    responder(204);
}

/* ---------- categorias ---------- */
function adminCriarCategoria(array $p): void
{
    [$erros, $d] = validarCategoria(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (categoriaRepetida($d['nome'], $d['slug'])) {
        throw new ErroApi(409, 'Já existe uma categoria com esse nome.');
    }
    responder(201, buscarCategoria(criarCategoria($d['nome'], $d['slug'])));
}

function adminRenomearCategoria(array $p): void
{
    $id = (int) $p[0];
    if (buscarCategoria($id) === null) {
        throw new ErroApi(404, 'Categoria não encontrada.');
    }
    [$erros, $d] = validarCategoria(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (categoriaRepetida($d['nome'], $d['slug'], $id)) {
        throw new ErroApi(409, 'Já existe uma categoria com esse nome.');
    }
    renomearCategoria($id, $d['nome'], $d['slug']);
    responder(200, buscarCategoria($id));
}

function adminExcluirCategoria(array $p): void
{
    $id = (int) $p[0];
    if (buscarCategoria($id) === null) {
        throw new ErroApi(404, 'Categoria não encontrada.');
    }
    if (contarProdutosDaCategoria($id) > 0) {
        throw new ErroApi(409, 'Esta categoria ainda tem produtos. Mude ou exclua os produtos antes.');
    }
    excluirCategoria($id);
    responder(204);
}

/* ---------- marcas ---------- */
// No admin a contagem inclui produtos inativos (é ela que trava a exclusão).
function adminListarMarcas(array $p): void
{
    responder(200, listarMarcas(false));
}

function adminCriarMarca(array $p): void
{
    [$erros, $d] = validarMarca(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (marcaRepetida($d['nome'], $d['slug'])) {
        throw new ErroApi(409, 'Já existe uma marca com esse nome.');
    }
    responder(201, buscarMarca(criarMarca($d['nome'], $d['slug'])));
}

function adminRenomearMarca(array $p): void
{
    $id = (int) $p[0];
    if (buscarMarca($id) === null) {
        throw new ErroApi(404, 'Marca não encontrada.');
    }
    [$erros, $d] = validarMarca(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    if (marcaRepetida($d['nome'], $d['slug'], $id)) {
        throw new ErroApi(409, 'Já existe uma marca com esse nome.');
    }
    renomearMarca($id, $d['nome'], $d['slug']);
    responder(200, buscarMarca($id));
}

function adminExcluirMarca(array $p): void
{
    $id = (int) $p[0];
    if (buscarMarca($id) === null) {
        throw new ErroApi(404, 'Marca não encontrada.');
    }
    if (contarProdutosDaMarca($id) > 0) {
        throw new ErroApi(409, 'Esta marca ainda tem produtos. Troque a marca deles antes de excluir.');
    }
    excluirMarca($id);
    responder(204);
}

/* ---------- pedidos ---------- */
function adminListarPedidos(array $p): void
{
    responder(200, ['pedidos' => listarTodosPedidos()]);
}

function adminMudarStatus(array $p): void
{
    $id = (int) $p[0];
    if (buscarPedido($id, null) === null) {
        throw new ErroApi(404, 'Pedido não encontrado.');
    }
    [$erros, $status] = validarStatus(lerJson());
    if ($erros) {
        throw new ErroApi(422, $erros[0], $erros);
    }
    alterarStatusPedido($id, $status);
    responder(200, buscarPedido($id, null));
}
