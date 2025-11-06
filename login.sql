create database login;
use login;

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    perfil VARCHAR(50) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    cpf_cnpj VARCHAR(20) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Importante que seja VARCHAR(255)
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP
);