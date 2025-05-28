<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados
?>

<?php // Lógica PHP para obter o nome do usuário logado
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário desconhecido'; // Caso o nome não esteja disponível
?>

<?php // Processar o formulário de criação de Televisor
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create'])) {
    // Obter os dados do formulário
    $nome = $_POST['nome']; // Nome do Televisor
    $url = $_POST['url']; // URL do Televisor
    $ip = $_POST['ip']; // IP do Televisor

    // Verificar se os dados foram fornecidos corretamente (não vazio)
    if (!empty($nome) && !empty($url) && !empty($ip)) {
        // Inserir os dados do televisor na base de dados
        $sql = "INSERT INTO televisores (nome, url, ip) VALUES (?, ?, ?)";
        $stmt = $conexao->prepare($sql);

        if ($stmt === false) {
            die('Erro na preparação da consulta de inserção: ' . $conexao->error);
        }

        $stmt->bind_param("sss", $nome, $url, $ip);

        if ($stmt->execute()) {
            // Obter o ID do televisor inserido
            $televisor_id = $stmt->insert_id;

            // Criar a URL dinâmica para o televisor
            $televisor_page = 'televisores/' . strtolower(str_replace(' ', '_', $nome)) . '.php'; // Converte o nome para um formato adequado de URL e coloca dentro da pasta

            // Criar a pasta 'televisores' caso não exista
            if (!file_exists('televisores')) {
                mkdir('televisores', 0777, true); // Cria a pasta com permissões adequadas
            }

            // Gerar o conteúdo da página PHP para o televisor
            $content = "<?php
            // Conectar ao banco de dados e buscar os dados do televisor
            include '../config.php'; // Corrigir o caminho para o arquivo de configuração, se necessário
        
            // Defina a variável com o valor do ID do televisor
            \$id_televisor = $televisor_id; // Aqui estamos colocando a variável PHP diretamente no código
        
            // Buscar o televisor com base no ID
            \$sql = 'SELECT * FROM televisores WHERE id = ?';
            \$stmt = \$conexao->prepare(\$sql);
            \$stmt->bind_param('i', \$id_televisor); // Agora passamos a variável \$id_televisor
            \$stmt->execute();
            \$result = \$stmt->get_result();
        
            if (\$result->num_rows > 0) {
                \$televisor = \$result->fetch_assoc();
                \$presentation_id = \$televisor['presentation_id']; // Supondo que exista um campo presentation_id
            } else {
                echo 'Televisor não encontrado.';
            }
        
                
            // Incluir a apresentação do televisor
            include '../load_presentation_televisores.php'; // Arquivo que carrega a apresentação de acordo com o presentation_id
            ?>";

            // Criar o arquivo PHP para o televisor na pasta 'televisores'
            file_put_contents($televisor_page, $content);

            // Se a inserção for bem-sucedida, exibir um alerta com JavaScript
            echo "<script>alert('Televisor cadastrado com sucesso!');</script>";
        } else {
            // Caso haja erro na inserção
            echo "<script>alert('Erro ao cadastrar televisor: " . $conexao->error . "');</script>";
        }
    } else {
        // Caso algum campo esteja vazio
        echo "<script>alert('Por favor, preencha todos os campos!');</script>";
    }
}
?>
<?php
// FUNÇÃO DO BOTÃO - EXCLUSÃO DE TELEVISOR
if (isset($_GET['delete_id'])) {
    $televisor_id = $_GET['delete_id'];

    // Buscar o nome do televisor para obter o nome do arquivo antes de excluir do banco de dados
    $sql = "SELECT nome FROM televisores WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $televisor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $televisor = $result->fetch_assoc();
        $televisor_nome = strtolower(str_replace(' ', '_', $televisor['nome'])) . '.php'; // Formatar o nome do arquivo

        // Definir o caminho completo para o arquivo na pasta 'televisores/'
        $file_path = 'televisores/' . $televisor_nome;

        // Verificar se o arquivo existe e excluí-lo
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                // Se o arquivo for excluído com sucesso
                echo "Arquivo excluído com sucesso!";
            } else {
                // Caso o arquivo não possa ser excluído
                echo "Erro ao excluir o arquivo!";
            }
        } else {
            echo "Arquivo não encontrado!";
        }

        // Agora, proceder com a exclusão do televisor no banco de dados
        $delete_sql = "DELETE FROM televisores WHERE id = ?";
        $delete_stmt = $conexao->prepare($delete_sql);

        if ($delete_stmt === false) {
            die('Erro na preparação da consulta de exclusão: ' . $conexao->error);
        }

        $delete_stmt->bind_param("i", $televisor_id);

        // Executa a exclusão do televisor
        if ($delete_stmt->execute()) {
            // Redireciona de volta para a página onde os televisores estão listados
            header('Location: televisores.php');
            exit;
        } else {
            // Caso a exclusão do televisor falhe
            echo "Erro ao excluir o televisor!";
        }
    } else {
        echo "Televisor não encontrado!";
    }
}
?>




