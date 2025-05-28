<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados




//////////////////////////////////


// Verificar se o ID da apresentação foi passado
if (isset($_GET['id'])) {
    $presentationId = $_GET['id'];

    // Buscar informações da apresentação no banco de dados
    $sql = "SELECT * FROM presentations WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $presentationId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $presentation = $result->fetch_assoc();
    } else {
        echo "<script>alert('Apresentação não encontrada!'); window.location.href = 'paineladmin.php';</script>";
        exit;
    }

    // Processar o formulário de edição
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Verificar se o arquivo foi enviado
        if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $file_name = basename($_FILES['arquivo']['name']);
            $file_path = $upload_dir . $file_name;

            // Move o arquivo para o diretório
            if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $file_path)) {
                // Adiciona o caminho do arquivo ao banco de dados
                $stmt = $conexao->prepare("INSERT INTO presentation_files (presentation_id, file_path) VALUES (?, ?)");
                $stmt->bind_param("is", $presentation_id, $file_path);
                $stmt->execute();
            }
        } else {
            // Caso o arquivo não tenha sido enviado corretamente
            if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
                echo "<script>alert('Erro ao enviar o arquivo.');</script>";
            }
        }

        if (isset($_POST['title'])) {
            $newTitle = $_POST['title'];

            // Obter o título atual para renomear o diretório
            $sqlSelect = "SELECT title FROM presentations WHERE id = ?";
            $stmtSelect = $conexao->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $presentationId);
            $stmtSelect->execute();
            $stmtSelect->bind_result($oldTitle);
            $stmtSelect->fetch();
            $stmtSelect->close();

            // Atualizar título da apresentação no banco de dados
            $sqlUpdate = "UPDATE presentations SET title = ? WHERE id = ?";
            $stmtUpdate = $conexao->prepare($sqlUpdate);
            $stmtUpdate->bind_param("si", $newTitle, $presentationId);

            if ($stmtUpdate->execute()) {
                // Atualizar o título do diretório de arquivos
                $oldDirectory = "uploads/" . $oldTitle; // Diretório antigo
                $newDirectory = "uploads/" . $newTitle; // Novo diretório

                // Verificar se o diretório antigo realmente existe
                if (file_exists($oldDirectory)) {
                    // Verificar se o diretório de destino já existe
                    if (file_exists($newDirectory)) {
                        echo "<script>alert('Título de apresentação já existe.'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                    } else {
                        // Renomear o diretório
                        if (rename($oldDirectory, $newDirectory)) {
                            // Atualizar file_path na tabela presentation_files
                            $sqlUpdateFiles = "UPDATE presentation_files SET file_path = REPLACE(file_path, ?, ?) WHERE presentation_id = ?";
                            $stmtUpdateFiles = $conexao->prepare($sqlUpdateFiles);
                            $stmtUpdateFiles->bind_param("ssi", $oldDirectory, $newDirectory, $presentationId);

                            if ($stmtUpdateFiles->execute()) {
                                echo "<script>alert('Título atualizado e diretório renomeado com sucesso!'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                            } else {
                                echo "<script>alert('Erro ao atualizar o caminho do arquivo.');</script>";
                            }
                        } else {
                            echo "<script>alert('Erro ao renomear o diretório de arquivos.');</script>";
                        }
                    }
                } else {
                    // Exibir mais informações de depuração
                    echo "<script>alert('O diretório antigo não foi encontrado. Caminho esperado: $oldDirectory');</script>";
                }
            } else {
                echo "<script>alert('Erro ao atualizar a apresentação.');</script>";
            }
        }
    }


    //PROCESSAMENTO DE EXCLUSÃO DE ARQUIVOS EM EDITAR.PHP
    // Verifica se o parâmetro 'delete_file' está presente
    if (isset($_GET['delete_file'])) {
        $fileId = $_GET['delete_file'];
        $presentationId = $_GET['id']; // Garantir que o presentationId também esteja disponível.

        // Buscar o caminho do arquivo a ser excluído
        $sqlFile = "SELECT file_path FROM presentation_files WHERE id = ?";
        $stmtFile = $conexao->prepare($sqlFile);
        $stmtFile->bind_param("i", $fileId);
        $stmtFile->execute();
        $fileResult = $stmtFile->get_result();

        if ($fileResult->num_rows > 0) {
            $file = $fileResult->fetch_assoc();
            $filePath = $file['file_path'];

            // Verificar se o arquivo existe no sistema
            if (file_exists($filePath)) {
                // Excluir o arquivo do servidor
                if (unlink($filePath)) {
                    // Excluir o registro do banco de dados
                    $sqlDeleteFile = "DELETE FROM presentation_files WHERE id = ?";
                    $stmtDeleteFile = $conexao->prepare($sqlDeleteFile);
                    $stmtDeleteFile->bind_param("i", $fileId);

                    if ($stmtDeleteFile->execute()) {
                        echo "<script>alert('Arquivo excluído com sucesso!'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                    } else {
                        echo "<script>alert('Erro ao excluir arquivo no banco de dados.'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                    }
                } else {
                    echo "<script>alert('Erro ao excluir o arquivo do servidor.'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                }
            } else {
                echo "<script>alert('Arquivo não encontrado no servidor.'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
            }
        } else {
            echo "<script>alert('Arquivo não encontrado no banco de dados.'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
        }
    }





    // Processar intervalos de arquivos
    if (isset($_POST['intervalo_slide'])) {
        // Itera sobre os intervalos enviados via POST
        foreach ($_POST['intervalo_slide'] as $fileId => $newIntervalo) {
            $newIntervalo = (int)$newIntervalo; // Garantir que o intervalo seja um número inteiro

            // Verifica se o intervalo está dentro do intervalo válido (1-180)
            if ($newIntervalo >= 1 && $newIntervalo <= 180) {
                // Atualiza apenas o intervalo_slide, sem afetar a ordem dos arquivos
                $sqlUpdateInterval = "UPDATE presentation_files SET intervalo_slide = ? WHERE id = ?";
                $stmtUpdateInterval = $conexao->prepare($sqlUpdateInterval);
                $stmtUpdateInterval->bind_param("ii", $newIntervalo, $fileId);

                if ($stmtUpdateInterval->execute()) {
                    // Sucesso na atualização
                    echo "<script>alert('Apresentação atualizada com sucesso!'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                } else {
                    // Falha na atualização
                    echo "<script>alert('Erro ao atualizar intervalo do arquivo.');</script>";
                }
            } else {
                // Caso o intervalo seja inválido
                echo "<script>alert('Intervalo inválido para o arquivo com ID $fileId.');</script>";
            }
        }
    }


    // Verificar se um novo arquivo foi enviado
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $files = $_FILES['files'];
        $intervalos = $_POST['intervalos'];

        // Criar o diretório baseado no título da apresentação
        $presentationTitle = $presentation['title']; // Título da apresentação do banco de dados
        $directory = "uploads/" . preg_replace("/[^a-zA-Z0-9]/", "_", $presentationTitle); // Remover caracteres especiais

        // Verificar se o diretório existe, se não, criar
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true); // Cria o diretório com permissões
        }

        // Loop para processar todos os arquivos enviados
        foreach ($files['name'] as $index => $fileName) {
            $fileTmpName = $files['tmp_name'][$index];
            $filePath = $directory . "/" . basename($fileName); // Caminho completo com o nome do arquivo
            $intervalo = (int)$intervalos[$index];

            // Mover o arquivo para o diretório correto
            if (move_uploaded_file($fileTmpName, $filePath)) {
                // Buscar a ordem do último arquivo
                $sqlOrder = "SELECT MAX(`order_number`) AS max_order FROM presentation_files WHERE presentation_id = ?";
                $stmtOrder = $conexao->prepare($sqlOrder);
                $stmtOrder->bind_param("i", $presentationId);
                $stmtOrder->execute();
                $orderResult = $stmtOrder->get_result();
                $orderData = $orderResult->fetch_assoc();
                $maxOrder = $orderData['max_order'] !== null ? $orderData['max_order'] : 0; // Se não houver arquivos, consideramos a ordem como 0

                $newOrder = $maxOrder + 1; // Novo arquivo receberá a próxima ordem

                // Inserir o novo arquivo no banco de dados
                $sqlInsertFile = "INSERT INTO presentation_files (presentation_id, file_path, intervalo_slide, `order_number`) VALUES (?, ?, ?, ?)";
                $stmtInsertFile = $conexao->prepare($sqlInsertFile);
                $stmtInsertFile->bind_param("isii", $presentationId, $filePath, $intervalo, $newOrder);

                if ($stmtInsertFile->execute()) {
                    echo "<script>alert('Arquivo adicionado com sucesso!'); window.location.href = 'editar.php?id=" . $presentationId . "';</script>";
                } else {
                    echo "<script>alert('Erro ao adicionar o arquivo no banco de dados.');</script>";
                }
            } else {
                echo "<script>alert('Erro ao mover o arquivo para o diretório correto.');</script>";
            }
        }
    }
}


