<!--PHP-->
<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados
?>

<?php // Lógica PHP para obter o nome do usuário logado
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário desconhecido'; // Caso o nome não esteja disponível
?>

<?php // Função para remover o diretório e todos os seus arquivos
function deleteDirectory($dirPath)
{
    // Verifica se o diretório existe
    if (is_dir($dirPath)) {
        // Lista todos os arquivos e subdiretórios do diretório
        $files = array_diff(scandir($dirPath), array('.', '..'));  // Ignora . e ..

        foreach ($files as $file) {
            $filePath = $dirPath . DIRECTORY_SEPARATOR . $file;

            if (is_dir($filePath)) {
                // Se for um diretório, chama recursivamente para deletar seus conteúdos
                deleteDirectory($filePath);
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

// Função de exclusão de apresentação e arquivos
if (isset($_GET['delete'])) {
    $presentation_id = $_GET['delete'];

    // Obter o título da apresentação antes de deletá-la (para garantir que o título estará disponível)
    $title_sql = "SELECT title FROM presentations WHERE id = ?";
    $title_stmt = $conexao->prepare($title_sql);
    $title_stmt->bind_param("i", $presentation_id);
    $title_stmt->execute();
    $title_result = $title_stmt->get_result();

    // Verificar se a consulta retornou o título da apresentação
    if ($title_row = $title_result->fetch_assoc()) {
        $presentation_title = $title_row['title'];  // Título da apresentação
        $directory_path = "uploads/" . preg_replace('/[^a-zA-Z0-9_]/', '_', $presentation_title);  // Caminho do diretório 'uploads/titulo_da_apresentacao'

        // Excluir os arquivos relacionados na tabela presentation_files
        $file_sql = "SELECT file_path FROM presentation_files WHERE presentation_id = ?";
        $file_stmt = $conexao->prepare($file_sql);
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

        // Agora excluir os registros de arquivos na tabela presentation_files
        $delete_files_sql = "DELETE FROM presentation_files WHERE presentation_id = ?";
        $delete_files_stmt = $conexao->prepare($delete_files_sql);
        $delete_files_stmt->bind_param("i", $presentation_id);
        $delete_files_stmt->execute();

        // Excluir a apresentação da tabela presentations
        $delete_presentation_sql = "DELETE FROM presentations WHERE id = ?";
        $delete_presentation_stmt = $conexao->prepare($delete_presentation_sql);
        $delete_presentation_stmt->bind_param("i", $presentation_id);
        if ($delete_presentation_stmt->execute()) {
            // Remover o diretório, se existir e não estiver vazio
            if (is_dir($directory_path)) {
                // Função recursiva para deletar todos os arquivos dentro do diretório
                deleteDirectory($directory_path);
            }

            echo "<script>alert('Apresentação excluída com sucesso!');</script>";
            echo "<script>window.location.href = 'apresentacoes.php';</script>";  // Redireciona para a mesma página
        } else {
            echo "<script>alert('Erro ao excluir a apresentação.');</script>";
        }
    } else {
        echo "<script>alert('Título da apresentação não encontrado.');</script>";
    }
}
?>

<?php //PHP DA SESSÃO APRESENTAÇÕES EXISTENTES // Número de apresentações por página
$items_per_page = 6;

// Verifica se a variável 'page' foi definida, se não, assume a página 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page; // Deslocamento para a consulta

// Determinar a ordenação
$sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'title_asc';
switch ($sort_order) {
    case 'title_asc':
        $order_by = 'title ASC';
        break;
    case 'title_desc':
        $order_by = 'title DESC';
        break;
    case 'uploaded_at_asc':
        $order_by = 'uploaded_at ASC';
        break;
    case 'uploaded_at_desc':
        $order_by = 'uploaded_at DESC';
        break;
    default:
        $order_by = 'title ASC';
}

// Recupera o termo de busca
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Consulta as apresentações com filtro de pesquisa
$sql = "SELECT * FROM presentations WHERE title LIKE ? ORDER BY $order_by LIMIT $offset, $items_per_page";
$stmt = $conexao->prepare($sql);
$searchParam = '%' . $searchQuery . '%'; // Prepara a pesquisa com wildcard
$stmt->bind_param("s", $searchParam);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se o resultado foi obtido corretamente
$presentations = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $presentations[] = $row;
    }
}

// Consultar o número total de apresentações
$sql_total = "SELECT COUNT(*) as total FROM presentations";
$result_total = $conexao->query($sql_total);
$row_total = $result_total->fetch_assoc();
$total_items = $row_total['total']; // Total de apresentações

// Calcular o número total de páginas
$total_pages = ceil($total_items / $items_per_page);
?>


<!-- HTML / CSS -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apresentações</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
    <style>
        html,
        body {
            width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            overflow: auto;
            /* Previne scroll horizontal */
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a {
            margin: 0 5px;
            padding: 8px 12px;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
        }

        .pagination .current-page {
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
        }

        a {
            text-decoration: none;
            list-style: none;
        }

        /* Defina a largura máxima para o dropdown */
        #navbarDropdownMenuLink {
            max-width: 200px;
            /* Defina o valor que achar adequado */
            width: 100px;
            /* Garante que o conteúdo se ajuste automaticamente */

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

    <div style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 20px;">
        <div style=" padding: 20px; border-radius: 8px; margin-top: 20px; width: 100%; height: 600px;">

            <h3 class="navbar-brand">Apresentações Existentes</h3>
            <!-- Filtros de Ordenação e Pesquisa -->
            <div class="mb-2">
                <div class="row">
                    <!-- Campo de Busca -->
                    <div class="col-md-3 col-12 mb-3">
                        <div class="presentation-item">
                            <div class="list-group">
                                <label for="searchInput" class="form-label">Procurar Apresentações</label>
                                <input type="text" id="searchInput" onkeyup="myFunction()" class="form-control w-100" placeholder="Pesquisar pelo titulo...">
                            </div>
                        </div>
                    </div>

                    <!-- Campo de Ordenação -->
                    <div class="col-md-3 col-12 mb-3">
                        <div class="mb-3">
                            <label for="sortOrder" class="form-label">Ordenar por</label>
                            <select id="sortOrder" class="form-select w-100" onchange="this.form.submit()">
                                <option value="title_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'title_asc' ? 'selected' : ''; ?>>Ordem Alfabética (A-Z)</option>
                                <option value="title_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'title_desc' ? 'selected' : ''; ?>>Ordem Alfabética (Z-A)</option>
                                <option value="uploaded_at_asc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'uploaded_at_asc' ? 'selected' : ''; ?>>Data de Criação (Mais Antiga)</option>
                                <option value="uploaded_at_desc" <?php echo isset($_GET['sort']) && $_GET['sort'] == 'uploaded_at_desc' ? 'selected' : ''; ?>>Data de Criação (Mais Recente)</option>
                            </select>
                        </div>

                    </div>

                </div>


                <div class="col-md-6 col-12 mb-2">
                    <div class="mb-3">
                        <button id="selectAllButton" class="btn btn-primary " onclick="selectAll()">Marcar/Desmarcar todas apresentações </button>
                        <button id="deleteSelectedButton" class="btn btn-danger " onclick="deleteSelected()">Excluir Selecionados</button>
                    </div>
                </div>



                <div class="list-group" id="presentationList"">
                    <?php
                    // Número de apresentações por página
                    $items_per_page = 5;

                    // Verifica se a variável 'page' foi definida, se não, assume a página 1
                    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $offset = ($page - 1) * $items_per_page; // Deslocamento para a consulta

                    // Determinar a ordenação
                    $sort_order = isset($_GET['sort']) ? $_GET['sort'] : 'title_asc';
                    switch ($sort_order) {
                        case 'title_asc':
                            $order_by = 'title ASC';
                            break;
                        case 'title_desc':
                            $order_by = 'title DESC';
                            break;
                        case 'uploaded_at_asc':
                            $order_by = 'uploaded_at ASC';
                            break;
                        case 'uploaded_at_desc':
                            $order_by = 'uploaded_at DESC';
                            break;
                        default:
                            $order_by = 'title ASC';
                    }


                    // Recupera o termo de busca
                    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

                    // Consulta as apresentações com filtro de pesquisa
                    $sql = "SELECT * FROM presentations WHERE title LIKE ? ORDER BY $order_by LIMIT $offset, $items_per_page";
                    $stmt = $conexao->prepare($sql);
                    $searchParam = '%' . $searchQuery . '%'; // Prepara a pesquisa com wildcard
                    $stmt->bind_param("s", $searchParam);
                    $stmt->execute();
                    $result = $stmt->get_result();


                    // Verifica se o resultado foi obtido corretamente
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">';

                            // Adicionando o checkbox para cada apresentação
                            echo '<input type="checkbox" class="presentation-checkbox" data-id="' . $row['id'] . '" style="width: 20px; height: 20px; margin-right: 10px;" />';

                            echo '<div class="col-12 col-md-6 mb-2 mb-md-0"><a href="editar.php?id=' . $row['id'] . '" class="text-decoration-none">' . $row['title'] . '</a></div>';
                            echo '<div class="col-12 col-md-3 text-end mb-2 mb-md-0"><span class="text-muted">' . $row['uploaded_at'] . '</span></div>';

                            // Botões para ação
                            echo '<div class="d-flex flex-wrap justify-content-between align-items-center">';
                            echo '<div class="btn-group mb-2 mb-md-0">';
                            echo '<a href="selecionar_televisor.php?presentation_id=' . $row['id'] . '" class="btn btn-info btn-sm">Apresentar</a>';
                            echo '</div>';

                            echo '<div class="btn-group mb-2 mb-md-0">';
                            echo '<a href="editar.php?id=' . $row['id'] . '" class="btn btn-primary btn-sm ms-2">Editar</a>';
                            echo '</div>';

                            echo '<div class="btn-group mb-2 mb-md-0">';
                            echo '<a href="?delete=' . $row['id'] . '" class="btn btn-danger btn-sm ms-2" onclick="return confirm(\'Tem certeza que deseja excluir esta apresentação e seus arquivos?\')">Excluir</a>';
                            echo '</div>';

                            echo '</div>'; // d-flex
                            echo '</div>'; // list-group-item
                        }
                    } else {
                        echo "<p>Nenhuma apresentação encontrada.</p>";
                    }

                    // Consultar o número total de apresentações
                    $sql_total = "SELECT COUNT(*) as total FROM presentations";
                    $result_total = $conexao->query($sql_total);
                    $row_total = $result_total->fetch_assoc();
                    $total_items = $row_total['total']; // Total de apresentações

                    // Calcular o número total de páginas
                    $total_pages = ceil($total_items / $items_per_page);

                    // Exibir links de paginação
                    echo '<div class="pagination mt-3">';
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i == $page) {
                            echo '<span class="current-page">' . $i . '</span>';
                        } else {
                            echo '<a href="?page=' . $i . '">' . $i . '</a>';
                        }
                    }
                    echo '</div>';

                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JAVASCRIPT -->
    <script>
        //BOTAO MARCAR E DESMARCAR TODAS APRESENTAÇÕES EXISTENTES
        // Função para selecionar/desmarcar todos os checkboxes
        function selectAll() {
            // Obtém todos os checkboxes de apresentação
            var checkboxes = document.querySelectorAll('.presentation-checkbox');

            // Verifica se todos os checkboxes estão marcados
            var allChecked = true;
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.checked) {
                    allChecked = false; // Se encontrar um checkbox desmarcado, define allChecked como falso
                }
            });

            // Se todos estiverem marcados, desmarque todos, senão marque todos
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });
        }
    </script>

    <script>
        //FUNÇÃO BOTAO EXCLUIR SELECIONADAS - MARCAR E DESMARCAR TODAS APRESENTAÇÕES
        function deleteSelected() {
            var checkboxes = document.querySelectorAll('.presentation-checkbox:checked');
            var ids = [];

            // Coleta os IDs das apresentações selecionadas
            checkboxes.forEach(function(checkbox) {
                ids.push(checkbox.getAttribute('data-id'));
            });
            if (ids.length > 0) {
                if (confirm("Tem certeza que deseja excluir as apresentações selecionadas?")) {
                    // Envia os IDs para o servidor via AJAX
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "delete_presentations.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            alert("Apresentações excluídas com sucesso!");
                            location.reload(); // Atualiza a página após exclusão
                        }
                    };
                    xhr.send("ids=" + JSON.stringify(ids)); // Envia os IDs selecionados
                }
            } else {
                alert("Nenhuma apresentação selecionada.");
            }
        }
    </script>

    <script>
        //FUNÇÃO DA BARRA DE PESQUISA - PESQUISA APRESENTAÇÕES EM TEXTO
        // Função da barra de pesquisa
        function myFunction() {
            var input = document.getElementById('searchInput');
            var searchQuery = input.value; // Pegando o valor digitado

            // Se o campo de pesquisa estiver vazio, redireciona para a página sem o parâmetro de busca
            if (searchQuery === "") {
                window.location.href = window.location.pathname; // Redireciona para a página atual sem parâmetros
            } else {
                // Fazendo a requisição AJAX se houver um valor digitado
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'search_presentations.php?search=' + searchQuery, true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Atualiza a lista com a resposta do servidor
                        document.getElementById('presentationList').innerHTML = xhr.responseText;
                    }
                };
                xhr.send();
            }
        }
    </script>

    <script>
        //FUNÇÃO BOTAO -  ORDENAR APRESENTAÇÕES
        document.getElementById('sortOrder').addEventListener('change', function() {
            window.location.href = '?sort=' + this.value;
        });
    </script>

</body>

</html>
