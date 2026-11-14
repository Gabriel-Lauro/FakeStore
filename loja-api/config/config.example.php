<?php
// config/config.example.php — copie para config.php e ajuste. O config.php NÃO vai para o Git.
// Use um usuário do MySQL próprio da loja, e não o root.

const DB_HOST = '127.0.0.1';
const DB_NOME = 'loja';
const DB_USUARIO = 'loja_app';
const DB_SENHA = 'troque-esta-senha';

// Pepper (opcional, ver 05-cuidados-e-seguranca.md). Se usar, regere os hashes do banco.sql.
const PEPPER = '';

const UPLOAD_MAX_BYTES = 4 * 1024 * 1024; // 4 MB
