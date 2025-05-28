<?php
include 'config.php'; // Conexão com o banco de dados

// ID da apresentação e do televisor que serão carregados
$presentation_id = isset($_GET['presentation_id']) ? $_GET['presentation_id'] : null;

// Verificar se o ID da apresentação foi fornecido
if ($presentation_id === null) {
  die('Erro: ID da apresentação não encontrado.');
}

// Buscar dados da apresentação
$presentation_sql = "SELECT * FROM presentations WHERE id = ?";
$presentation_stmt = $conexao->prepare($presentation_sql);

if ($presentation_stmt === false) {
  die('Erro na preparação da consulta: ' . $conexao->error);
}

$presentation_stmt->bind_param("i", $presentation_id);
$presentation_stmt->execute();
$presentation_result = $presentation_stmt->get_result();

if ($presentation_result->num_rows > 0) {
  $presentation = $presentation_result->fetch_assoc();
} else {
  die('Apresentação não encontrada.');
}

// Buscar arquivos da apresentação (ordem alterada com base na `order_number`)
$files_sql = "SELECT * FROM presentation_files WHERE presentation_id = ? ORDER BY `order_number`";
$files_stmt = $conexao->prepare($files_sql);

if ($files_stmt === false) {
  die('Erro na preparação da consulta de arquivos: ' . $conexao->error);
}

$files_stmt->bind_param("i", $presentation_id);
$files_stmt->execute();
$files_result = $files_stmt->get_result();
$files = $files_result->fetch_all(MYSQLI_ASSOC); // Obtenha todos os resultados em um array associativo

// Buscar dados do televisor (por exemplo, a URL associada a esse televisor)
$televisor_sql = "SELECT * FROM televisores WHERE presentation_id = ?";
$televisor_stmt = $conexao->prepare($televisor_sql);

if ($televisor_stmt === false) {
  die('Erro na preparação da consulta do televisor: ' . $conexao->error);
}

$televisor_stmt->bind_param("i", $presentation_id);
$televisor_stmt->execute();
$televisor_result = $televisor_stmt->get_result();

if ($televisor_result->num_rows > 0) {
  $televisor = $televisor_result->fetch_assoc(); // Obter o televisor
  $televisor_url = $televisor['url']; // URL completa do televisor
  $televisor_name = $televisor['nome']; // Atribui o nome do televisor à variável

} else {
  die('Televisor não encontrado.');
}

?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Apresentação: <?php echo htmlspecialchars($presentation['title']); ?></title>

  <!-- CSS -->
  <style>
    /* Garantir que o HTML e o corpo ocupem 100% da tela */
    html,
    body {
      margin: 0;
      padding: 0;
      height: 100%;
      overflow: hidden;
      display: flex;
      justify-content: center;
      align-items: center;
      position: relative;
    }

    /* A classe geral para slides (imagens e vídeos) */
    .slideShow {
      width: 100vw;
      height: 100vh;
      object-fit: cover;
    }

    /* Caso o slide seja um vídeo */
    .slideShow.video {
      object-fit: cover;
    }

    /* Estilo para o título da apresentação */
    .presentation-title {
      font-family: Arial, Helvetica, sans-serif;
      position: absolute;
      top: 10px;
      /* Distância do topo */
      left: 10px;
      /* Distância da esquerda */
      font-size: 30px;
      font-weight: bold;
      color: white;
      z-index: 10;
      background-color: rgba(0, 0, 0, 0.5);
      /* Fundo semitransparente para o texto */
      padding: 5px 10px;
      border-radius: 5px;
    }

    /* Estilo para o nome do televisor */
    .tv-name {
      font-family: Arial, Helvetica, sans-serif;
      font-weight: bold;

      position: absolute;
      top: 60px;
      /* Distância do topo */
      left: 10px;
      /* Distância da direita */
      font-size: 20px;
      color: white;
      z-index: 10;
      background-color: rgba(0, 0, 0, 0.5);
      /* Fundo semitransparente para o texto */
      padding: 5px 10px;
      border-radius: 5px;
    }
  </style>
</head>

<body>
  <div class="slide-container">
    <div id="slideShow"></div>
    <!-- Exibe o nome da apresentação -->
    <div class="presentation-title">Apresentação: <?php echo htmlspecialchars($presentation['title']); ?></div>
    <!-- Exibe o nome do televisor -->
    <div class="tv-name">Televisor: <?php echo htmlspecialchars($televisor_name); ?></div>
  </div>




  <script>
    // Converte a variável PHP '$files' em um array JavaScript
    const slides = <?php echo json_encode($files); ?>;
    const televisorUrl = '<?php echo $televisor_url; ?>';

    // Variável que mantém o índice do slide atual
    let currentSlideIndex = 0;

    // Função para exibir um slide baseado no índice
    function showSlide(index) {
      const slideContainer = document.getElementById('slideShow');
      const slideData = slides[index];

      slideContainer.innerHTML = '';

      // Verifica se o slide possui um caminho de arquivo
      if (slideData.file_path) {
        // Verifica se o arquivo é uma imagem (extensões jpg, jpeg, png ou gif)
        if (slideData.file_path.match(/\.(jpg|jpeg|png|gif)$/i)) {
          // Cria um elemento de imagem
          const img = document.createElement('img');
          img.src = slideData.file_path; // Define o caminho da imagem
          img.alt = 'Imagem do Slide'; // Texto alternativo para a imagem
          img.classList.add('slideShow'); // Adiciona a classe CSS para garantir o estilo
          slideContainer.appendChild(img); // Adiciona a imagem ao contêiner de slides


        } else if (slideData.file_path.match(/\.(mp4|avi|mov)$/i)) {
          // Cria um elemento de vídeo
          const video = document.createElement('video');
          video.src = slideData.file_path; // Define o caminho do vídeo
          video.autoplay = true; // O vídeo começa automaticamente
          video.loop = true; // O vídeo vai se repetir infinitamente
          video.muted = true; // Define o vídeo como mudo para garantir que inicie automaticamente
          video.classList.add('slideShow', 'video'); // Adiciona a classe CSS para estilo e responsividade
          slideContainer.appendChild(video); // Adiciona o vídeo ao contêiner
        }
      }

      setTimeout(function() {
        currentSlideIndex = (currentSlideIndex + 1) % slides.length;
        showSlide(currentSlideIndex);
      }, slideData.intervalo_slide * 1000);
    }

    // Inicia a exibição do primeiro slide
    showSlide(currentSlideIndex);

    // Usar pushState para mudar a URL visível na barra de endereços sem recarregar a página
    const cleanUrl = '<?php echo "show_slides.php?televisor_id=" . $televisor['id'] . "&presentation_id=" . $presentation_id; ?>';

    // Alterar a URL visível na barra de endereços (sem recarregar a página)
    history.pushState(null, '', cleanUrl);
  </script>

</body>

</html>