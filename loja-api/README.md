# FAKESTORE — API (loja-api)

API em PHP + MySQL/MariaDB da loja de roupas. Responde sempre JSON, com o status HTTP certo.
Linguagem de Programação VIII, UNIFEG.

## Como rodar no XAMPP

1. Inicie **Apache** e **MySQL** no painel do XAMPP. Ative o `mod_rewrite` (já vem ligado).
2. Importe o banco: phpMyAdmin, aba **Importar**, arquivo `banco.sql`. Ou no terminal:
   `C:\xampp\mysql\bin\mysql.exe -u root < banco.sql`
   O script cria o banco `loja`, as 8 tabelas, os dados de exemplo e o usuário `loja_app` do MySQL.
3. Copie `config/config.example.php` para `config/config.php`. O padrão já bate com o `banco.sql`. Troque a senha do `loja_app` nos dois lugares se quiser.
4. Configure o VirtualHost (`C:\xampp\apache\conf\extra\httpd-vhosts.conf`):

```
<VirtualHost *:80>
    ServerName loja.local
    ServerAlias loja.localhost
    DocumentRoot "C:/caminho/fakestore/loja-front/public"
    <Directory "C:/caminho/fakestore/loja-front/public">
        AllowOverride All
        Require all granted
    </Directory>

    Alias /api "C:/caminho/fakestore/loja-api/public"
    <Directory "C:/caminho/fakestore/loja-api/public">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

5. Abra `http://loja.localhost/` (o front) ou teste a API em `http://loja.localhost/api/categorias`.

Confira no `php.ini` que `upload_max_filesize` e `post_max_size` são de pelo menos 4M (no XAMPP deste projeto estão em 40M).

## Logins de teste

| Perfil | E-mail | Senha |
|---|---|---|
| Administrador | admin@loja.com | Admin@123 |
| Cliente | cliente@loja.com | Cliente@123 |

## Endpoints

Todas as rotas ficam sob `/api`. Corpo e resposta em JSON (o upload usa `multipart/form-data`).

### Autenticação

| Método | Rota | Ação | Sucesso | Erros |
|---|---|---|---|---|
| POST | /auth/cadastro | Criar conta (sempre cliente) | 201 | 409, 422 |
| POST | /auth/login | Entrar | 200 | 401 |
| POST | /auth/logout | Sair | 204 | |
| GET | /auth/me | Quem está logado | 200 | 401 |

### Catálogo (público)

| Método | Rota | Ação | Sucesso | Erros |
|---|---|---|---|---|
| GET | /categorias | Listar categorias | 200 | |
| GET | /marcas | Listar marcas, com o total de produtos de cada uma | 200 | |
| GET | /produtos | Listar com filtros e paginação | 200 | |
| GET | /produtos/{id} | Detalhe, com imagens e variações | 200 | 404 |
| POST | /carrinho/validar | Conferir preço e estoque | 200 | 422 |

Filtros de `GET /produtos`: `categoria` (slug), `marca` (slug), `tamanho`, `preco_min`, `preco_max`, `busca`, `destaque=1`, `ordenar` (`recentes`, `preco_asc`, `preco_desc`, `nome`), `pagina`, `por_pagina` (máx. 50).

No admin, `POST`, `PUT` e `PATCH` de produto aceitam `marca_id` (opcional). Marca inexistente retorna 422 e `null` remove a marca do produto.

### Cliente logado

| Método | Rota | Ação | Sucesso | Erros |
|---|---|---|---|---|
| GET | /conta | Ver meus dados | 200 | 401 |
| PUT | /conta | Editar nome e telefone | 200 | 401, 422 |
| PUT | /conta/senha | Trocar senha | 204 | 401, 422 |
| GET | /enderecos | Listar endereços | 200 | 401 |
| POST | /enderecos | Criar endereço | 201 | 401, 422 |
| PUT | /enderecos/{id} | Editar endereço | 200 | 401, 404, 422 |
| DELETE | /enderecos/{id} | Remover endereço | 204 | 401, 404 |
| POST | /pedidos | Finalizar compra | 201 | 401, 422 |
| GET | /pedidos | Meus pedidos | 200 | 401 |
| GET | /pedidos/{id} | Detalhe do pedido | 200 | 401, 404 |
| PUT | /pedidos/{id}/cancelar | Cancelar pedido | 200 | 401, 404, 422 |

