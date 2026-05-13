<?php
$c = new mysqli('127.0.0.1', 'root', '');
if ($c->connect_error) die("MySQL error: " . $c->connect_error);

// Crear la BD de usuarios si no existe
$c->query('CREATE DATABASE IF NOT EXISTS api_usuarios_dsw');
echo "BD api_usuarios_dsw: OK\n";

// Crear la tabla users (como la de Laravel)
$c->select_db('api_usuarios_dsw');
$c->query("CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Tabla users: OK\n";

// Crear la tabla personal_access_tokens (Sanctum)
$c->query("CREATE TABLE IF NOT EXISTS personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "Tabla personal_access_tokens: OK\n";

echo "\nTodo listo para generar tokens de prueba.\n";
$c->close();
