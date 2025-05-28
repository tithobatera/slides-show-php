<?php
session_start();
include 'config.php'; // Arquivo de configuração do banco de dados
?>

<?php
// Lógica PHP para obter o nome do usuário logado
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Usuário desconhecido'; // Caso o nome não esteja disponível
?>



<!-- HTML / CSS -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- CSS -->
  <style>
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

    .logo a div {
      transition: transform 1s ease;
      /* A transição suave de 1 segundo */
    }

    .logo a:hover div {
      transform: scale(1.1);
      /* Aumenta o tamanho da logo no hover */
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
  <nav class="navbar navbar-expand-sm navbar-dark shadow shadow-sm" id="qs-main-header" style="background-size: 100% 100%;background-image:url('');">
  <div class="container">
      <a id="qs-modules-link" href="home.php" class="navbar-brand" onclick="$('#qs-modules').show(); $('#qs-modules-link').addClass('active');"><img src="""></a>
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

  <!--SESSÃO BANNER PAGINA INICIAL (HOME) -->
  <section class="banner">
    <div style="background-image:url('imagens/bg_blue.jpg'); 
                background-size: cover; 
                background-position: center; 
                background-repeat: no-repeat; 
                height: 80vh; 
                padding-top: 10px;
                position: relative;">
    </div>

    <div class="logo" style="position: absolute; top: 50%; left: 49%; transform: translate(-50%, -50%);">
      <!-- Adicionando a tag <a> com o link desejado -->
      <a href="" target="_blank"
        style="display: block; height: 150px; width: 150px;">
        <div style="background-image:url(''); 
                        background-size: contain; 
                        background-position: center; 
                        background-repeat: no-repeat; 
                        height: 150px; 
                        width: 150px;
                        cursor: pointer;">
        </div>
      </a>
    </div>
  </section>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
