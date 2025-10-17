<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

// Buscar exercícios no banco
$sql = "SELECT id_ex, nome, grau, tipos, arquivo, link, foto FROM tb_exercicios";
$result = $cone->query($sql);

// carregar progresso do usuario
$progresso = [];
$stmtp = $cone->prepare("SELECT id_ex, ex_feitos, ex_restantes FROM tb_progresso WHERE id_user = ?");
if ($stmtp) {
    $stmtp->bind_param('i', $id_user);
    if ($stmtp->execute()) {
        $stmtp->bind_result($pe_id_ex, $pe_feitos, $pe_restantes);
        while ($stmtp->fetch()) {
            $progresso[intval($pe_id_ex)] = ['ex_feitos' => intval($pe_feitos), 'ex_restantes' => intval($pe_restantes)];
        }
    }
    $stmtp->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Altus - Exercícios</title>
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
.sidebar {
  width: 240px;
  background: var(--sidebar);
  color: var(--white);
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar h2 {
  font-size: 20px;
  font-weight: 600;
  margin-bottom: 0;
  margin-left: 8px;
  text-align: left;
}

.sidebar ul {
  list-style: none;
  padding: 0;
}

.sidebar ul li {
  padding: 12px 20px;
  margin: 4px 0;
  cursor: pointer;
  transition: background-color 0.2s;
  color: var(--white);
  font-weight: 600;
}

.sidebar ul li:hover {
  background: var(--sidebar-dark);
}

.sidebar ul li a {
  text-decoration: none;
  color: var(--white);
  display: block;
  width: 100%;
  height: 100%;
}

.sidebar ul li a:hover {
  color: var(--white);
  text-decoration: none;
  .brand {
  display: flex;
  align-items: center;
  justify-content: center; /* centraliza no meio da sidebar */
  gap: 10px;
  margin-bottom: 30px;
}

.logo-altus {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

}
 .sidebar logo-altus {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 15px;
  margin-bottom: 20px;
}
.logo-altus {
  width: 40px;  
  height: 40px;  
  object-fit: contain; 
}
.logout a {
  text-decoration: none; 
  color: var(--white);   
  font-weight: 600;      
  display: inline-block;
  padding: 10px 15px;
  border-radius: 6px;
  background: var(--sidebar-dark); 
  transition: background 0.3s;
}

.logout a:hover {
  background: var(--accent); 
  color: #fff;
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

    /* Badge de concluído */
    .exercise-card > [data-badge="concluido"] {
      position: absolute;
      top: 12px;
      right: 12px;
      background: #2ecc71;
      color: #fff;
      padding: 6px 10px;
      border-radius: 12px;
      font-weight: bold;
      font-size: 13px;
      z-index: 10;
      pointer-events: none;
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
      <img class="logo-altus" src="\tentativa-1\sistema\funcionalidades\pacientes\Logo.png" alt="Logo Altus">
    </div>
    <ul>
      <li><a href="\tentativa-1\sistema\funcionalidades\pacientes\perfil_user.php">👤 Perfil </a></li>
      <li><a href="\tentativa-1\sistema\funcionalidades\pacientes\calendario.php">📅 Agenda</a></li>
    </ul>
    <div class="logout">
      <a href="/tentativa-1/sistema/pacientes/sistema.php" 
     style="display:inline-block; padding:10px 16px; background:#168686; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">
     ⬅ Voltar
  </a>
    </div>
  </div>

  <!-- Conteúdo -->
  <div class="main-content">
    
    <h2>Selecione o exercício de hoje!</h2>

    <div class="exercises-container">
      <?php while($row = $result->fetch_assoc()): 
          $idEx = intval($row['id_ex']);
          $done = isset($progresso[$idEx]) && $progresso[$idEx]['ex_feitos'] >= 0;
      ?>
        <div class="exercise-card" data-ex-id="<?php echo $idEx; ?>">
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
            <?php if ($done): ?>
              <div data-badge="concluido">Concluído</div>
              <a class="exercise-open-link" href="<?php echo $url; ?>" target="_blank" style="background:#27ae60;">Refazer exercício</a>
            <?php else: ?>
              <a class="exercise-open-link" href="<?php echo $url; ?>" target="_blank">Abrir Exercício</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>

    <div class="footer">
      <p>ALTUS © 2025 - Todos os direitos reservados</p>
    </div>
  </div>
   <div style="position: absolute; top: 800px; left: 20px;">
  
</body>
</html>