<?php // Buscar todos os televisores
$televisores_sql = "SELECT * FROM televisores";
$televisores_result = $conexao->query($televisores_sql);

// Verificar se a consulta foi bem-sucedida
if ($televisores_result === false) {
    die("Erro na consulta: " . $conexao->error);
}

// Inicializar a variável de televisores como um array vazio
$televisores = [];

if ($televisores_result->num_rows > 0) {
    // Armazenar os dados dos televisores
    while ($row = $televisores_result->fetch_assoc()) {
        $televisores[] = $row;
    }
}
?>






<!--HTML / CSS-->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Televisores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!--CSS -->
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

    <style>
        /* Aumentar a largura do modal */
        .modal-dialog {
            max-width: 70%;
            /* Ajuste a largura do modal para 90% da tela */
        }

        /* Aumentar a altura do modal */
        .modal-content {
            height: 85vh;
            /* Ajuste a altura do modal para 80% da altura da tela */
        }
    </style>

    <style>
        .form-control,
        .input-group {
            width: 300px;
        }
    </style>

    <style>
        #presentationList {
            width: 540px;
        }
    </style>

    <style>
        /* Modal responsivo */
        .modal-dialog {
            max-width: 70%;
            /* 70% de largura da tela em dispositivos grandes */
            height: 90vh;
            /* 80% de altura da tela */
        }

        /* Ajustando para telas menores */
        @media (max-width: 768px) {
            .modal-dialog {
                max-width: 90%;
                /* 90% de largura da tela em dispositivos móveis */
                height: 70vh;
                /* 70% de altura da tela */
            }
        }

        /* Ajustando para telas ainda menores (smartphones em retrato) */
        @media (max-width: 576px) {
            .modal-dialog {
                max-width: 95%;
                /* 95% de largura da tela em smartphones pequenos */
                height: 60vh;
                /* 60% de altura da tela */
            }
        }
    </style>




</head>