?>

<?php
if (isset($_POST['action']) && $_POST['action'] === 'update_order') {
    // Captura o array de novos arquivos e suas ordens
    $orderData = $_POST['order'];

    // Atualiza a ordem no banco de dados
    foreach ($orderData as $file) {
        $fileId = $file['id'];
        $newOrder = $file['order'];

        // Atualize a ordem no banco de dados para o arquivo
        $sqlUpdate = "UPDATE presentation_files SET order_number = ? WHERE id = ?";
        $stmtUpdate = $conexao->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ii", $newOrder, $fileId);
        $stmtUpdate->execute();
    }

    // Retorne uma resposta de sucesso para o AJAX
    echo json_encode(['status' => 'success']);
    exit;
}

?>



<?php

// Verifique se a requisição contém a atualização da ordem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decodificar os dados JSON enviados via AJAX
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['updateOrder'])) {
        $newOrder = $data['updateOrder'];
        $presentationId = $_GET['id']; // ID da apresentação

        // Iniciar uma transação para garantir que todas as alterações sejam feitas corretamente
        $conexao->begin_transaction();

        try {
            // Atualizar a ordem de cada arquivo no banco de dados
            foreach ($newOrder as $file) {
                $fileId = $file['id'];
                $order = $file['order'];

                // Atualizar a ordem no banco de dados
                $sql = "UPDATE presentation_files SET order_number = ? WHERE id = ? AND presentation_id = ?";
                $stmt = $conexao->prepare($sql);
                $stmt->bind_param("iii", $order, $fileId, $presentationId);
                $stmt->execute();
            }

            // Confirmar a transação
            $conexao->commit();

            // Retornar sucesso para o AJAX
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            // Se ocorrer um erro, desfazer a transação
            $conexao->rollback();

            // Retornar erro para o AJAX
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Dados de ordem não recebidos.']);
    }

    exit;
}
?>




