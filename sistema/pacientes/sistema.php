<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="/tentativa-1/sistema/pacientes/css/sistema.css" rel="stylesheet">
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="brand">
      <img class="logo-altus" src="/tentativa-1/sistema/pacientes/icons/logo.png" alt="Logo Altus">
    </div>
    <ul>
  
      
      <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/perfil_user.php">👤 Perfil</a></li>
       <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/calendario.php">📅 Agenda </a></li>
      <li onclick="window.location.href='/tentativa-1/index/login/login_user.php'">🚪 Sair</li>
    </ul>
  </div>

  <!-- Conteúdo principal -->
  <div class="main">
    <div class="welcome-container">
      <div class="owl-speech">
        <img src="\tentativa-1\sistema\pacientes\icons\coruja.png" alt="coruja" class="animated-owl">
        <div class="speech-bubble">
          <h1>Bem-vindo!</h1>
        </div>
      </div>
    </div>
    
    <div class="cards" style="justify-content: center; align-items: flex-start; margin-bottom: 40px;">
      <a href="/tentativa-1/sistema/exercicios/exercicios.php" style="text-decoration:none; color:inherit;">
        <div class="card">
          <div class="card-icon">
            <img src="\tentativa-1\sistema\pacientes\icons\exercicio.png" alt="exercicios">
          </div>
          <h3>Exercícios</h3>
        </div>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/pacientes/feedback(usuario).php" style="text-decoration:none; color:inherit;">
        <div class="card">
          <div class="card-icon">
            <img src="\tentativa-1\sistema\pacientes\icons\feedbacks.png" alt="feedbacks">
          </div>
          <h3>FeedBack</h3>
        </div>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/pacientes/chat_user.php" style="text-decoration:none; color:inherit;">
        <div class="card">
          <div class="card-icon">
            <img src="\tentativa-1\sistema\pacientes\icons\chat.png" alt="chat">
          </div>
          <h3>Conversas</h3>
        </div>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/comunidade/chat_comunidade.php" style="text-decoration:none; color:inherit;">
        <div class="card">
          <div class="card-icon">
            <img src="\tentativa-1\sistema\pacientes\icons\comunidade.png" alt="chat">
          </div>
          <h3>Comunidade</h3>
        </div>
      </a>
    </div>

    <!-- Progresso dos Exercícios -->
    <div class="exercicios-status">
      <?php
      // Pega total de exercícios
      $sqlTotal = "SELECT COUNT(*) AS total FROM tb_exercicios";
      $resTotal = $cone->query($sqlTotal);
      $totalEx = $resTotal->fetch_assoc()['total'];

      // Pega quantos o usuário já fez
      $sqlFeitos = "SELECT COUNT(*) AS feitos FROM tb_progresso WHERE id_user = $id_user";
      $resFeitos = $cone->query($sqlFeitos);
      $feitos = $resFeitos->fetch_assoc()['feitos'];

      // Lista detalhada
      $sqlLista = "
          SELECT e.id_ex, e.nome, p.autonomia,
                CASE WHEN p.id_ex IS NULL THEN 0 ELSE 1 END AS feito
          FROM tb_exercicios e
          LEFT JOIN tb_progresso p
            ON e.id_ex = p.id_ex AND p.id_user = $id_user
      ";
      $resLista = $cone->query($sqlLista);

      // Renderiza progresso
      echo '<div class="progresso-card">';
      echo '<div class="progresso-icon">';
      echo '<img src="\tentativa-1\sistema\pacientes\icons\desempenho.png" alt="Desempenho">';
      echo '</div>';
      echo "<h2>Seu Progresso</h2>";
      echo "<p class='resumo'>Você concluiu <strong>$feitos</strong> de <strong>$totalEx</strong> exercícios</p>";
      echo "<ul class='lista-exercicios'>";
      while ($row = $resLista->fetch_assoc()) {
          $icon = $row['autonomia'];
          $status = $row['feito'] ? "feito" : "nao-feito";
          $icon = $row['feito'] ? "✅" : "❌";
          echo "<li class='$status'>{$row['nome']} $icon - {$row['autonomia']}</li>";
          

      }
      echo "</ul>";
      echo "</div>";
      ?>
    </div>
    
    <script src="login.js"></script>
</body>
</html>