<body class="bg-light">
    <!--NAV PRINCIPAL -->
    <nav class="navbar navbar-expand-sm navbar-dark shadow shadow-sm" id="qs-main-header" style="background-size: 100% 100%;background-image:url('imagens/backgroundNav.jpg');">
        <div class="container">
            <a id="qs-modules-link" href="home.php" class="navbar-brand" onclick="$('#qs-modules').show(); $('#qs-modules-link').addClass('active');"><img src="https://qunitserver.pt-fk.kabi.portugal.fresenius.de/apps/ep/assets/images/qunitsimbol_v4.3.png" width="30" height="30" class="d-inline-block align-top" alt=""></a>
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

    <!-- CRIAÇÃO DE TELEVISORES -->
    <!-- CRIAÇÃO DE TELEVISORES E TELEVISORES CADASTRADOS EM DUAS COLUNAS -->
    <div class="row">
        <!-- Coluna de Criação de Televisor -->
        <div class="col-12 col-md-5" style=" box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1)";> <!-- Alterei a classe para ser responsiva em dispositivos menores -->
            <div class="row text-left" style="padding: 20px;padding-left: 40px; ">
                <h3 class="navbar-brand">Adicionar novo televisor</h3>
                <div style="padding: 10px; border-radius: 8px; width: 100%; height: auto;"> <!-- Removi a altura fixa e overflow -->
                    <form action="televisores.php" method="POST">
                        <div class="mb-2">
                            <label for="nome" class="form-label">Nome do Televisor:</label>
                            <input type="text" class="form-control" id="nome" name="nome" required oninput="removerEspacos(); gerarUrl();">
                        </div>

                        <div class="mb-2">
                            <label for="url" class="form-label">URL do Televisor:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="url" name="url" readonly>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="ip" class="form-label">IP do Televisor:</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="ip" name="ip" required>
                                <button type="button" class="btn btn-secondary" id="gerar-ip">Gerar IP</button>
                            </div>
                        </div>

                        <button type="submit" name="create" class="btn btn-primary btn-block">Criar Televisor</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Coluna de Televisores Cadastrados -->
        <div class="col-12 col-md-7"> <!-- Alterei para que seja responsivo -->
            <div style="box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1); padding: 20px; padding-left:40px">
                <h3 class="navbar-brand">Televisores adicionados</h3>

                <!-- Buscar Televisor -->
                <div style="padding: 10px; border-radius: 8px; width: 100%; height: 310px; overflow:auto;">
                    <div class="list-group">
                        <label for="searchInput" class="form-label">Buscar televisor</label>
                        <input type="text" id="searchInput" onkeyup="myFunction()" class="form-control w-40" placeholder="Pesquisar pelo nome...">
                    </div>
                    <br>

                    <div id="presentationList">
                        <?php if (count($televisores) == 0 || $televisores[0] === null): ?>
                            <p>Nenhum televisor encontrado.</p>
                        <?php else: ?>
                            <ul class="list-group">
                                <?php foreach ($televisores as $row): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $row['nome']; ?>
                                        <div>
                                            <!-- Passando presentation_id para o modal -->
                                            <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                                data-toggle="modal"
                                                data-target="#previewModal"
                                                data-id="<?= $row['id']; ?>"
                                                data-presentation-id="<?= $row['presentation_id']; ?>">Visualizar Televisor</a>
                                            <a href="editartelevisores.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                                            <button onclick="confirmDelete(<?php echo $row['id']; ?>)" class="btn btn-danger btn-sm ms-2">Excluir</button>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <!-- Modal -->


    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <!-- O conteúdo da apresentação será carregado aqui via AJAX -->
                    <div id="previewContent"></div>

                    <!-- Novo campo para exibir o nome da apresentação -->
                    <p id="presentationName"></p> <!-- Aqui será exibido o nome da apresentação -->
                </div>
            </div>
        </div>
    </div>










    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Versão completa -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>



    <!-- JAVASCRIPT -->


    <script>
        $('#previewModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget); // Botão que ativou o modal
            var televisorId = button.data('id'); // ID do televisor
            var presentationId = button.data('presentation-id'); // ID da apresentação

            // Obter o contêiner de conteúdo do modal
            var modalBody = $(this).find('.modal-body #previewContent');

            // Limpar qualquer conteúdo anterior
            modalBody.empty();

            // Fazer a requisição AJAX para carregar os slides dessa apresentação
            $.ajax({
                url: 'load_presentation.php', // Novo arquivo PHP que carrega a apresentação
                method: 'GET',
                data: {
                    presentation_id: presentationId
                },
                success: function(response) {
                    modalBody.html(response); // Adiciona os slides no modal
                },

                error: function() {
                    modalBody.html('<p>Erro ao carregar a apresentação.</p>');
                }
            });
        });

        // Evento para recarregar a página quando o modal for fechado
        $('#previewModal').on('hidden.bs.modal', function() {
            location.reload(); // Recarrega a página
        });
    </script>



    <script>
        // Função campo de buscar televisor atraves da digitação 
        function myFunction() {
            var input = document.getElementById('searchInput');
            var searchQuery = input.value; // Pegando o valor digitado

            // Fazendo a requisição AJAX para a página de busca
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_televisores.php?search=' + searchQuery, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Atualiza a lista com a resposta do servidor
                    document.getElementById('presentationList').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>

    <script>
        // Função para remover espaços enquanto o usuário digita titulo de televisor

        function removerEspacos() {
            var nomeInput = document.getElementById("nome");
            nomeInput.value = nomeInput.value.replace(/\s+/g, ''); // Remove todos os espaços
        }
    </script>

    <script>
        // Função para confirmar a exclusão
        function confirmDelete(televisorId) {
            if (confirm("Tem certeza que deseja excluir este televisor?")) {
                // Passa o ID corretamente na URL para que o script de exclusão funcione
                window.location.href = "televisores.php?delete_id=" + televisorId;
            }
        }
    </script>

    <script>
        // GERADOR DE URL E IP  - // Função para gerar a URL automaticamente com base no nome do televisor
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
        // Função para gerar um IP aleatório
        function gerarIp() {
            const ipGerado = "192.168." + Math.floor(Math.random() * 256) + "." + Math.floor(Math.random() * 256);
            document.getElementById('ip').value = ipGerado;
        }
        // Adicionar o evento de clique no botão "Gerar IP"
        document.getElementById('gerar-ip').addEventListener('click', gerarIp);
    </script>

</body>

</html>