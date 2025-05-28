<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados

// Função para remover o diretório e todos os seus arquivos
function deleteSelected($dirPath)
{
    // Verifica se o diretório existe
    if (is_dir($dirPath)) {
        // Lista todos os arquivos e subdiretórios do diretório
        $files = array_diff(scandir($dirPath), array('.', '..'));  // Ignora . e ..

        foreach ($files as $file) {
            $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                // Se for um diretório, chama recursivamente para deletar seus conteúdos
                deleteSelected($filePath);
            } else {
                // Se for um arquivo, remove o arquivo
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }

        // Após apagar todos os arquivos, tenta remover o diretório
        if (rmdir($dirPath)) {
            echo "Diretório excluído com sucesso.";
        } else {
            echo "Erro ao excluir o diretório.";
        }
    }
}

// Função de exclusão de apresentações e arquivos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os IDs das apresentações enviadas no formato JSON
    $ids = json_decode($_POST['ids'], true);

    // Verifica se é um array válido e tem elementos
    if (is_array($ids) && count($ids) > 0) {
        // Inicia a transação para garantir a integridade da exclusão
        $conexao->begin_transaction();

        try {
            // Prepara a consulta para excluir os arquivos relacionados na tabela presentation_files
            $file_sql = "SELECT file_path FROM presentation_files WHERE presentation_id = ?";
            $file_stmt = $conexao->prepare($file_sql);

            // Prepara a consulta para excluir as apresentações
            $delete_presentation_sql = "DELETE FROM presentations WHERE id = ?";
            $delete_presentation_stmt = $conexao->prepare($delete_presentation_sql);

            // Itera sobre os IDs das apresentações selecionadas
            foreach ($ids as $presentation_id) {
                // Obter o título da apresentação antes de deletá-la (para garantir que o título estará disponível)
                $title_sql = "SELECT title FROM presentations WHERE id = ?";
                $title_stmt = $conexao->prepare($title_sql);
                $title_stmt->bind_param("i", $presentation_id);
                $title_stmt->execute();
                $title_result = $title_stmt->get_result();

                if ($title_row = $title_result->fetch_assoc()) {
                    $presentation_title = $title_row['title'];  // Título da apresentação
                    $directory_path = "uploads/" . preg_replace('/[^a-zA-Z0-9_]/', '_', $presentation_title);  // Caminho do diretório 'uploads/titulo_da_apresentacao'

                    // Excluir os arquivos relacionados
                    $file_stmt->bind_param("i", $presentation_id);
                    $file_stmt->execute();
                    $file_result = $file_stmt->get_result();

                    // Excluir fisicamente os arquivos do diretório
                    while ($file_row = $file_result->fetch_assoc()) {
                        $filePath = $file_row['file_path'];
                        if (file_exists($filePath)) {
                            unlink($filePath);  // Remove o arquivo do sistema
                        }
                    }

                    // Excluir os registros de arquivos na tabela presentation_files
                    $delete_files_sql = "DELETE FROM presentation_files WHERE presentation_id = ?";
                    $delete_files_stmt = $conexao->prepare($delete_files_sql);
                    $delete_files_stmt->bind_param("i", $presentation_id);
                    $delete_files_stmt->execute();

                    // Excluir a apresentação da tabela presentations
                    $delete_presentation_stmt->bind_param("i", $presentation_id);
                    $delete_presentation_stmt->execute();

                    // Remover o diretório, se existir e não estiver vazio
                    if (is_dir($directory_path)) {
                        deleteSelected($directory_path);
                    }
                }
            }

            // Confirma a transação
            $conexao->commit();
            echo json_encode(['success' => true, 'message' => 'Apresentações excluídas com sucesso!']);
        } catch (Exception $e) {
            // Se algo der errado, faz rollback
            $conexao->rollback();
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir as apresentações: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nenhum ID válido foi enviado']);
    }
}
