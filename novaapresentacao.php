<!-- PHP -->
<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados
?>

<?php // Lógica PHP para obter o nome do usuário logado
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário desconhecido'; // Caso o nome não esteja disponível
?>

<?php //PROCESSAMENTO DO FORMULARIO DE CRIAÇÃO DE UMA APRESENTAÇÃO 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obter os dados do formulário
    $title = $_POST['title']; // Título da apresentação
    $files = $_FILES['files']; // Arquivos enviados (multiplos)
    $intervalos = isset($_POST['intervalos']) ? $_POST['intervalos'] : []; // Intervalos para cada arquivo

    // Validar o valor do intervalo entre slides
    if (isset($_POST['intervalo_slide'])) {
        $intervalo_slide = (int)$_POST['intervalo_slide'];
        if ($intervalo_slide < 1 || $intervalo_slide > 600) {
            echo "<script>alert('O intervalo geral deve ser entre 1 e 600 segundos.');</script>";
            exit;
        }
    } else {
        $intervalo_slide = 5; // Definir valor padrão
    }

    // Verificar se já existe uma apresentação com o mesmo título
    $sqlCheckTitle = "SELECT id FROM presentations WHERE title = ?";
    $stmtCheckTitle = $conexao->prepare($sqlCheckTitle);
    $stmtCheckTitle->bind_param("s", $title);
    $stmtCheckTitle->execute();
    $stmtCheckTitle->store_result();

    if ($stmtCheckTitle->num_rows > 0) {
        // Se já existe o título, exibe alerta e não prossegue com o upload
        echo "<script>alert('Título de apresentação já existe. Por favor, altere o título para continuar.');</script>";
        echo "<script>window.location.href = 'novaapresentacao.php';</script>"; // Redireciona para a página onde o título pode ser alterado
        exit; // Impede que o código continue a execução
    }

    // Verificar se os arquivos foram enviados
    if (isset($files) && $files['error'][0] == 0) {
        // Definir o diretório base onde os arquivos serão salvos
        $baseUploadDirectory = 'uploads/';

        // Sanitizar o título da apresentação para usá-lo como nome de diretório
        $sanitizedTitle = preg_replace('/[^a-zA-Z0-9_]/', '_', $title); // Substitui espaços e caracteres especiais por underscores
        $uploadDirectory = $baseUploadDirectory . $sanitizedTitle . '/'; // Diretório específico para a apresentação

        // Criar o diretório com o nome do título se não existir
        if (!file_exists($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true); // Cria o diretório com permissões
        }

        // 1. Inserir o título da apresentação na base de dados
        $sql = "INSERT INTO presentations (title) VALUES (?)";
        $stmt = $conexao->prepare($sql);
        $stmt->bind_param("s", $title);

        if ($stmt->execute()) {
            // Pega o ID da apresentação inserida
            $presentation_id = $conexao->insert_id;

            // 2. Loop para salvar os arquivos associados a esta apresentação
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] == 0) {
                    $fileName = basename($files['name'][$i]);
                    $filePath = $uploadDirectory . $fileName;
                    $fileInterval = isset($intervalos[$i]) ? (int)$intervalos[$i] : $intervalo_slide; // Pega o intervalo específico para o arquivo
                    $fileOrder = $i + 1; // A ordem do arquivo será a posição no array (começa de 1)

                    // Mover o arquivo para o diretório de uploads
                    if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
                        // Inserir o arquivo na tabela `presentation_files` com o intervalo específico e a ordem
                        $file_sql = "INSERT INTO presentation_files (presentation_id, file_path, intervalo_slide, `order_number`) VALUES (?, ?, ?, ?)";
                        $file_stmt = $conexao->prepare($file_sql);
                        $file_stmt->bind_param("isii", $presentation_id, $filePath, $fileInterval, $fileOrder);

                        if (!$file_stmt->execute()) {
                            echo "<script>alert('Erro ao adicionar o arquivo!');</script>";
                        }
                    } else {
                        echo "<script>alert('Falha no upload do arquivo.');</script>";
                    }
                }
            }

            echo "<script>alert('Apresentação e arquivos adicionados com sucesso!');</script>";
            echo "<script>window.location.href = 'novaapresentacao.php';</script>"; // Redireciona após sucesso

        } else {
            echo "<script>alert('Erro ao adicionar a apresentação!');</script>";
        }
    } else {
        echo "<script>alert('Por favor, selecione ao menos um arquivo.');</script>";
    }
}
?>



