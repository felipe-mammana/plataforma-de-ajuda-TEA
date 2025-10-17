<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_med = ensureLoggedInMedico(); // garante que só médico logado acesse

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

// Buscar exercícios no banco
$sql = "SELECT id_ex, nome, grau, tipos, arquivo, link, foto FROM tb_exercicios";
$result = $cone->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Altus - Exercícios (Médico)</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg: #f7f6ed;
      --sidebar: #168686;
      --sidebar-dark: #0e5a59;
      --card: #ffffff;
      --accent: #00c1a0;
      --text-dark: #333;
      --white: #fff;
    }

    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: "Poppins", sans-serif;
      background: var(--bg);
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: var(--sidebar);
      color: var(--white);
      display: flex;
      flex-direction: column;
      padding: 20px 0;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      position: fixed;
      top: 0; bottom: 0; left: 0;
    }

    .sidebar .brand {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      margin-bottom: 25px;
      flex-direction: column;
    }

    .sidebar .brand img {
      width: 60px;
      margin-bottom: 8px;
    }

    .sidebar .brand h2 {
      font-size: 20px;
      font-weight: 600;
    }

    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
      flex: 1;
    }

    .sidebar ul li {
      padding: 14px 20px;
      cursor: pointer;
      transition: background 0.2s;
    }

    .sidebar ul li a {
      text-decoration: none;
      color: var(--white);
      font-weight: 500;
      display: block;
    }

    .sidebar ul li:hover {
      background: var(--sidebar-dark);
      border-radius: 6px;
    }

    .logout {
      padding: 15px;
    }

    .logout a {
      display: block;
      text-align: center;
      padding: 10px;
      background: var(--sidebar-dark);
      color: var(--white);
      text-decoration: none;
      border-radius: 8px;
      font-weight: 600;
      transition: background 0.3s;
    }

    .logout a:hover {
      background: var(--accent);
    }

    /* Conteúdo */
    .main-content {
      flex: 1;
      margin-left: 240px;
      padding: 40px;
    }

    .main-content h2 {
      font-size: 26px;
      font-weight: 600;
      color: var(--sidebar-dark);
      margin-bottom: 25px;
      text-align: center;
    }

    .exercises-container {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 25px;
    }

    .exercise-card {
      background: var(--card);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 6px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    .exercise-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 12px 25px rgba(0,0,0,0.15);
    }

    .exercise-image {
      height: 180px;
      background-size: cover;
      background-position: center;
      border-bottom: 1px solid #eee;
    }

    .exercise-info {
      padding: 20px;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .exercise-id {
      font-size: 14px;
      color: #7f8c8d;
      margin-bottom: 5px;
    }

    .exercise-name {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    .exercise-details {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 15px;
    }

    .exercise-detail {
      background-color: #f8f9fa;
      padding: 6px 10px;
      border-radius: 12px;
      font-size: 13px;
      color: var(--sidebar-dark);
    }

    .exercise-info a {
      text-decoration: none;
      background: var(--accent);
      color: white;
      padding: 10px 16px;
      border-radius: 8px;
      transition: 0.3s;
      text-align: center;
      font-weight: 600;
    }

    .exercise-info a:hover {
      background: #0ea78a;
    }

    .footer {
      text-align: center;
      margin-top: 40px;
      color: #7f8c8d;
      font-size: 14px;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="brand">
      <img src="/tentativa-1/sistema/pacientes/icons/logo.png" alt="Logo Altus">
      
    </div>
    <ul>
      <li><a href="perfil.php">👤 Perfil</a></li>
      <li><a href="\tentativa-1\sistema\funcionalidades\medicos\pacientes.php">👥 Pacientes</a></li>
    <div class="logout">
      <a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a>
    </div>
  </div>

  <!-- Conteúdo -->
  <div class="main-content">
    <h2>Exercícios disponíveis</h2>

    <div class="exercises-container">
      <?php while($row = $result->fetch_assoc()): ?>
        <div class="exercise-card" data-ex-id="<?php echo $row['id_ex']; ?>">
          <div class="exercise-image" 
               style="background-image: url('mostrar_imagem_ex.php?id=<?php echo $row['id_ex']; ?>');">
          </div>
          <div class="exercise-info">
            <div>
              <div class="exercise-id">ID: <?php echo $row['id_ex']; ?></div>
              <div class="exercise-name"><?php echo $row['nome']; ?></div>
              <div class="exercise-details">
                <span class="exercise-detail">Grau: <?php echo $row['grau']; ?></span>
                <span class="exercise-detail">Tipo: <?php echo $row['tipos']; ?></span>
              </div>
            </div>
            <?php
              $path = str_replace("C:/xampp/htdocs", "", $row['arquivo']);
              $sep = (strpos($path, '?') !== false) ? '&' : '?';
              $url = $path . $sep . 'id_ex=' . urlencode($row['id_ex']);
            ?>
            <a class="exercise-open-link" href="<?php echo $url; ?>" target="_blank">Abrir Exercício</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
</body>
</html>
