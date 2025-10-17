✅ Código corrigido
<?php
session_start();

if (!isset($_SESSION['id_med'])) {
    header("Location: login_med.php");
    exit();
}

include("c:/xampp/htdocs/tentativa-1/conexao.php");

$id_medico = $_SESSION['id_med'];

$where = [];

if (isset($_GET['id_user']) && $_GET['id_user'] != "") {
    $id_user = mysqli_real_escape_string($cone, $_GET['id_user']);
    $where[] = "p.id_user LIKE '%$id_user%'";
}

if (isset($_GET['nome']) && $_GET['nome'] != "") {
    $nome = mysqli_real_escape_string($cone, $_GET['nome']);
    $where[] = "u.Nome LIKE '%$nome%'";
}

if (isset($_GET['sobrenome']) && $_GET['sobrenome'] != "") {
    $sobrenome = mysqli_real_escape_string($cone, $_GET['sobrenome']);
    $where[] = "u.Sobrenome LIKE '%$sobrenome%'";
}

if (isset($_GET['id_ex']) && $_GET['id_ex'] != "") {
    $id_ex = mysqli_real_escape_string($cone, $_GET['id_ex']);
    $where[] = "p.id_ex LIKE '%$id_ex%'";
}

if (isset($_GET['autonomia']) && $_GET['autonomia'] != "") {
    $autonomia = mysqli_real_escape_string($cone, $_GET['autonomia']);
    $where[] = "p.autonomia LIKE '%$autonomia%'";
}

$sql = "SELECT 
    p.id_user,
    u.Nome,
    u.Sobrenome,
    p.id_ex,
    p.autonomia
FROM tb_progresso p
JOIN tb_user u ON p.id_user = u.id_user";

if (count($where) > 0) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.id_user";

$result = mysqli_query($cone, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Progresso</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="menu_med.css"> <!-- Seu arquivo CSS de sidebar -->
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f7f6ed;
            display: flex;
            min-height: 100vh;
            overflow: hidden;
        }
        /* ===== SIDEBAR ===== */
.sidebar {
  width: 240px;
  background-color: #168686;
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  color: white;
}

.sidebar .brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 30px;
}
.sidebar .logo-altus {
  width: 40px;
  height: 40px;
  object-fit: contain;
}
.sidebar ul {
  list-style: none;
  padding: 0;
  margin: 0;
}
.sidebar ul li {
  padding: 12px 20px;
  margin: 4px 0;
  cursor: pointer;
  transition: background-color 0.2s;
  font-weight: 600;
}
.sidebar ul li a {
  text-decoration: none;
  color: var(--white);
  display: block;
}
.sidebar ul li:hover {
  background: var(--sidebar-dark);
}
.logout {
  margin-top: auto;
  padding: 0 20px;
}
.logout a {
  text-decoration: none;
  color: var(--white);
  font-weight: 600;
  display: block;
  padding: 10px 15px;
  border-radius: 6px;
  background: var(--sidebar-dark);
  text-align: center;
  transition: background 0.3s;
}
.logout a:hover {
  background: var(--accent);
}

        .main {
            margin-left: 240px; /* Espaço para a sidebar */
            flex: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
            background: #f7f6ed;
            align-items: center;
        }

        h2 {
            font-size: 32px;
            font-weight: 600;
            color: #168686;
            text-align: center;
            margin-bottom: 40px;
        }

        .search-box {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .search-box input {
            padding: 8px 12px;
            width: 180px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
            margin: 5px;
        }

        .search-box button {
            padding: 8px 12px;
            border: none;
            background: #168686;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            padding-bottom: 30px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 16px;
            width: 300px;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .user-name {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .stats {
            margin: 8px 0;
        }

        .badge {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: bold;
            color: white;
        }

        .badge.verde { background: #2ecc71; }
        .badge.amarelo { background: #f1c40f; }
        .badge.vermelho { background: #e74c3c; }

        /* Responsividade */
        @media (max-width: 900px) {
            .cards {
                flex-direction: column;
                gap: 25px;
                margin-top: 20px;
            }
            .card {
                width: 100%;
                max-width: 350px;
            }
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
         <li><a href="perfil.php">👤 Perfil</a></li>
        <li><a href="pacientes.php">👥 Pacientes</a></li>
      <li><a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a></li>
    </ul>

    <div class="logout">
      <a href="/tentativa-1/logout.php">i</a>
    </div>
  </div>
    <!-- Main Content -->
    <div class="main">
        <h2>📊 Progresso dos Usuários</h2>

        <!-- Caixa de busca -->
        <div class="search-box">
            <form method="GET">
                <input type="text" name="id_user" placeholder="ID Usuário" value="<?php echo isset($_GET['id_user']) ? htmlspecialchars($_GET['id_user']) : ''; ?>">
                <input type="text" name="nome" placeholder="Nome" value="<?php echo isset($_GET['nome']) ? htmlspecialchars($_GET['nome']) : ''; ?>">
                <input type="text" name="sobrenome" placeholder="Sobrenome" value="<?php echo isset($_GET['sobrenome']) ? htmlspecialchars($_GET['sobrenome']) : ''; ?>">
                <input type="text" name="id_ex" placeholder="ID Exercício" value="<?php echo isset($_GET['id_ex']) ? htmlspecialchars($_GET['id_ex']) : ''; ?>">
                <input type="text" name="autonomia" placeholder="Autonomia" value="<?php echo isset($_GET['autonomia']) ? htmlspecialchars($_GET['autonomia']) : ''; ?>">
                <button type="submit">🔍 Pesquisar</button>
            </form>
        </div>

        <div class="cards">
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "
                    <div class='card'>
                        <div class='user-name'>#{$row['id_user']} - {$row['Nome']} {$row['Sobrenome']}</div>
                        <div class='stats'>🆔 Exercício: <b>{$row['id_ex']}</b></div>
                        <div class='stats'>⚡ Autonomia: <b>{$row['autonomia']}</b></div>
                        <div class='stats'>✅ Exercícios feitos</div>
                    </div>";
                }
            } else {
                echo "<p style='text-align:center'>Nenhum usuário encontrado</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>