<!-- HTML / CSS -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Apresentação</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

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

        /* Defina a largura máxima para o dropdown */
        #navbarDropdownMenuLink {
            max-width: 200px;
            /* Defina o valor que achar adequado */
            width: 100px;
            /* Garante que o conteúdo se ajuste automaticamente */
        }
    </style>

</head>

<body class="bg-light">
    <!--NAV PRINCIPAL -->
    <nav class="navbar navbar-expand-sm navbar-dark shadow shadow-sm" id="qs-main-header" style="background-size: 100% 100%;background-image:url('imagens/backgroundNav.jpg');">
        <div class="container">
            <a id="qs-modules-link" href="home.php" class="navbar-brand" onclick="$('#qs-modules').show(); $('#qs-modules-link').addClass('active');"><img src=""></a>
            <a href="home.php" class="navbar-brand">Painel Administrativo</a>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="navbarDropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <?php echo $username; ?>
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

        </div>
    </nav>

    <!--BARRA DE LINKS - PAGINAS -->
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

    <!-- CRIAÇÃO DE NOVA APRESENTAÇÕES -->
    <form action="novaapresentacao.php" method="POST" id="uploadForm" enctype="multipart/form-data" style=" padding: 20px; border-radius: 3px; margin-top: 20px; box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);">
        <h3 class="navbar-brand">Criar Nova Apresentação de Slides</h3>

        <div class="mb-3">
            <label for="title" class="form-label">Título da Apresentação:</label>
            <input type="text" id="title" name="title" class="form-control custom-input w-50" required />
        </div>


        <div class="mb-3" id="fileUploadContainer">
            <label for="file" class="form-label me-1">Upload de arquivos /</label>
            <label for="file" class="form-label">Add intervalo apresentação</label>

            <div class="file-upload-container">
                <div class="file-upload-item" style="display: flex; gap: 10px;">
                    <input type="file" name="files[]" class="form-control form-control" required style="width: 250px;" />

                    <input type="number" name="intervalos[]" class="form-control form-control" placeholder="Segundos" required min="1" max="600" style="width: 110px;" />
                </div>
            </div>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-secondary" id="addFileButton">+ Adicionar mais arquivos</button>
            <br><br>
            <button type="submit" class="btn btn-primary">Criar Apresentação</button>
        </div>
    </form>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>



    <!-- JAVASCRIPT -->
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

        // Adicionar novos campos de upload
        document.getElementById('addFileButton').addEventListener('click', function() {
            const fileUploadContainer = document.getElementById('fileUploadContainer');

            const newFileUploadItem = document.createElement('div');
            newFileUploadItem.classList.add('file-upload-item');
            newFileUploadItem.style.display = 'flex'; // Usar Flexbox para alinhar os itens lado a lado
            newFileUploadItem.style.gap = '10px'; // Espaçamento entre os campos

            // Criar campo para o arquivo
            const fileInput = document.createElement('input');
            fileInput.type = 'file';
            fileInput.name = 'files[]'; // Mudar para um array para permitir vários arquivos
            fileInput.classList.add('form-control');
            fileInput.required = true;
            fileInput.style.width = '250px'; // Definir a largura do campo de arquivo

            // Criar campo para intervalo do arquivo
            const intervalInput = document.createElement('input');
            intervalInput.type = 'number';
            intervalInput.name = 'intervalos[]';
            intervalInput.classList.add('form-control');
            intervalInput.placeholder = 'Intervalo';
            intervalInput.required = true;
            intervalInput.min = 1;
            intervalInput.style.width = '110px'; // Definir a largura do campo de intervalo

            // Criar o botão de "X" para excluir o campo
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.classList.add('btn', 'btn-danger', 'btn-sm');
            closeButton.textContent = 'X';

            // Adicionar um evento de clique para fechar o campo
            closeButton.addEventListener('click', function() {
                newFileUploadItem.remove(); // Remove o item do DOM
            });

            // Adicionar os campos de arquivo, intervalo e o botão de "X" ao item
            newFileUploadItem.appendChild(fileInput);
            newFileUploadItem.appendChild(intervalInput);
            newFileUploadItem.appendChild(closeButton);

            // Adicionar o novo item ao container
            fileUploadContainer.querySelector('.file-upload-container').appendChild(newFileUploadItem);
        });
    </script>

</body>

</html>
