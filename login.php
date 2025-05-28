<?php
session_start();

// Incluir o arquivo de configuração (conexão com banco de dados)
include 'config.php';

// Lista de usuários e senhas (essa abordagem é apenas para demonstração, não é segura em um ambiente real)
$usuarios = [
    'admin' => 'senha123',
    'teste' => 'senha123',
    'usuario1' => 'senha456',
    'usuario2' => 'senha789'
];

// Verifique se o método é POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os valores enviados via POST
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verifique se o usuário e senha correspondem a um dos usuários no array
    if (isset($usuarios[$username]) && $usuarios[$username] == $password) {
        // Armazena o nome do usuário na sessão
        $_SESSION['username'] = $username;

        // Retorna uma resposta JSON de sucesso
        echo json_encode([
            'success' => true,
            'message' => 'Login bem-sucedido!'
        ]);
    } else {
        // Caso contrário, retorna uma mensagem de erro
        echo json_encode([
            'success' => false,
            'message' => 'Usuário ou senha incorretos!'
        ]);
    }
}
?>

