<?php
include 'config.php'; // Conexão com o banco de dados


// Buscar os dados da apresentação associada ao presentation_id
$presentation_sql = "SELECT * FROM presentations WHERE id = ?";
$presentation_stmt = $conexao->prepare($presentation_sql);

if (!$presentation_stmt) {
    die('Erro na preparação da consulta da apresentação: ' . $conexao->error);
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

if (!$files_stmt) {
    die('Erro na preparação da consulta de arquivos: ' . $conexao->error);
}

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
    </style>

</head>

<body>
    <div class="slide-container">
        <div id="slideShow"></div>
    </div>

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

            // Definindo o diretório de upload
            const uploadDirectory = '../';

            // Verifica se o slide possui um caminho de arquivo
            if (slideData.file_path) {
                // Se o arquivo for uma imagem (extensões jpg, jpeg, png ou gif)
                if (slideData.file_path.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    // Cria um elemento de imagem
                    const img = document.createElement('img');
                    img.src = uploadDirectory + slideData.file_path; // Define o caminho da imagem
                    img.alt = 'Imagem do Slide'; // Texto alternativo para a imagem
                    img.classList.add('slideShow'); // Adiciona a classe CSS para garantir o estilo
                    slideContainer.appendChild(img); // Adiciona a imagem ao contêiner

                } else if (slideData.file_path.match(/\.(mp4|avi|mov)$/i)) {
                    // Cria um elemento de vídeo
                    const video = document.createElement('video');
                    video.src = uploadDirectory + slideData.file_path; // Define o caminho do vídeo
                    video.autoplay = true; // O vídeo começa automaticamente
                    video.loop = true; // O vídeo vai se repetir infinitamente
                    video.muted = true; // Define o vídeo como mudo para garantir que inicie automaticamente
                    video.classList.add('slideShow', 'video'); // Adiciona a classe CSS para estilo e responsividade
                    slideContainer.appendChild(video); // Adiciona o vídeo ao contêiner
                }

            }
            // Verifica se chegou ao último slide
            if (index === slides.length - 1) {
                // Após o último slide, recarrega a página
                setTimeout(function() {
                    window.location.reload();
                }, slideData.intervalo_slide * 1000); // Delay para garantir que o último slide seja exibido
            }


            // Define o tempo para trocar para o próximo slide
            setTimeout(function() {
                currentSlideIndex = (currentSlideIndex + 1) % slides.length;
                showSlide(currentSlideIndex);
            }, slideData.intervalo_slide * 1000); // Converte o intervalo de segundos para milissegundos
        }

        // Inicia a exibição do primeiro slide assim que a página carregar
        window.onload = function() {
            showSlide(currentSlideIndex);
        }
    </script>
</body>

</html>