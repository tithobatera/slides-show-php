<?php
$host = 'localhost'; // Endereço do servidor MySQL
$dbname = ''; // Nome do banco de dados
$username = ''; // Seu usuário do banco de dados
$password = ''; // Senha em branco, pois o MySQL no XAMPP não tem senha por padrão //INSERIR SENHADA BASE DE DADOS

// Conectar ao banco de dados
$conexao = new mysqli($host, $username, $password, $dbname);

// Verificar se há erro de conexão
if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
?>
