# FakeStore

Loja de roupas e calçados com estética neobrutalista retrô: bordas pretas, sombras duras e tipografia pesada e inclinada.
Trabalho de Linguagem de Programação VIII (UNIFEG), Ciência da Computação, 8º período.

## Visão geral

| Parte | Pasta | O que é |
| --- | --- | --- |
| Front | `loja-front/` | Páginas em PHP, HTML, CSS e JS puro. Consome a API com `fetch`. |
| API | `loja-api/` | API REST em PHP 7.4+ com MariaDB/MySQL. Documentação própria em `loja-api/README.md`. |

Funcionalidades: catálogo com filtros (categoria, marca, tamanho, preço e busca), página de produto com variações, carrinho, checkout, endereços, pedidos, conta do cliente e painel administrativo (produtos, marcas, imagens).

## Estrutura

```
fakestore/
├── .github/workflows/deploy.yml   deploy automático na VPS a cada push na main
├── loja-api/
│   ├── banco.sql                  esquema e dados iniciais
│   ├── config/                    conexão e config.example.php
│   ├── public/                    index.php (roteador único) e uploads/
│   └── src/                       controladores, modelos, validação, rotas
└── loja-front/
    ├── includes/                  cabeçalho, rodapé, autenticação, rotas das páginas
    ├── paginas/<nome>/            pagina.php, estilo.css e img/ de cada página
    └── public/                    index.php, css/, js/ e img/ (raiz web)
```

Cada página é uma pasta em `loja-front/paginas/<nome>/`, com o CSS e as imagens que só ela usa. O CSS padrão do site fica em `loja-front/public/css`.

## Rodando localmente (XAMPP)

1. Ligue Apache e MySQL.
2. Importe `loja-api/banco.sql`.
3. Copie `loja-api/config/config.example.php` para `loja-api/config/config.php` e ajuste os dados do banco.
4. Aponte o VirtualHost com raiz em `loja-front/public` e `Alias /api` para `loja-api/public` (exemplo no README da API).
5. Abra `http://loja.localhost/`.

Logins de teste do ambiente local: `admin@loja.com` / `Admin@123` e `cliente@loja.com` / `Cliente@123`.

## Modo demo

`/?demo=1` (cliente) e `/?demo=admin` (administrador) usam uma API simulada no navegador, sem back-end.
A constante `MODO_DEMO` em `loja-front/includes/auth.php` está em `false`. Só ligue para apresentações.

## Deploy

O deploy é automático: todo push na `main` dispara o workflow **Deploy na VPS**, que sincroniza o código com `rsync` por SSH e testa se o site responde.

O que o deploy **não** toca:

- `loja-api/config/config.php`: segredos do servidor, fora do Git.
- `loja-api/public/uploads/`: fotos enviadas pelo admin, fora do Git.

Secrets necessários no repositório: `VPS_HOST`, `VPS_USER`, `VPS_SSH_KEY` e `VPS_KNOWN_HOSTS`.
O workflow também pode ser disparado manualmente na aba Actions (`workflow_dispatch`).

### Servidor

- Ubuntu com nginx, PHP-FPM 7.4 e MariaDB.
- Raiz do front em `loja-front/public`; `/api/` é encaminhado ao roteador único da API; `/api/uploads/` serve as fotos sem nunca executar PHP.
- HTTPS com Let's Encrypt emitido para o IP do servidor (certificado de curta duração, renovado automaticamente pelo certbot). HTTP redireciona para HTTPS.
- Cookie de sessão com `Secure`, `HttpOnly` e `SameSite=Lax`.
- Login por senha desativado no SSH (somente chave), fail2ban ativo e atualizações de segurança automáticas.

## Segurança

- Senhas com hash (`password_hash`) e sessão compartilhada entre front e API.
- Consultas com PDO e parâmetros vinculados.
- Uploads validados por tipo e tamanho (4 MB) e servidos sem execução de código.
- `config.php` e `uploads/` ficam fora do repositório (veja o `.gitignore`).

## Licença

Veja o arquivo [LICENSE](LICENSE).
