<?php

include 'config.php';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Administrador</title>

  <!-- Incluindo o Bootstrap através do CDN -->
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>
  <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="login-container card p-4" style="max-width: 400px; width: 100%;">
      <h2 class="text-center">Iniciar Sessão</h2>
      <form id="loginForm">
        <div class="form-group">
          <label for="username">Usuário</label>
          <input type="text" id="username" name="username" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="password">Senha</label>
          <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
      </form>
    </div>
  </div>

  <!-- Incluindo o Bootstrap JS e Popper.js -->
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

  <script>
    // Função para validar e enviar o login via AJAX
    document.getElementById('loginForm').addEventListener('submit', function(event) {
      event.preventDefault(); // Impede o envio do formulário

      // Obter os valores do formulário
      var username = document.getElementById("username").value;
      var password = document.getElementById("password").value;

      // Enviar os dados via AJAX para o back-end
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "login.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.onload = function() {
        if (xhr.status == 200) {
          var response = JSON.parse(xhr.responseText);
          if (response.success) {
            // Redireciona para o painel administrativo
            window.location.href = "home.php"; // Redireciona para home.php
          } else {
            // Exibe um alerta caso a autenticação falhe
            alert(response.message);
          }
        }
      };

      // Envia os dados para o PHP
      xhr.send("username=" + username + "&password=" + password);
    });
  </script>
</body>

</html>
