# FAKESTORE

Loja de roupas. Linguagem de Programação VIII (UNIFEG), Ciência da Computação, 8º período.
Estética neobrutalista retrô, com áreas minimalistas.

- `loja-front/`: páginas e interface (PHP + HTML/CSS/JS). Consome a API com `fetch`.
  Cada página é uma pasta em `loja-front/paginas/<nome>/` com `pagina.php`, `estilo.css` (CSS só dela) e `img/` (imagens só dela).
  O CSS padrão do site fica em `loja-front/public/css`.
- `loja-api/`: API em PHP + MySQL. Veja o `loja-api/README.md` (como rodar, endpoints, segurança, Postman).

## Rodar rápido (XAMPP)

1. Ligue Apache e MySQL.
2. Importe `loja-api/banco.sql`.
3. Copie `loja-api/config/config.example.php` para `config.php`.
4. Aponte o VirtualHost: raiz em `loja-front/public` e `Alias /api` para `loja-api/public` (exemplo no README da API).
5. Abra `http://loja.localhost/`.

Logins de teste: `admin@loja.com` / `Admin@123` e `cliente@loja.com` / `Cliente@123`.

## Modo demo do front

`/?demo=1` (cliente) e `/?demo=admin` (admin) usam uma API de mentira no navegador, sem back-end.
**Antes de entregar, coloque `MODO_DEMO` como `false` em `loja-front/includes/auth.php`.**
