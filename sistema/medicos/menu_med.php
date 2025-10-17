<?php
session_start();

// Redireciona para login se não estiver logado
if (!isset($_SESSION['id_med'])) {
    header("Location: /tentativa-1/index/login/login_med.php");
    exit();
}

include("C:xampp/htdocs/tentativa-1/conexao.php");
$id_medico = $_SESSION['id_med'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Médico - Altus</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="menu_med.css">
</head>
<body>
  <!-- ===== SIDEBAR ===== -->
  <div class="sidebar">
    <div class="brand">
      <img class="logo-altus" src="../medicos/icones/logo_altusboneco.png" alt="Logo Altus">
    </div>
    <ul>
      <li><a href="/tentativa-1/sistema/funcionalidades/medicos/pacientes.php">👥 Pacientes</a></li>
      <li><a href="/tentativa-1/sistema/funcionalidades/medicos/perfil.php">👤 Perfil</a></li>
      <li onclick="window.location.href='/tentativa-1/index/login/login_med.php'">🚪 Sair</li>
    </ul>
  </div>

  <!-- ===== CONTEÚDO PRINCIPAL ===== -->
  <div class="main">
    <div class="welcome-container">
      <div class="owl-speech">
        <img src="../medicos/icones/coruja-med.png" alt="Coruja" class="animated-owl">
        <div class="speech-bubble">
          <h1>Bem-vindo, Doutor!</h1>
        </div>
      </div>
    </div>

    <!-- Cards de Funções -->
    <div class="cards">
      <a href="/tentativa-1/sistema/funcionalidades/medicos/consulta.php" class="card">
        <div class="card-icon">
          <img src="../medicos/icones/telemedicine.png" alt="Consultas">
        </div>
        <h3>Consultas</h3>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/medicos/progresso.php" class="card">
        <div class="card-icon">
          <img src="../medicos/icones/progresso.png" alt="Progresso">
        </div>
        <h3>Progresso</h3>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/medicos/feedback.php" class="card">
        <div class="card-icon">
          <img src="\tentativa-1\sistema\pacientes\icons\feedbacks.png" alt="Feedback">
        </div>
        <h3>Feedback</h3>
      </a>
      <a href="/tentativa-1/sistema/exercicios/exercicios_med.php" class="card">
        <div class="card-icon">
          <img src="\tentativa-1\sistema\pacientes\icons\exercicio.png" alt="Exercícios">
        </div>
        <h3>Exercícios</h3>
      </a>
      <a href="/tentativa-1/sistema/funcionalidades/comunidade/chat_comunidade.php" class="card">
        <div class="card-icon">
          <img src="\tentativa-1\sistema\pacientes\icons\comunidade.png" alt="Comunidade">
        </div>
        <h3>Comunidade</h3>
      </a>
    </div>
  </div>
</body>
</html>
