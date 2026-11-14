<?php
// src/validacao.php — confere os dados e devolve [lista de erros, dados limpos]. Não fala com o banco.

const QUANTIDADE_MAXIMA = 20;

/* ---------- helpers ---------- */
function textoLimpo($valor): string
{
    return is_string($valor) ? trim($valor) : '';
}

function soDigitos($valor): string
{
    return preg_replace('/\D+/', '', is_scalar($valor) ? (string) $valor : '');
}

// Inteiro vindo do JSON ou da URL ("5" ou 5). Qualquer outra coisa vira null.
function inteiro($valor): ?int
{
    if (is_int($valor)) {
        return $valor;
    }
    if (is_string($valor) && preg_match('/^-?\d{1,9}$/', $valor)) {
        return (int) $valor;
    }
    return null;
}

// Valor em reais (número ou texto numérico) -> "59.90", ou null se não for número.
function valorMonetario($valor): ?string
{
    if (is_bool($valor) || !is_numeric($valor)) {
        return null;
    }
    $n = (float) $valor;
    if ($n < 0 || $n > 99999999.99) {
        return null;
    }
    return number_format($n, 2, '.', '');
}

function booleano($valor, bool $padrao): int
{
    if ($valor === null) {
        return $padrao ? 1 : 0;
    }
    return filter_var($valor, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
}

function gerarSlug(string $nome): string
{
    $slug = mb_strtolower($nome, 'UTF-8');
    $slug = strtr($slug, [
        'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
        'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i', 'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
        'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ç' => 'c', 'ñ' => 'n',
    ]);
    return trim(preg_replace('/[^a-z0-9]+/', '-', $slug), '-');
}

/* ---------- usuário ---------- */
// $cadastro = true: nome, e-mail, telefone e senha. false: só nome e telefone (editar conta).
// Nunca lê "perfil": quem se cadastra é sempre cliente.
function validarUsuario(array $d, bool $cadastro = true): array
{
    $erros = [];
    $nome = textoLimpo($d['nome'] ?? null);
    if (mb_strlen($nome, 'UTF-8') < 2 || mb_strlen($nome, 'UTF-8') > 100) {
        $erros[] = 'Informe seu nome (de 2 a 100 letras).';
    }

    $telefone = soDigitos($d['telefone'] ?? '');
    if ($telefone !== '' && !in_array(strlen($telefone), [10, 11], true)) {
        $erros[] = 'O telefone deve ter 10 ou 11 números, com DDD.';
    }
    $limpo = ['nome' => $nome, 'telefone' => $telefone];

    if ($cadastro) {
        $email = strtolower(textoLimpo($d['email'] ?? null));
        if (strlen($email) > 150 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um e-mail válido.';
        }
        $limpo['email'] = $email;
        $erros = array_merge($erros, validarSenha($d['senha'] ?? null));
        $limpo['senha'] = is_string($d['senha'] ?? null) ? $d['senha'] : '';
    }
    return [$erros, $limpo];
}

function validarSenha($senha): array
{
    if (!is_string($senha) || mb_strlen($senha, 'UTF-8') < 8) {
        return ['A senha precisa ter pelo menos 8 caracteres.'];
    }
    // Sem pepper, o bcrypt só considera os 72 primeiros bytes.
    if (PEPPER === '' && strlen($senha) > 72) {
        return ['A senha pode ter no máximo 72 caracteres.'];
    }
    return [];
}

/* ---------- produto (usada no POST e no PUT) ---------- */
function validarProduto(array $d): array
{
    $erros = [];

    $nome = textoLimpo($d['nome'] ?? null);
    if (mb_strlen($nome, 'UTF-8') < 2 || mb_strlen($nome, 'UTF-8') > 150) {
        $erros[] = 'Informe o nome do produto (de 2 a 150 caracteres).';
    }

    $categoria = inteiro($d['categoria_id'] ?? null);
    if ($categoria === null || $categoria < 1) {
        $erros[] = 'Escolha uma categoria.';
    }

    $descricao = textoLimpo($d['descricao'] ?? '');
    if (mb_strlen($descricao, 'UTF-8') > 2000) {
        $erros[] = 'A descrição pode ter no máximo 2000 caracteres.';
    }

    // Marca é opcional: vazio/null = produto sem marca.
    $marca = null;
    $marcaBruta = $d['marca_id'] ?? null;
    if ($marcaBruta !== null && $marcaBruta !== '') {
        $marca = inteiro($marcaBruta);
        if ($marca === null || $marca < 1) {
            $erros[] = 'Marca inválida.';
            $marca = null;
        }
    }

    $preco = valorMonetario($d['preco'] ?? null);
    if ($preco === null || centavos($preco) <= 0) {
        $erros[] = 'O preço deve ser maior que zero.';
        $preco = null;
    }

    $original = null;
    $bruto = $d['preco_original'] ?? null;
    if ($bruto !== null && $bruto !== '') {
        $original = valorMonetario($bruto);
        if ($original === null) {
            $erros[] = 'O preço original é inválido.';
        } elseif ($preco !== null && centavos($original) < centavos($preco)) {
            $erros[] = 'O preço original não pode ser menor que o preço.';
        }
    }

    return [$erros, [
        'nome'           => $nome,
        'categoria_id'   => $categoria,
        'marca_id'       => $marca,
        'descricao'      => $descricao,
        'preco'          => $preco,
        'preco_original' => $original,
        'destaque'       => booleano($d['destaque'] ?? null, false),
        'ativo'          => booleano($d['ativo'] ?? null, true),
    ]];
}

/* ---------- variação ---------- */
function validarVariacao(array $d): array
{
    $erros = [];
    $tamanho = strtoupper(textoLimpo($d['tamanho'] ?? null));
    if (!preg_match('/^[A-Z0-9]{1,10}$/', $tamanho)) {
        $erros[] = 'Informe o tamanho (por exemplo P, M, G ou 42).';
    }
    $estoque = inteiro($d['estoque'] ?? null);
    if ($estoque === null || $estoque < 0 || $estoque > 100000) {
        $erros[] = 'O estoque deve ser um número inteiro, sem negativo.';
    }
    return [$erros, ['tamanho' => $tamanho, 'estoque' => $estoque]];
}

function validarEstoque(array $d): array
{
    $estoque = inteiro($d['estoque'] ?? null);
    if ($estoque === null || $estoque < 0 || $estoque > 100000) {
        return [['O estoque deve ser um número inteiro, sem negativo.'], null];
    }
    return [[], $estoque];
}

/* ---------- endereço ---------- */
function validarEndereco(array $d): array
{
    $erros = [];
    $rotulo = textoLimpo($d['rotulo'] ?? '');
    if ($rotulo === '') {
        $rotulo = 'Endereço';
    }
    if (mb_strlen($rotulo, 'UTF-8') > 30) {
        $erros[] = 'O nome do endereço pode ter no máximo 30 caracteres.';
    }

    $cep = soDigitos($d['cep'] ?? '');
    if (strlen($cep) !== 8) {
        $erros[] = 'O CEP deve ter 8 números.';
    }

    $campos = [
        'logradouro' => ['a rua', 150, true],
        'numero'     => ['o número', 20, true],
        'complemento' => ['o complemento', 80, false],
        'bairro'     => ['o bairro', 80, true],
        'cidade'     => ['a cidade', 80, true],
    ];
    $limpo = ['rotulo' => $rotulo, 'cep' => $cep];
    foreach ($campos as $campo => [$rotuloCampo, $max, $obrigatorio]) {
        $valor = textoLimpo($d[$campo] ?? '');
        if ($obrigatorio && $valor === '') {
            $erros[] = 'Informe ' . $rotuloCampo . '.';
        } elseif (mb_strlen($valor, 'UTF-8') > $max) {
            $erros[] = 'Texto longo demais em ' . $rotuloCampo . ' (máximo ' . $max . ').';
        }
        $limpo[$campo] = $valor;
    }

    $uf = strtoupper(textoLimpo($d['uf'] ?? ''));
    if (!preg_match('/^[A-Z]{2}$/', $uf)) {
        $erros[] = 'A UF deve ter 2 letras.';
    }
    $limpo['uf'] = $uf;
    $limpo['principal'] = booleano($d['principal'] ?? null, false);
    return [$erros, $limpo];
}

/* ---------- carrinho / pedido ---------- */
// Recebe a lista [{variacao_id, quantidade}] e devolve a lista limpa, com repetidos somados.
function validarItens($itens): array
{
    if (!is_array($itens) || count($itens) === 0) {
        return [['O carrinho está vazio.'], []];
    }
    if (count($itens) > 50) {
        return [['O carrinho tem itens demais.'], []];
    }
    $erros = [];
    $somados = [];
    foreach ($itens as $item) {
        $id = is_array($item) ? inteiro($item['variacao_id'] ?? null) : null;
        $quantidade = is_array($item) ? inteiro($item['quantidade'] ?? null) : null;
        if ($id === null || $id < 1) {
            $erros[] = 'Há um item inválido no carrinho.';
            continue;
        }
        if ($quantidade === null || $quantidade < 1 || $quantidade > QUANTIDADE_MAXIMA) {
            $erros[] = 'A quantidade de cada item deve ser de 1 a ' . QUANTIDADE_MAXIMA . '.';
            continue;
        }
        $somados[$id] = ($somados[$id] ?? 0) + $quantidade;
        if ($somados[$id] > QUANTIDADE_MAXIMA) {
            $erros[] = 'A quantidade de cada item deve ser de 1 a ' . QUANTIDADE_MAXIMA . '.';
        }
    }
    $lista = [];
    foreach ($somados as $id => $quantidade) {
        $lista[] = ['variacao_id' => $id, 'quantidade' => $quantidade];
    }
    return [array_values(array_unique($erros)), $lista];
}

/* ---------- categoria ---------- */
function validarCategoria(array $d): array
{
    $nome = textoLimpo($d['nome'] ?? null);
    $slug = gerarSlug($nome);
    if (mb_strlen($nome, 'UTF-8') < 2 || mb_strlen($nome, 'UTF-8') > 50 || $slug === '') {
        return [['Informe o nome da categoria (de 2 a 50 caracteres).'], null];
    }
    return [[], ['nome' => $nome, 'slug' => $slug]];
}

/* ---------- marca ---------- */
function validarMarca(array $d): array
{
    $nome = textoLimpo($d['nome'] ?? null);
    $slug = gerarSlug($nome);
    if (mb_strlen($nome, 'UTF-8') < 2 || mb_strlen($nome, 'UTF-8') > 50 || $slug === '') {
        return [['Informe o nome da marca (de 2 a 50 caracteres).'], null];
    }
    return [[], ['nome' => $nome, 'slug' => $slug]];
}

/* ---------- pedido ---------- */
const STATUS_PEDIDO = ['aguardando_pagamento', 'pago', 'enviado', 'entregue', 'cancelado'];

function validarStatus(array $d): array
{
    $status = textoLimpo($d['status'] ?? null);
    if (!in_array($status, STATUS_PEDIDO, true)) {
        return [['Status inválido. Use: ' . implode(', ', STATUS_PEDIDO) . '.'], null];
    }
    return [[], $status];
}
