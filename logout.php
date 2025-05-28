<?php
session_start();
session_destroy(); // Destroi todas as sessões
header("Location: index.php"); // Redireciona para o login
exit;
?>