### Administrador

| Método | Rota | Ação | Sucesso | Erros |
|---|---|---|---|---|
| GET | /admin/produtos | Listar todos (inclui inativos) | 200 | 401, 403 |
| POST | /admin/produtos | Criar produto | 201 | 401, 403, 422 |
| PUT | /admin/produtos/{id} | Editar produto | 200 | 401, 403, 404, 422 |
| DELETE | /admin/produtos/{id} | Excluir produto | 204 | 401, 403, 404 |
| POST | /admin/produtos/{id}/imagens | Enviar foto (campo `imagem`) | 201 | 401, 403, 404, 413, 422 |
| DELETE | /admin/imagens/{id} | Remover foto | 204 | 401, 403, 404 |
| POST | /admin/produtos/{id}/variacoes | Criar tamanho e estoque | 201 | 401, 403, 404, 409, 422 |
| PUT | /admin/variacoes/{id} | Ajustar estoque | 200 | 401, 403, 404, 422 |
| DELETE | /admin/variacoes/{id} | Remover variação | 204 | 401, 403, 404 |
| POST | /admin/categorias | Criar categoria | 201 | 401, 403, 409, 422 |
| PUT | /admin/categorias/{id} | Renomear categoria | 200 | 401, 403, 404, 409, 422 |
| DELETE | /admin/categorias/{id} | Excluir categoria | 204 | 401, 403, 404, 409 |
| GET | /admin/marcas | Listar marcas | 200 | 401, 403 |
| POST | /admin/marcas | Criar marca | 201 | 401, 403, 409, 422 |
| PUT | /admin/marcas/{id} | Renomear marca | 200 | 401, 403, 404, 409, 422 |
| DELETE | /admin/marcas/{id} | Excluir marca (bloqueia se ainda houver produtos) | 204 | 401, 403, 404, 409 |
| GET | /admin/pedidos | Listar todos os pedidos | 200 | 401, 403 |
| PUT | /admin/pedidos/{id}/status | Mudar status | 200 | 401, 403, 404, 422 |

Regras gerais: 401 sem login, 403 sem permissão, 422 com `{"erros": [...]}`, 409 para conflito, 413 para foto acima de 4 MB. Pedido e endereço de outra pessoa dão 404.

## Estrutura

```
loja-api/
├── public/        index.php (roteador único), .htaccess, uploads/
├── config/        config.php (fora do Git), conexao.php
├── src/
│   ├── rotas.php, resposta.php, auth.php, validacao.php
│   ├── controladores/   (leem o JSON e chamam responder)
│   └── modelos/         (todo o SQL, com PDO e prepared statements)
├── banco.sql
└── colecao-postman.json
```

## Decisões de segurança

- Senha com `password_hash` e `password_verify`; login com mensagem genérica.
- O cadastro ignora o campo `perfil`: toda conta nova é cliente.
- Permissão conferida no servidor, em `index.php`, antes de rodar a rota (`exigirLoginApi` e `exigirPerfilApi`).
- Checkout em transação: o servidor recalcula preços e total, e baixa o estoque com `UPDATE ... WHERE estoque >= ?`, sem deixar negativo.
- Upload: confere o conteúdo com `finfo` e `getimagesize`, aceita JPG, PNG e WEBP, grava com nome aleatório e bloqueia PHP em `uploads/`.
- `display_errors` desligado na API: erros vão para o log do Apache.
- O banco usa o usuário `loja_app` (só SELECT, INSERT, UPDATE e DELETE), e não o `root`.

## Testes no Postman

1. Importe `colecao-postman.json`.
2. Rode as pastas em ordem (1, 2, 3, 4), pelo Collection Runner. O Postman guarda o cookie de sessão entre as requisições.
3. Na pasta 4, nas requisições de upload, escolha um arquivo na aba **Body**.

A coleção tem 93 requisições, com testes de sucesso e erro (401, 403, 404, 409, 413, 422) para visitante, cliente e admin.

## Fora do escopo

Limite de tentativas de login, senha atual na troca de senha, recuperação de senha, redimensionar imagens e pagamento real (o pedido nasce como `aguardando_pagamento`).

## Prints das telas

_Adicione aqui os prints da home, produtos, carrinho, checkout e painel admin._
