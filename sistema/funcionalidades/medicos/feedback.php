<?php
session_start();

// Se não houver login, volta para a tela de login
if (!isset($_SESSION['id_med'])) {
    header("Location: login_med.php");
    exit();
}

include("c:/xampp/htdocs/tentativa-1/conexao.php");

// Usa o ID da sessão em vez de fixar manualmente
$id_medico = $_SESSION['id_med'];

// Filtro de busca
$filtro = "";
if (isset($_GET['busca']) && !empty($_GET['busca'])) {
    $busca = mysqli_real_escape_string($cone, $_GET['busca']);
    $filtro = "WHERE u.id_user LIKE '%$busca%' OR u.nome LIKE '%$busca%' OR u.sobrenome LIKE '%$busca%'";
}

$sql = "
SELECT 
    u.id_user, 
    u.nome, 
    u.sobrenome, 
    m.dificuldades,
    f.avancos, 
    f.observacoes, 
    f.data_feedback
FROM tb_user AS u
LEFT JOIN tb_user_med AS m 
    ON u.id_user = m.id_user
LEFT JOIN (
    SELECT id_user, avancos, observacoes, data_feedback
    FROM tb_feedback
    WHERE data_feedback = (
        SELECT MAX(data_feedback)
        FROM tb_feedback AS f2
        WHERE f2.id_user = tb_feedback.id_user
    )
) AS f
    ON u.id_user = f.id_user
$filtro
ORDER BY u.nome ASC
";

$result = mysqli_query($cone, $sql);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>FeedBacks</title>
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
  color: var(--text-dark);
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
}

.sidebar .brand {
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  margin-bottom: 20px;
}

.sidebar .brand img {
  width: 80px;
  margin-bottom: 10px;
}

.sidebar h2 {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
}

.sidebar ul {
  list-style: none;
  padding: 0;
  margin-top: 20px;
}

.sidebar ul li {
  padding: 12px 20px;
  margin: 4px 0;
  cursor: pointer;
  transition: background-color 0.2s;
}

.sidebar ul li:hover {
  background: var(--sidebar-dark);
}

.sidebar ul li a {
  text-decoration: none;
  color: var(--white);
  display: block;
  font-weight: 600;
}

/* Conteúdo */
.container {
  flex: 1;
  padding: 30px;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: var(--sidebar-dark);
}

.search-box {
  text-align: center;
  margin-bottom: 20px;
}

.search-box input[type="text"] {
  padding: 8px;
  width: 250px;
  border: 1px solid #ccc;
  border-radius: 5px;
}

.search-box button {
  padding: 8px 12px;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: 600;
}

.search-box button:hover {
  background: #00977d;
}

.card {
  border: 1px solid #ddd;
  border-radius: 12px;
  padding: 15px;
  margin: 15px 0;
  background: var(--card);
  box-shadow: 2px 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.card:hover {
  transform: translateY(-5px);
}

.card h3 {
  margin: 0 0 10px;
  color: var(--sidebar-dark);
}

.card p {
  margin: 6px 0;
}

.dif { color: #c0392b; font-weight: bold; }
.avan { color: #27ae60; font-weight: bold; }
.obs { color: #2980b9; }
.data { font-size: 0.9em; color: #555; }

.logout {
  margin-top: auto;
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
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="brand">
    <img src="/tentativa-1/sistema/pacientes/icons/logo.png" alt="Logo Altus">

  </div>
  <ul>
    <li><a href="pacientes.php">👥 Pacientes</a></li>
    <li><a href="perfil.php">👤 Perfil</a></li>
<div class="logout">
    <a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a>
  </div>
</div>
  </ul>
  

<!-- Conteúdo -->
<div class="container">
    <h2>FeedBacks</h2>

    <!-- Barra de pesquisa -->
    <div class="search-box">
        <form method="GET">
            <input type="text" name="busca" placeholder="Pesquisar por ID ou Nome" value="<?php echo isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : ''; ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <?php
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            echo "
            <div class='card'>
                <h3>👤 {$row['nome']} {$row['sobrenome']} <span style='font-size:0.8em; color:#666;'>(ID: {$row['id_user']})</span></h3>
                <p class='data'>📅 " . (!empty($row['data_feedback']) ? date('d/m/Y', strtotime($row['data_feedback'])) : 'Sem data') . "</p>
                <p class='dif'>⚠️ Dificuldades: " . (!empty($row['dificuldades']) ? $row['dificuldades'] : 'Nenhuma registrada') . "</p>
                <p class='avan'>✅ Avanços: " . (!empty($row['avancos']) ? $row['avancos'] : 'Nenhum registrado') . "</p>
                <p class='obs'>📝 Observações: " . (!empty($row['observacoes']) ? $row['observacoes'] : 'Sem observações') . "</p>
            </div>
            ";
        }
    } else {
        echo "<p style='text-align:center;'>Nenhum feedback encontrado</p>";
    }
    ?>
</div>

</body>
</html>
