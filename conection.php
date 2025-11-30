<?php
// conection.php
$servername = "localhost";
$username = "root";
$password = "260908";
$database = "amapon";

// Criar conexão
$conn = new mysqli($servername, $username, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    // Em um ambiente de produção, é melhor registrar o erro e mostrar uma mensagem genérica.
    die("Erro na conexão: " . $conn->connect_error);
}

// Definir o charset para UTF-8
$conn->set_charset("utf8mb4");

// Opcional: Definir a conexão como global para fácil acesso, embora não seja a melhor prática em projetos grandes.
// global $conn; 
