<?php
$host = 'localhost'; // Endereço do servidor MySQL
$dbname = 'projeto_fresenius'; // Nome do banco de dados
$username = 'root'; // Seu usuário do banco de dados
$password = 'Titho@1810'; // Senha em branco, pois o MySQL no XAMPP não tem senha por padrão

// Conectar ao banco de dados
$conexao = new mysqli($host, $username, $password, $dbname);

// Verificar se há erro de conexão
if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}
?>
