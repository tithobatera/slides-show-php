<div style="overflow: auto;">


    <?php


    // Conexão com o banco de dados
    include 'config.php'; // Certifique-se de incluir sua conexão com o banco de dados

    // Recupera o termo de busca
    $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

    // Número de apresentações por página
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

    // Consulta as apresentações com filtro de pesquisa
    $sql = "SELECT * FROM presentations WHERE title LIKE ? ORDER BY $order_by LIMIT $offset, $items_per_page";
    $stmt = $conexao->prepare($sql);
    $searchParam = '%' . $searchQuery . '%'; // Prepara a pesquisa com wildcard
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verifica se o resultado foi obtido corretamente
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
    ?>

</div>
</div>