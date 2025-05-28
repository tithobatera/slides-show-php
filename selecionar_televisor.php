<?php
session_start();

// Inclui o arquivo de configuração para a conexão com o banco de dados
include 'config.php';



// Captura o ID da apresentação, que será passado pela URL
$presentation_id = isset($_GET['presentation_id']) ? (int)$_GET['presentation_id'] : null;
$presentation_title = '';

// Verifica se um ID de apresentação foi passado
if ($presentation_id) {
    try {
        // Conectar ao banco de dados
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para buscar o título da apresentação com base no ID
        $sql = "SELECT title FROM presentations WHERE id = :presentation_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':presentation_id', $presentation_id, PDO::PARAM_INT);
        $stmt->execute();

        // Verifica se encontrou o título da apresentação
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $presentation_title = $row['title']; // Armazena o título da apresentação
        } else {
            $presentation_title = 'Apresentação não encontrada';
        }
    } catch (PDOException $e) {
        echo "Erro ao buscar o título da apresentação: " . $e->getMessage();
        exit();
    }
}

// Consulta para buscar todos os televisores
try {
    // Conectar ao banco de dados
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT id, nome, url FROM televisores";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // Recupera todos os televisores
    $televisores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar os televisores: " . $e->getMessage();
    exit();
}
?>

<?php // Verifica se um título de apresentação foi encontrado
$presentation_info = '';
if ($presentation_id) {
    $presentation_info = '<p><strong>Apresentação Selecionada:</strong> ' . htmlspecialchars($presentation_title) . '</p>';
}

// Prepara os televisores para exibição
$televisores_html = '';
if ($televisores && count($televisores) > 0) {
    foreach ($televisores as $televisor) {
        $televisores_html .= '<div class="list-group-item d-flex justify-content-between align-items-center">';
        $televisores_html .= '<input type="checkbox" name="televisor_id[]" value="' . $televisor['id'] . '" id="televisor_' . $televisor['id'] . '" required>';
        $televisores_html .= '<label for="televisor_' . $televisor['id'] . '">' . $televisor['nome'] . '</label>';
        $televisores_html .= '</div>';
    }
} else {
    $televisores_html = '<p>Nenhum televisor encontrado.</p>';
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
    <title>Seleção Televisores</title>
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

    <!-- TELEVISORES CADASTRADOS -->
    <div class="container mt-5">
        <h2 class="navbar-brand">Selecione o Televisor para Apresentação</h2>

        <?php
        // Verifica se um título de apresentação foi encontrado
        if ($presentation_id) {
            echo '<p><strong>Apresentação Selecionada:</strong> ' . htmlspecialchars($presentation_title) . '</p>';
        }
        ?>
        <button id="selectAllButton" class="btn btn-primary " onclick="selectAll()">Marcar/Desmarcar todas apresentações </button>

        <!-- Formulário de seleção -->
        <form action="#" method="get" target="#"> <!-- ATIVAR PAGINA ACTION="SHOW_SLIDES.PHP" E TARGET="_BLANK"  -->

            <div class="list-group">
                <?php echo $televisores_html; ?>
            </div>
        </form>
        <!-- Passa o ID da apresentação como campo oculto para show_slides.php -->
        <input type="hidden" name="presentation_id" value="<?php echo $presentation_id; ?>">

        <button type="button" class="btn btn-primary mt-3" onclick="iniciarApresentacao()">Iniciar Apresentação</button>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>



    <!-- JAVASCRIPT -->
    <script>
        // Função para iniciar a apresentação e emitir o alerta
        function iniciarApresentacao() {
            // Exibe o alerta de que a apresentação foi iniciada
            alert("Apresentação Iniciada");

            // Aqui você pode colocar o código para iniciar a apresentação, se necessário
            // Por exemplo, iniciar o primeiro slide:
            showSlide(currentSlideIndex); // Se você tiver essa função que começa a apresentação
        }
    </script>

    <script>
        function iniciarApresentacao() {
            var checkboxes = document.querySelectorAll('input[name="televisor_id[]"]:checked');
            if (checkboxes.length > 0) {
                checkboxes.forEach(function(checkbox, index) {
                    var televisorId = checkbox.value;

                    // Passo 1: Enviar para a página 'show_slides.php' (abrir nova aba com a apresentação)
                    var form = document.createElement('form');
                    form.method = 'GET';
                    //form.action = 'show_slides.php'; // ATIVAR PAGINA ACTION="SHOW_SLIDES.PHP"  

                    var televisorInput = document.createElement('input');
                    televisorInput.type = 'hidden';
                    televisorInput.name = 'televisor_id';
                    televisorInput.value = televisorId;
                    form.appendChild(televisorInput);

                    var presentationInput = document.createElement('input');
                    presentationInput.type = 'hidden';
                    presentationInput.name = 'presentation_id';
                    presentationInput.value = '<?php echo $presentation_id; ?>'; // Passa o ID da apresentação via PHP
                    form.appendChild(presentationInput);

                    // Definir o alvo do formulário como '_blank' para abrir uma nova aba
                    //form.target = '_blank'; // ATIVAR PAGINA ACTION="SHOW_SLIDES.PHP" E TARGET="_BLANK"  -->
                    document.body.appendChild(form);

                    // Para garantir que as abas sejam abertas sem bloqueio, adicionar um pequeno atraso
                    setTimeout(function() {
                        form.submit();
                        document.body.removeChild(form);
                    }, 100 * index); // Atraso para abrir uma nova aba para cada televisor


                    console.log('Presentation ID:', '<?php echo $presentation_id; ?>'); // Verifique se o valor está correto
                    console.log('Televisor ID:', televisorId); // Verifique o ID do televisor


                    // Enviar a requisição AJAX para atualizar o televisor
                    $.ajax({
                        url: 'update_televisores.php',
                        type: 'POST',
                        data: {
                            presentation_id: '<?php echo $presentation_id; ?>',
                            televisor_id: televisorId
                        },
                        success: function(response) {
                            var result = JSON.parse(response);
                            if (result.status === 'success') {
                                console.log('Televisor atualizado com sucesso.');
                            } else {
                                console.log('Erro ao atualizar televisor: ' + result.message);
                            }
                        },
                        error: function() {
                            console.log('Erro de comunicação com o servidor para atualizar o televisor.');
                        }
                    });

                });
            } else {
                alert('Por favor, selecione pelo menos um televisor.');
            }
        }
    </script>



    <script>
        // Função para selecionar/desmarcar todos os checkboxes
        function selectAll() {
            var checkboxes = document.querySelectorAll('input[name="televisor_id[]"]');
            var allChecked = true;
            checkboxes.forEach(function(checkbox) {
                if (!checkbox.checked) {
                    allChecked = false;
                }
            });

            checkboxes.forEach(function(checkbox) {
                checkbox.checked = !allChecked;
            });
        }
    </script>

</body>

</html>