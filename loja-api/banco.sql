-- banco.sql — Loja de roupas (FAKESTORE). MySQL / MariaDB, InnoDB, utf8mb4.
-- Importe pelo phpMyAdmin (aba Importar) ou: mysql -u root < banco.sql
-- Logins de teste:  admin@loja.com / Admin@123   |   cliente@loja.com / Cliente@123

CREATE DATABASE IF NOT EXISTS loja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loja;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS itens_pedido, pedidos, produto_variacoes, produto_imagens, produtos, marcas, categorias, enderecos, usuarios;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE usuarios (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  telefone   VARCHAR(11)  NOT NULL DEFAULT '',
  senha_hash VARCHAR(255) NOT NULL,
  perfil     ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
  criado_em  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE enderecos (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id  INT UNSIGNED NOT NULL,
  rotulo      VARCHAR(30)  NOT NULL DEFAULT 'Endereço',
  cep         CHAR(8)      NOT NULL,
  logradouro  VARCHAR(150) NOT NULL,
  numero      VARCHAR(20)  NOT NULL,
  complemento VARCHAR(80)  NOT NULL DEFAULT '',
  bairro      VARCHAR(80)  NOT NULL,
  cidade      VARCHAR(80)  NOT NULL,
  uf          CHAR(2)      NOT NULL,
  principal   TINYINT(1)   NOT NULL DEFAULT 0,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categorias (
  id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE marcas (
  id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(50) NOT NULL UNIQUE,
  slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE produtos (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  categoria_id   INT UNSIGNED NOT NULL,
  marca_id       INT UNSIGNED NULL,
  nome           VARCHAR(150) NOT NULL,
  descricao      TEXT NOT NULL,
  preco          DECIMAL(10,2) NOT NULL,
  preco_original DECIMAL(10,2) NULL,
  destaque       TINYINT(1) NOT NULL DEFAULT 0,
  ativo          TINYINT(1) NOT NULL DEFAULT 1,
  criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_produtos_marca (marca_id),
  FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE RESTRICT,
  FOREIGN KEY (marca_id) REFERENCES marcas(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE produto_imagens (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  produto_id INT UNSIGNED NOT NULL,
  caminho    VARCHAR(255) NOT NULL,
  ordem      INT UNSIGNED NOT NULL DEFAULT 1,
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE produto_variacoes (
  id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  produto_id INT UNSIGNED NOT NULL,
  tamanho    VARCHAR(10) NOT NULL,
  estoque    INT UNSIGNED NOT NULL DEFAULT 0,
  UNIQUE KEY uq_variacao_tam (produto_id, tamanho),
  FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE pedidos (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  usuario_id      INT UNSIGNED NOT NULL,
  status          ENUM('aguardando_pagamento','pago','enviado','entregue','cancelado') NOT NULL DEFAULT 'aguardando_pagamento',
  total           DECIMAL(10,2) NOT NULL,
  end_rotulo      VARCHAR(30)  NOT NULL,
  end_cep         CHAR(8)      NOT NULL,
  end_logradouro  VARCHAR(150) NOT NULL,
  end_numero      VARCHAR(20)  NOT NULL,
  end_complemento VARCHAR(80)  NOT NULL DEFAULT '',
  end_bairro      VARCHAR(80)  NOT NULL,
  end_cidade      VARCHAR(80)  NOT NULL,
  end_uf          CHAR(2)      NOT NULL,
  criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE itens_pedido (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id      INT UNSIGNED NOT NULL,
  variacao_id    INT UNSIGNED NULL,
  nome_produto   VARCHAR(150) NOT NULL,
  tamanho        VARCHAR(10)  NOT NULL,
  quantidade     INT UNSIGNED NOT NULL,
  preco_unitario DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id)   REFERENCES pedidos(id) ON DELETE CASCADE,
  FOREIGN KEY (variacao_id) REFERENCES produto_variacoes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------- dados de exemplo ----------
INSERT INTO usuarios (id, nome, email, telefone, senha_hash, perfil) VALUES
  (1, 'Administrador', 'admin@loja.com',   '',            '$2y$10$yD.OxjVxWXU6F.t5ACgcruTy7XVMjI4B79SpY5gWW4vC/rlAmbq1a', 'admin'),
  (2, 'Cliente Teste', 'cliente@loja.com', '35999990000', '$2y$10$ZLsIJCDLcJ5DaGjwHG3r2.CjkIPcPpTPix1X/vBXokPIJCGxbjFj2', 'cliente');

INSERT INTO enderecos (id, usuario_id, rotulo, cep, logradouro, numero, complemento, bairro, cidade, uf, principal) VALUES
  (1, 2, 'Casa', '37890000', 'Rua Quinze de Novembro', '100', 'Apto 12', 'Centro', 'Muzambinho', 'MG', 1);

INSERT INTO categorias (id, nome, slug) VALUES
  (1, 'Camisetas', 'camisetas'), (2, 'Calças', 'calcas'), (3, 'Vestidos', 'vestidos'), (4, 'Jaquetas', 'jaquetas');

INSERT INTO marcas (id, nome, slug) VALUES
  (1, 'Adidas', 'adidas'), (2, 'Nike', 'nike'), (3, 'New Balance', 'new-balance'), (4, 'Puma', 'puma');

INSERT INTO produtos (id, categoria_id, nome, descricao, preco, preco_original, destaque, ativo) VALUES
  (1,  1, 'Camiseta Básica Retrô',   'Algodão macio, corte reto e gola reforçada. A camiseta que combina com tudo.', 59.90, 79.90, 1, 1),
  (2,  2, 'Calça Flare Canelada',    'Canelada, com cintura alta e cordão ajustável. Boca larga estilo anos 70.', 129.90, NULL, 1, 1),
  (3,  4, 'Jaqueta Corta-Vento',     'Leve, com zíper até o queixo e bolsos laterais. Ótima para dias de vento.', 219.90, 259.90, 1, 1),
  (4,  3, 'Vestido Midi Listrado',   'Malha leve e listras largas, comprimento midi. Fácil de vestir e de combinar.', 149.90, NULL, 1, 1),
  (5,  1, 'Camiseta Oversized Lima', 'Modelagem ampla e ombro caído. Cor lima, bem viva.', 69.90, NULL, 1, 1),
  (6,  2, 'Calça Jogger Cinza',      'Moletom leve com punho na barra e bolsos fundos.', 119.90, 139.90, 1, 1),
  (7,  4, 'Jaqueta Jeans Clássica',  'Jeans de lavagem média, botões metálicos e dois bolsos no peito.', 249.90, NULL, 1, 1),
  (8,  3, 'Vestido Curto Verde',     'Curto, com alças finas e tecido que não amassa.', 109.90, 129.90, 1, 1),
  (9,  1, 'Camiseta Gráfica Raio',   'Estampa de raio laranja no peito. Edição limitada.', 79.90, NULL, 0, 1),
  (10, 2, 'Calça Cargo Verde',       'Seis bolsos, tecido resistente e ajuste na barra.', 159.90, 189.90, 0, 1);

INSERT INTO produto_variacoes (id, produto_id, tamanho, estoque) VALUES
  (10, 1, 'P', 8), (11, 1, 'M', 17), (12, 1, 'G', 0),
  (20, 2, 'P', 4), (21, 2, 'M', 6), (22, 2, 'G', 3),
  (30, 3, 'M', 5), (31, 3, 'G', 2), (32, 3, 'GG', 0),
  (40, 4, 'P', 3), (41, 4, 'M', 7), (42, 4, 'G', 2),
  (50, 5, 'M', 9), (51, 5, 'G', 6),
  (60, 6, 'P', 5), (61, 6, 'M', 8), (62, 6, 'G', 4),
  (70, 7, 'M', 4), (71, 7, 'G', 3),
  (80, 8, 'P', 6), (81, 8, 'M', 2),
  (90, 9, 'P', 3), (91, 9, 'M', 3), (92, 9, 'G', 3),
  (100, 10, 'M', 5), (101, 10, 'G', 5);

-- Duas compras do cliente de teste (o endereço foi copiado para o pedido).
INSERT INTO pedidos (id, usuario_id, status, total, end_rotulo, end_cep, end_logradouro, end_numero, end_complemento, end_bairro, end_cidade, end_uf, criado_em) VALUES
  (1, 2, 'entregue',             189.70, 'Casa', '37890000', 'Rua Quinze de Novembro', '100', 'Apto 12', 'Centro', 'Muzambinho', 'MG', '2026-10-02 14:30:00'),
  (2, 2, 'aguardando_pagamento', 219.90, 'Casa', '37890000', 'Rua Quinze de Novembro', '100', 'Apto 12', 'Centro', 'Muzambinho', 'MG', '2026-11-05 09:12:00');

INSERT INTO itens_pedido (pedido_id, variacao_id, nome_produto, tamanho, quantidade, preco_unitario) VALUES
  (1, 11, 'Camiseta Básica Retrô',   'M', 2, 59.90),
  (1, 51, 'Camiseta Oversized Lima', 'G', 1, 69.90),
  (2, 30, 'Jaqueta Corta-Vento',     'M', 1, 219.90);

-- ---------- usuário do MySQL só da loja (não use o root na API) ----------
CREATE USER IF NOT EXISTS 'loja_app'@'localhost'  IDENTIFIED BY 'troque-esta-senha';
CREATE USER IF NOT EXISTS 'loja_app'@'127.0.0.1'  IDENTIFIED BY 'troque-esta-senha';
GRANT SELECT, INSERT, UPDATE, DELETE ON loja.* TO 'loja_app'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE ON loja.* TO 'loja_app'@'127.0.0.1';
FLUSH PRIVILEGES;
