<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 


$id_user = $_SESSION['id_user'];
$mensagem = "";

// Processa o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $avancos = mysqli_real_escape_string($cone, $_POST['avancos']);
    $observacoes = mysqli_real_escape_string($cone, $_POST['observacoes']);
    
    // DEBUG: Verifica se os valores estão corretos
    // echo "ID User: " . $id_user . "<br>";
    // echo "Avanços: " . $avancos . "<br>";
    // echo "Observações: " . $observacoes . "<br>";
    
    // Insere o feedback no banco de dados (CORREÇÃO: aspas no $id_user)
    $sql = "INSERT INTO tb_feedback (id_user, avancos, observacoes, data_feedback) 
            VALUES ('$id_user', '$avancos', '$observacoes', NOW())";
    
    // DEBUG: Mostra a query para verificar
    // echo "Query: " . $sql . "<br>";
    
    if (mysqli_query($cone, $sql)) {
        $mensagem = "<div style='color: green; padding: 10px; background: #d4edda; border-radius: 5px;'>✅ Feedback enviado com sucesso!</div>";
    } else {
        $mensagem = "<div style='color: red; padding: 10px; background: #f8d7da; border-radius: 5px;'>❌ Erro ao enviar feedback: " . mysqli_error($cone) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Feedback - TCC TEA</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }
        :root {
  --sidebar: #168686;
  --sidebar-dark: #0e5e5e;
  --white: #ffffff;
  --accent: #1fa3a3;
}

.sidebar {
  width: 240px;
  background: var(--sidebar);
  color: var(--white);
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  position: fixed;          /* fixa na lateral */
  top: 0;
  left: 0;
  height: 100vh;
}

.sidebar .brand {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  margin-bottom: 30px;
  padding: 0 20px;
}

.sidebar .logo-altus {
  width: 40px;
  height: 40px;
  object-fit: contain;
}

.sidebar h2 {
  font-size: 20px;
  font-weight: 600;
  margin: 0;
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
  width: 100%;
}

.sidebar ul li:hover {
  background: var(--sidebar-dark);
}

.logout {
  margin-top: auto; /* fica no final da sidebar */
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
  transition: background 0.3s;
  text-align: center;
}

.logout a:hover {
  background: var(--accent);
}

body {
  font-family: 'Poppins', sans-serif;
  background-color: #f7f6ef;  /* mesmo tom claro da tela principal */
  margin: 0;
  padding-left: 240px;        /* espaço para a sidebar fixa */
}

.container {
  max-width: 700px;
  margin: 40px auto;
  background: #ffffff;
  padding: 40px;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.08);
  text-align: center;
}

.container h2 {
  font-size: 1.8rem;
  color: #0e5e5e; /* tom escuro do verde */
  margin-bottom: 25px;
}

.form-group {
  text-align: left;
  margin-bottom: 25px;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #0e5e5e;
}

textarea {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid #d6e4e4;
  border-radius: 12px;
  font-size: 16px;
  resize: vertical;
  min-height: 120px;
  background: #f9fbfb;
  transition: border-color .3s;
}

textarea:focus {
  border-color: #168686;
  outline: none;
}

.btn-enviar {
  display: inline-block;
  background: #168686;
  color: #fff;
  border: none;
  padding: 14px 40px;
  font-size: 18px;
  font-weight: 600;
  border-radius: 30px;
  cursor: pointer;
  transition: background .3s;
  margin-top: 10px;
}

.btn-enviar:hover {
  background: #0e5e5e;
}

.mensagem-ok {
  background: #dff7e1;
  color: #0a6630;
  padding: 12px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 600;
}

.mensagem-erro {
  background: #ffe1e1;
  color: #b32020;
  padding: 12px 20px;
  border-radius: 12px;
  margin-bottom: 20px;
  font-weight: 600;
}

.voltar {
  display: inline-block;
  margin-top: 25px;
  color: #168686;
  font-weight: 600;
  text-decoration: none;
  transition: color .2s;
}
.voltar:hover {
  color: #0e5e5e;
  text-decoration: underline;
}

    </style>
</head>
<body>
<!-- Sidebar -->
<div class="sidebar">
  <div class="brand">
    <img class="logo-altus" src="Logo.png" alt="Logo Altus">
  </div>

  <ul>
    <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/perfil_user.php">👤 Perfil</a></li>
    <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/calendario.php">📅 Agenda</a></li>
     <div class="logout">
    <a href="/tentativa-1/sistema/pacientes/sistema.php">⬅ Voltar</a>
  </div>
</div>
  </ul>

 
 <div class="container">
  <h2>📝 Enviar Feedback para o Médico</h2>

  <?php
    if (!empty($mensagem)) {
        // aplica classes diferentes conforme sucesso ou erro
        $classe = strpos($mensagem, '✅') !== false ? 'mensagem-ok' : 'mensagem-erro';
        echo "<div class='$classe'>$mensagem</div>";
    }
  ?>

  <form method="POST" action="">
    <div class="form-group">
      <label for="avancos">Avanços e Progressos</label>
      <textarea id="avancos" name="avancos"
        placeholder="Conte sobre seus avanços, conquistas e progressos..." required></textarea>
    </div>

    <div class="form-group">
      <label for="observacoes">Observações e Dificuldades</label>
      <textarea id="observacoes" name="observacoes"
        placeholder="Compartilhe suas dificuldades, observações ou algo que queira destacar..." required></textarea>
    </div>

    <button type="submit" class="btn-enviar">📤 Enviar Feedback</button>
  </form>
</div>
