<?php
// Inclui o arquivo de configuração para a conexão com o banco de dados
include 'config.php';

// Verifique se os dados foram recebidos via POST
if (isset($_POST['presentation_id']) && isset($_POST['televisor_id'])) {
    $presentation_id = $_POST['presentation_id'];
    $televisor_id = $_POST['televisor_id'];

    // Debug: Verifique os dados recebidos
    var_dump($presentation_id, $televisor_id);  // Adiciona um var_dump para verificar se os valores foram recebidos

    try {
        // Conectar ao banco de dados
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Atualizar o banco de dados
        $sql = "UPDATE televisores SET presentation_id = :presentation_id WHERE id = :televisor_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':presentation_id', $presentation_id, PDO::PARAM_INT);
        $stmt->bindParam(':televisor_id', $televisor_id, PDO::PARAM_INT);
        $stmt->execute();

        // Retorne uma resposta de sucesso
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        // Retorne erro caso falhe
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    // Se os dados não foram recebidos corretamente
    echo json_encode(['status' => 'error', 'message' => 'Dados não recebidos corretamente']);
}
?>
