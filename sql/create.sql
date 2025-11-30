DROP DATABASE IF EXISTS amapon;
CREATE DATABASE amapon;

USE amapon;

-- TABELA DE ENDEREÇO (CORRIGIDA)
CREATE TABLE endereco_paciente(
    id_endereco INT AUTO_INCREMENT PRIMARY KEY,
    cep VARCHAR(9) NOT NULL,
    uf CHAR(2) NOT NULL,
    cidade VARCHAR(150) NOT NULL,
    rua VARCHAR(200)  NOT NULL,
    complemento VARCHAR(150)
);

-- TABELA ACOMPANHANTE (SEM ALTERAÇÕES CRÍTICAS)
CREATE TABLE acompanhante(
    id_acompanhante INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    idade INT NOT NULL,
    cpf_acompanhante VARCHAR(11) NOT NULL,
    telefone_acompanhante VARCHAR(20)
);

-- TABELA PACIENTES (CORRIGIDA)
CREATE TABLE pacientes(
    id_paciente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(200) NOT NULL,
    idade INT NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    telefone VARCHAR(20),
    email VARCHAR(200) NOT NULL,
    senha VARCHAR(255) NOT NULL,  -- NECESSÁRIO para password_hash
    id_endereco INT,
    id_acompanhante INT,
    FOREIGN KEY (id_endereco) REFERENCES endereco_paciente(id_endereco),
    FOREIGN KEY (id_acompanhante) REFERENCES acompanhante(id_acompanhante)
);
