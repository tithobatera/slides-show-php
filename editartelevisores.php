<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados

// Buscar o televisor a ser editado
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM televisores WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $televisor = $result->fetch_assoc();
} else {
    echo "Televisor não encontrado.";
    exit;
}

// Atualizar o televisor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $nome = $_POST['nome'];
    $url = $_POST['url'];
    $ip = $_POST['ip'];

    // Verificar se já existe um televisor com o mesmo nome
    $check_nome_sql = "SELECT id FROM televisores WHERE nome = ? AND id != ?";
    $stmt_check = $conexao->prepare($check_nome_sql);
    $stmt_check->bind_param("si", $nome, $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Se o nome já existe, exibir um alerta
        echo "<script>alert('Já existe um televisor com esse nome!');</script>";
    } else {
        // Caso o nome não exista, fazer a atualização
        // Obter o nome antigo do televisor
        $get_nome_sql = "SELECT nome FROM televisores WHERE id = ?";
        $stmt_get_nome = $conexao->prepare($get_nome_sql);
        $stmt_get_nome->bind_param("i", $id);
        $stmt_get_nome->execute();
        $result_get_nome = $stmt_get_nome->get_result();
        $old_nome = $result_get_nome->fetch_assoc()['nome'];

        // Atualizar o banco de dados com o novo nome
        $update_sql = "UPDATE televisores SET nome = ?, url = ?, ip = ? WHERE id = ?";
        $stmt = $conexao->prepare($update_sql);
        $stmt->bind_param("sssi", $nome, $url, $ip, $id);

        if ($stmt->execute()) {
            // Verificar se o nome do arquivo também precisa ser atualizado
            $old_filename = strtolower(str_replace(' ', '_', $old_nome)) . '.php';
            $new_filename = strtolower(str_replace(' ', '_', $nome)) . '.php';

            // Verificar se o nome do arquivo foi alterado e renomear o arquivo no diretório
            if ($old_filename != $new_filename) {
                $old_file_path = 'televisores/' . $old_filename;
                $new_file_path = 'televisores/' . $new_filename;

                // Verificar se o arquivo antigo existe
                if (file_exists($old_file_path)) {
                    if (rename($old_file_path, $new_file_path)) {
                        echo "<script>alert('Televisor atualizado e arquivo renomeado com sucesso!');</script>";
                    } else {
                        echo "<script>alert('Erro ao renomear o arquivo!');</script>";
                    }
                }
            }

            // Exibir alerta de sucesso e redirecionar para a página de televisores
            echo "<script>window.location.href = 'televisores.php';</script>";
        } else {
            // Se ocorrer algum erro ao atualizar, exibir um alerta de erro
            echo "<script>alert('Erro ao atualizar o televisor: " . $conexao->error . "');</script>";
        }
    }
}

?>

<?php // Lógica PHP para obter o nome do usuário logado
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário desconhecido'; // Caso o nome não esteja disponível
?>




<!-- HTML / CSS -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Televisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS -->
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





    <!-- Formulário para editar o televisor -->
    <div class="row text-left" style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 20px;">
        <div style=" padding: 20px; border-radius: 8px; margin-top: 20px; width: 100%; height: 390px;">
            <h3 class="navbar-brand">Editar televisor</h3>



            <form action="editartelevisores.php?id=<?php echo $televisor['id']; ?>" method="POST">
                <div class="mb-3">
                    <label for="nome" class="form-label">Nome do Televisor:</label>
                    <input type="text" id="nome" name="nome" class="form-control" value="<?php echo $televisor['nome']; ?>" oninput="removerEspacos(); gerarUrl();" required>
                </div>

                <div class="mb-3">
                    <label for="url" class="form-label">URL do Televisor:</label>
                    <input type="text" id="url" name="url" class="form-control" value="<?php echo $televisor['url']; ?>" required readonly>
                </div>

                <div class="mb-3">
                    <label for="ip" class="form-label">IP do Televisor:</label>
                    <input type="text" id="ip" name="ip" class="form-control" value="<?php echo $televisor['ip']; ?>" required>
                </div>

                <button type="submit" name="update" class="btn btn-primary">Atualizar Televisor</button>
            </form>

        </div>
    </div>


    <!-- Scripts do Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>


    <!-- JAVASCRIPT-->
    <script>
        // Função para remover espaços enquanto o usuário digita
        function removerEspacos() {
            var nomeInput = document.getElementById("nome");
            nomeInput.value = nomeInput.value.replace(/\s+/g, ''); // Remove todos os espaços
        }
    </script>

    <script>
        // Função para gerar a URL automaticamente com base no nome do televisor
        function gerarUrl() {
            const baseUrl = ""; // URL base
            const nomeTelevisor = document.getElementById("nome").value; // Obtém o valor do campo de nome
            if (nomeTelevisor) { // Verifica se o nome do televisor foi inserido
                const urlGerada = baseUrl + encodeURIComponent(nomeTelevisor); // Gera a URL com o nome
                document.getElementById('url').value = urlGerada; // Preenche o campo da URL
            } else {
                document.getElementById('url').value = ""; // Limpa a URL se o nome estiver vazio
            }
        }
    </script>

</body>

</html>