<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Apresentação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-sm navbar-dark shadow shadow-sm" id="qs-main-header" style="background-size: 100% 100%;background-image:url('imagens/backgroundNav.jpg');">
        <div class="container">
            <a id="qs-modules-link" href="#" class="navbar-brand" onclick="$('#qs-modules').show(); $('#qs-modules-link').addClass('active');"><img src="https://qunitserver.pt-fk.kabi.portugal.fresenius.de/apps/ep/assets/images/qunitsimbol_v4.3.png" width="30" height="30" class="d-inline-block align-top" alt=""></a>
            <a href="paineladmin.php" class="navbar-brand">Painel Administrativo</a>
            <style>
                html,
                body {
                    width: 100%;
                    margin: 0;
                    padding: 0;
                    overflow-x: hidden;
                    /* Previne scroll horizontal */
                }

                a {
                    text-decoration: none;
                    list-style: none;
                }

                #qs-modules {
                    text-align: left;
                    position: fixed;
                    display: none;
                    top: 42px;
                    width: 1000px;
                    background-color: white;
                    box-shadow: 0px 0px 10px #999;
                    padding: 10px;
                    z-index: 999999;
                    border-radius: 5px;
                }

                #qs-modules a {
                    display: inline-block !important;
                    text-decoration: none;
                    font-weight: normal;
                    color: #666;
                    padding: 0;
                    padding-left: 10px;
                    width: 230px;
                    height: 40px;
                    border-radius: 5px;
                }

                #qs-modules a:hover {
                    background: #f3f3f3;
                }

                #qs-modules .qs-modules-icon {
                    line-height: 40px;
                    display: inline-block;
                    width: 20px;
                    text-align: center;
                    vertical-align: middle;
                }

                #qs-modules .qs-modules-title {
                    line-height: 40px;
                    display: inline-block;
                    font-size: 13px;
                    margin-left: 10px;
                    vertical-align: middle;
                }

                #qs-modules a img {
                    max-width: 30px;
                    float: right;
                }

                #qs-modules a i {
                    font-size: 20px;
                    color: #1B5B93;
                }

                #qs-modules a.active {
                    background: #1B5B93;
                    color: #fff;
                }

                #qs-modules a.active i {
                    background: #1B5B93;
                    color: #fff;
                }

                #qs-global-message {
                    position: fixed;
                    z-index: 999997;
                    margin-top: 200px;
                    font-size: 14px;
                    left: 0;
                    width: 100%;
                }
            </style>

            <style>
                /* Defina a largura máxima para o dropdown */
                #navbarDropdownMenuLink {
                    max-width: 200px;
                    /* Defina o valor que achar adequado */
                    width: 100px;
                    /* Garante que o conteúdo se ajuste automaticamente */

                }
            </style>



            <style>
                /* Adicionando o efeito de hover com sombra e transição suave */
                .menu-item {
                    transition: box-shadow 0.3s ease, transform 0.3s ease;
                }

                .menu-item:hover {
                    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
                    /* Sombra preta ao passar o mouse */
                    transform: translateY(-3px);
                    /* Levanta o item um pouco */
                }
            </style>

            <style>
                .form-control {
                    width: 500px;
                }
            </style>



            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>


            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php
                            // Exibe o nome do usuário logado
                            echo $_SESSION['username'];
                            ?>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
                            <a class="dropdown-item" href="novaapresentacao.php"><i class="far fa-user fa-fw"></i> Nova apresentação</a>
                            <a class="dropdown-item" href="Apresentacoes.php"><i class="far fa-user fa-fw"></i> Apresentações</a>
                            <a class="dropdown-item" href="televisores.php"><i class="far fa-user fa-fw"></i> Televisores</a>
                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="logout.php"><i class="far fa-user fa-fw"></i> Logout</a>


                        </div>
                    </li>
                </ul>
            </div>

        </div><!-- /.container-fluid -->
    </nav>



    <div class="container-fluid bg-light">
        <div class="row text-center justify-content-center" style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);">
            <div class="container d-flex justify-content-between w-100 px-3 flex-wrap">
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <a href="home.php" class="text-muted d-block p-3 menu-item">
                        <i class="fas fa-coffee mr-2"></i>Home
                    </a>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <a href="novaapresentacao.php" class="text-muted d-block p-3 menu-item">
                        <i class="fas fa-coffee mr-2"></i>Criar apresentação
                    </a>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <a href="apresentacoes.php" class="text-muted d-block p-3 menu-item">
                        <i class="far fa-flag mr-2"></i>Apresentações
                    </a>
                </div>
                <div class="col-12 col-md-3 mb-2 mb-md-0">
                    <a href="televisores.php" class="text-muted d-block p-3 menu-item">
                        <i class="far fa-map mr-3"></i>Televisores
                    </a>
                </div>
            </div>
        </div>
    </div>




    <!-- Formulário para editar o título da apresentação -->
    <div class="row text-left" style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 20px;">
        <div style=" padding: 20px; border-radius: 8px; margin-top: 20px; width: 100%; height: auto;">

            <h3 class="navbar-brand">Editar apresentação</h3>

            <form action="editar.php?id=<?php echo $presentationId; ?>" method="POST" enctype="multipart/form-data">


                <div class="mb-3">
                    <label for="title" class="form-label">Título da Apresentação:</label>
                    <input type="text" id="title" name="title" class="form-control custom-input w-50" value="<?php echo $presentation['title']; ?>" required />
                </div>
                <button type="submit" class="btn btn-primary">Atualizar Titulo da Apresentação</button>
            </form>
        </div>
    </div>



    <div class="row text-left" style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 20px;">
        <div style=" padding: 20px; border-radius: 8px; margin-top: 20px; width: 100%; height: auto;">
            <h3 class="navbar-brand">Arquivos da apresentação</h3>

            <form action="editar.php?id=<?php echo $presentationId; ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="mb-3" id="fileUploadContainer">
                    <label for="file" class="form-label">Adicionar mais arquivos para apresentação:</label>
                    <div class="file-upload-container">
                        <div class="file-upload-item" style="display: flex; gap: 10px;">
                            <input type="file" name="files[]" class="form-control form-control" required style="width: 250px;" />
                            <input type="number" name="intervalos[]" class="form-control form-control" placeholder="Segundos" required min="1" style="width: 110px;" />
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <button type="button" class="btn btn-secondary" id="addFileButton">+ Adicionar mais arquivos</button>
                    <button type="submit" class="btn btn-primary" id="submitButton">Adicionar Arquivo</button>
                </div>
            </form>



            <!-- Formulário para editar o intervalo de cada arquivo -->
            <form action="editar.php?id=<?php echo $presentationId; ?>" method="POST">
                <div class="list-group mt-3">

                    <button type="submit" class="btn btn-warning mt-3">Atualizar Apresentação</button>


                    <?php
                    // Exibir arquivos corretamente
                    $sqlFiles = "SELECT * FROM presentation_files WHERE presentation_id = ? ORDER BY `order_number` ASC";
                    $stmtFiles = $conexao->prepare($sqlFiles);
                    $stmtFiles->bind_param("i", $presentationId);
                    $stmtFiles->execute();
                    $resultFiles = $stmtFiles->get_result();

                    if ($resultFiles->num_rows > 0) {
                        // Início da tabela dentro de um contêiner responsivo
                        echo '<div class="table-responsive">';
                        echo '<table class="table table-bordered">';
                        echo '<thead class="thead-light">';
                        echo '<tr>';
                        echo '<th style="text-align: center;">Mover</th>';  // Coluna de Mover
                        echo '<th style="text-align: center;">Nome do Arquivo</th>';  // Coluna de Nome do Arquivo
                        echo '<th style="text-align: center;">Intervalo Slide</th>';
                        echo '<th style="text-align: center;">Excluir Arquivo</th>';  // Coluna de Excluir Arquivo
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        // Exibe os arquivos
                        while ($row = $resultFiles->fetch_assoc()) {
                            echo '<tr>';

                            // Botões de setas para alterar a ordem
                            echo '<td style="text-align: center; background-color: rgba(151, 151, 151, 0.2);">';
                            echo '<a href="javascript:void(0);" class="btn btn-light btn-sm move-file-up mr-2" data-file-id="' . $row['id'] . '" data-presentation-id="' . $presentationId . '" style="border: 1px solid #ccc; color: #333; background-color: #f8f9fa; transition: background-color 0.3s ease;">↑</a>';
                            echo '<a href="javascript:void(0);" class="btn btn-light btn-sm move-file-down" data-file-id="' . $row['id'] . '" data-presentation-id="' . $presentationId . '" style="border: 1px solid #ccc; color: #333; background-color: #f8f9fa; transition: background-color 0.3s ease;">↓</a>';
                            echo '</td>';

                            // Nome do arquivo
                            echo '<td style="text-align: left; overflow: hidden; text-overflow: ellipsis;">';
                            echo '<a href="' . $row['file_path'] . '" class="text-decoration-none" target="_blank">' . basename($row['file_path']) . '</a>';
                            echo '</td>';

                            // Campo para editar o intervalo
                            echo '<td style="text-align: center; background-color: rgba(151, 151, 151, 0.2);">';
                            echo '<input type="number" name="intervalo_slide[' . $row['id'] . ']" value="' . $row['intervalo_slide'] . '" class="form-control" min="1" max="180" style="width: 180px;">';
                            echo '</td>';

                            // Botão apagar arquivo
                            echo '<td style="text-align: center;">';
                            echo '<a href="editar.php?id=' . $presentationId . '&delete_file=' . $row['id'] . '" class="btn btn-danger btn-sm" onclick="return confirm(\'Tem certeza que deseja excluir este arquivo?\')">Apagar Arquivo</a>';
                            echo '</td>';

                            echo '</tr>';
                        }

                        echo '</tbody>';
                        echo '</table>';
                        echo '</div>'; // Fechar a div de responsividade
                    } else {
                        echo "<p>Nenhum arquivo encontrado.</p>";
                    }
                    ?>

                </div>
            </form>
        </div>
    </div>
    </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>


    <script>
        // Detectar quando um arquivo é selecionado
        const fileUploadContainer = document.getElementById('fileUploadContainer');

        fileUploadContainer.addEventListener('change', function(event) {
            const input = event.target;

            // Verifica se o campo alterado é o de "files[]" (upload de arquivos)
            if (input.type === 'file' && input.files.length > 0) {
                const file = input.files[0];

                // Verifica se o arquivo é um vídeo
                if (file.type.match('video.*')) {
                    const videoElement = document.createElement('video');
                    const intervalInput = input.nextElementSibling; // Encontrar o campo de intervalo correspondente

                    // Carregar o vídeo e obter a duração
                    const videoURL = URL.createObjectURL(file);
                    videoElement.src = videoURL;

                    videoElement.onloadedmetadata = function() {
                        const duration = videoElement.duration; // Duração do vídeo em segundos
                        intervalInput.value = Math.ceil(duration); // Preencher o campo de intervalo com a duração do vídeo (em segundos)
                    };
                }
            }
        });
    </script>




    <script>
        // Adicionar novo campo de upload de arquivo e intervalo
        document.getElementById("addFileButton").addEventListener("click", function() {
            const container = document.getElementById("fileUploadContainer");

            // Criar novo item para upload de arquivo
            const newFileUploadItem = document.createElement("div");
            newFileUploadItem.classList.add("file-upload-item");
            newFileUploadItem.style.display = "flex"; // Usar Flexbox para alinhar os itens lado a lado
            newFileUploadItem.style.gap = "10px"; // Espaçamento entre os campos

            // Criar campo para o arquivo
            const fileInput = document.createElement("input");
            fileInput.type = "file";
            fileInput.name = "files[]"; // Mudar para um array para permitir vários arquivos
            fileInput.classList.add("form-control");
            fileInput.required = true;
            fileInput.style.width = "250px"; // Definir a largura do campo de arquivo

            // Criar campo para intervalo do arquivo
            const intervalInput = document.createElement("input");
            intervalInput.type = "number";
            intervalInput.name = "intervalos[]";
            intervalInput.classList.add("form-control");
            intervalInput.placeholder = "Segundos";
            intervalInput.required = true;
            intervalInput.min = 1;
            intervalInput.style.width = "110px"; // Definir a largura do campo de intervalo

            // Criar o botão de "X" para excluir o campo
            const closeButton = document.createElement("button");
            closeButton.type = "button";
            closeButton.classList.add("btn", "btn-danger", "btn-sm");
            closeButton.textContent = "X";

            // Adicionar um evento de clique para fechar o campo
            closeButton.addEventListener("click", function() {
                newFileUploadItem.remove(); // Remove o item do DOM
            });

            // Adicionar os campos de arquivo, intervalo e o botão de exclusão ao item
            newFileUploadItem.appendChild(fileInput);
            newFileUploadItem.appendChild(intervalInput);
            newFileUploadItem.appendChild(closeButton);

            // Adicionar o novo item ao container
            container.appendChild(newFileUploadItem);
        });
    </script>


    <script>
        function reloadTable() {
            $.ajax({
                url: "editar.php?id=<?php echo $presentationId; ?>",
                method: "GET",
                success: function(response) {
                    // Substituir o conteúdo da tabela com a nova ordem
                    $('#fileTable').html(response);
                }
            });
        }
    </script>
    <script>
        ///////////////////////BOTAO ATUALIZAÇÃO DA NOVA ORDEM DOS ARQUIVOS//////////////////////////////////////


        // Função para atualizar a tabela após a ordem ser alterada
        function reloadTable() {
            $.ajax({
                url: "editar.php?id=<?php echo $presentationId; ?>",
                method: "GET",
                success: function(response) {
                    // Substituir a tabela atual pela nova
                    $('#fileTable').html(response);
                }
            });
        }
    </script>



    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Mover arquivos para cima
        $('.move-file-up').on('click', function() {
            var currentRow = $(this).closest('tr');
            var prevRow = currentRow.prev(); // Linha anterior

            if (prevRow.length) {
                currentRow.insertBefore(prevRow); // Mover para cima

                // Atualizar a ordem no banco de dados
                updateFileOrder(currentRow);
            }
        });

        // Mover arquivos para baixo
        $('.move-file-down').on('click', function() {
            var currentRow = $(this).closest('tr');
            var nextRow = currentRow.next(); // Linha seguinte

            if (nextRow.length) {
                currentRow.insertAfter(nextRow); // Mover para baixo

                // Atualizar a ordem no banco de dados
                updateFileOrder(currentRow);
            }
        });

        // Função para atualizar a ordem no banco de dados
        function updateFileOrder(movedRow) {
            var orderData = [];

            // Percorrer todas as linhas da tabela e capturar a nova ordem
            $('tr').each(function(index) {
                var fileId = $(this).find('.move-file-up').data('file-id'); // Captura o ID do arquivo
                orderData.push({
                    id: fileId,
                    order: index + 1 // A nova ordem
                });
            });

            // Enviar a nova ordem para o servidor via AJAX
            $.ajax({
                url: 'editar.php?id=<?php echo $presentationId; ?>',
                method: 'POST',
                data: {
                    action: 'update_order',
                    order: orderData
                },
                success: function(response) {
                    var result = JSON.parse(response);
                    if (result.status === 'success') {
                        alert("Ordem atualizada com sucesso!");
                    } else {
                        alert("Erro ao atualizar a ordem.");
                    }
                }
            });
        }
    </script>


    <script>
        function deleteFile(fileId) {
            // Confirmar a exclusão do arquivo
            if (confirm('Tem certeza que deseja excluir este arquivo?')) {
                // Enviar solicitação AJAX para o PHP para excluir o arquivo
                var xhr = new XMLHttpRequest();
                xhr.open("GET", "editar.php?delete_file=" + fileId + "&id=<?php echo $presentationId; ?>", true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        var response = xhr.responseText;
                        if (response.includes('Arquivo excluído com sucesso!')) {
                            // Se o arquivo foi excluído com sucesso, remover o arquivo da tela
                            document.getElementById('file_' + fileId).remove();
                        }
                        alert(response);
                    }
                };
                xhr.send();
            }
        }
    </script>
</body>

</html>