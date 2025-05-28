<?php
// Inclua a conexão com o banco de dados
include 'config.php';

// Verificar se o termo de busca foi fornecido
if (isset($_GET['search'])) {
    $searchQuery = $_GET['search'];
    
    // Consulta os televisores com base no termo de busca
    $sql = "SELECT * FROM televisores WHERE nome LIKE ?";
    $stmt = $conexao->prepare($sql);
    $searchParam = '%' . $searchQuery . '%'; // Adiciona os wildcards para a busca
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se há resultados
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Exibe cada televisor encontrado
            echo "<ul class='list-group'>
                    <li class='list-group-item d-flex justify-content-between align-items-center'>
                        " . $row['nome'] . "
                        <div>
                            <a href='editartelevisores.php?id=" . $row['id'] . "' class='btn btn-primary btn-sm'>Editar</a>
                            <button onclick='confirmDelete(" . $row['id'] . ")' class='btn btn-danger btn-sm ms-2'>Excluir</button>
                        </div>
                    </li>
                </ul>";
        }
    } else {
        echo "<p>Nenhum televisor encontrado.</p>";
    }
}
?>
