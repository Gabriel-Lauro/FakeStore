<?php
// src/resposta.php — como a API responde: sempre JSON, com o status HTTP certo.

// Erro "esperado" (validação, não achou, sem permissão...). Pode ser lançado de qualquer camada;
// o public/index.php captura e transforma em resposta.
class ErroApi extends Exception
{
    public array $erros;

    public function __construct(int $status, string $mensagem, array $erros = [])
    {
        parent::__construct($mensagem, $status);
        $this->erros = $erros;
    }
}

function responder(int $status, $dados = null): void
{
    // Descarta qualquer saída solta (aviso, echo esquecido) para o JSON sair limpo.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    if ($status !== 204 && $dados !== null) {
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION);
    }
    exit;
}

// Converte um ErroApi no JSON do contrato: {"erro": "..."} ou, no 422, {"erros": [...]}.
function responderErro(ErroApi $e): void
{
    $status = $e->getCode();
    if ($status === 422) {
        responder(422, ['erros' => $e->erros ?: [$e->getMessage()]]);
    }
    responder($status, ['erro' => $e->getMessage()]);
}

// Lê o corpo da requisição como JSON. Corpo vazio vira []; JSON quebrado é 400.
function lerJson(): array
{
    $bruto = file_get_contents('php://input');
    if ($bruto === false || trim($bruto) === '') {
        return [];
    }
    $dados = json_decode($bruto, true);
    if (!is_array($dados)) {
        throw new ErroApi(400, 'O corpo da requisição não é um JSON válido.');
    }
    return $dados;
}

// ---------- dinheiro: as contas são feitas em centavos (inteiros), nunca com float solto ----------
function centavos($decimal): int
{
    return (int) round(((float) $decimal) * 100);
}

function centavosParaSql(int $c): string
{
    return sprintf('%d.%02d', intdiv($c, 100), $c % 100);
}

function centavosParaNumero(int $c): float
{
    return $c / 100;
}
