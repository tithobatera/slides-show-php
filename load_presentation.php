<?php
include 'config.php'; // Conexão com o banco de dados

// ID da apresentação passada por GET
$presentation_id = isset($_GET['presentation_id']) ? $_GET['presentation_id'] : null;

if ($presentation_id === null) {
    die('Erro: ID da apresentação não fornecido.');
}


// Buscar dados da apresentação
$presentation_sql = "SELECT * FROM presentations WHERE id = ?";
$presentation_stmt = $conexao->prepare($presentation_sql);
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
$files_stmt->bind_param("i", $presentation_id);
$files_stmt->execute();
$files_result = $files_stmt->get_result();
$files = $files_result->fetch_all(MYSQLI_ASSOC); // Obtenha todos os resultados em um array associativo
?>



<!-- HTML -->
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apresentação: <?php echo htmlspecialchars($presentation['title']); ?></title>
    <STYle>
        .slide-container {
            width:
                65vw;
            /*
        Ajuste
        a
        largura
        (80%
        da
        largura
        da
        tela)
        */
            height:
                70vh;
            /*
        Ajuste
        a
        altura
        (60%
        da
        altura
        da
        tela)
        */

        }

        .slideShow {
            width:
                95%;
            height:
                95%;
            object-fit:
                cover;
        }

        .slideShow.video {
            width:
                100%;
            height:
                100%;
            object-fit:
                contain;
        }
    </STYle>
</head>

<body>
    <p><?php echo htmlspecialchars($presentation['title']); ?> </p>

    <div class="slide-container">
        <div id="slideShow"></div>
    </div>
    <!-- Exibe o nome da apresentação antes do slideshow -->



    <!-- JAVASCRIPT -->
    <script>
        // Converte a variável PHP '$files' em um array JavaScript
        const slides = <?php echo json_encode($files); ?>;

        // Variável que mantém o índice do slide atual
        let currentSlideIndex = 0;

        // Função para exibir um slide baseado no índice
        function showSlide(index) {
            // Obtém o contêiner onde o slide será exibido
            const slideContainer = document.getElementById('slideShow');

            // Obtém os dados do slide atual
            const slideData = slides[index];

            // Limpa qualquer conteúdo anterior no contêiner de slides
            slideContainer.innerHTML = '';

            // Verifica se o slide possui um caminho de arquivo
            if (slideData.file_path) {
                // Se o arquivo for uma imagem (extensões jpg, jpeg, png ou gif)
                if (slideData.file_path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    // Cria um elemento de imagem
                    const img = document.createElement('img');
                    img.src = slideData.file_path; // Define o caminho da imagem
                    img.alt = 'Imagem do Slide'; // Texto alternativo para a imagem
                    img.classList.add('slideShow'); // Adiciona a classe CSS para garantir o estilo
                    slideContainer.appendChild(img); // Adiciona a imagem ao contêiner


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

            // Define o tempo para trocar para o próximo slide
            // A função setTimeout é usada para esperar o tempo de intervalo antes de passar para o próximo slide
            setTimeout(function() {
                // Atualiza o índice do slide para o próximo
                currentSlideIndex = (currentSlideIndex + 1) % slides.length;
                // Chama a função novamente para exibir o próximo slide
                showSlide(currentSlideIndex);
            }, slideData.intervalo_slide * 1000); // Converte o intervalo de segundos para milissegundos
        }

        // Inicia a exibição do primeiro slide
        showSlide(currentSlideIndex);
    </script>



</body>

</html>