<?php
session_start();

// Se não houver login, volta para a tela de login
if (!isset($_SESSION['id_med'])) {
    header("Location: login_med.php");
    exit();
}

include ("c:/xampp/htdocs/tentativa-1/conexao.php");

// Usa o ID da sessão em vez de fixar manualmente
$id_medico = $_SESSION['id_med'];

$sql = "SELECT m.nome, m.tipo_especialidade, m.telefone, m.crn, m.foto, 
               l.email
        FROM tb_medico m
        JOIN tb_login l ON l.id_med = m.id_med
        WHERE m.id_med = $id_medico";

$result = $cone->query($sql);

if (!$result) {
    die("Erro na query: " . $cone->error);
}

$medico = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil Médico</title>
    <link rel="stylesheet" href="perfil_med.css">
    <style>
       .sidebar {
        width: 240px;
        background: #168686;
        color: #fff;
        display: flex;
        flex-direction: column;
        padding: 20px 0;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        position: fixed;
        top: 0; bottom: 0; left: 0;
    }

    .sidebar .logo {
        text-align: center;
        font-size: 22px;
        margin-bottom: 25px;
        font-weight: 600;
    }

    .sidebar ul {
        list-style: none;
        padding: 0;
        margin-top: 20px;
    }

    .sidebar ul li {
        padding: 14px 20px;
        cursor: pointer;
        transition: background 0.2s;
        border-radius: 6px;
    }

    .sidebar ul li a {
        text-decoration: none;   /* remove sublinhado */
        color: #fff;            /* mantém branco */
        font-weight: 500;
        display: block;         /* ocupa o bloco inteiro */
    }

    .sidebar ul li:hover {
        background: #0e5a59;    /* muda cor no hover */
    }

    .logout {
        margin-top: auto;
        padding: 14px 20px;
    }

    .logout a {
        text-decoration: none;
        color: #fff;
        font-weight: 600;
        display: block;
    }

    .logout a:hover {
        text-decoration: underline;
    }
    body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f9f9f0;
    display: flex;
    justify-content: center;   /* centraliza na horizontal */
    align-items: center;       /* centraliza na vertical */
    min-height: 100vh;
}

/* garante que o conteúdo não fique escondido pela sidebar */
.container {
    margin-left: 260px;  /* largura da sidebar (240px + espaçamento) */
    display: flex;
    gap: 20px;
    max-width: 1000px;
    width: 100%;
    justify-content: center;
}

    </style>
</head>
<body>

<div class="sidebar">
     <!-- Sidebar -->
    <ul>
         <li><a href="perfil.php">👤 Perfil</a></li>
        <li><a href="pacientes.php">👥 Pacientes</a></li>
      <li><a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a></li>
    </ul>

    <div class="logout">
      <a href="/tentativa-1/logout.php"></a>
    </div>
  </div>
<a href="/tentativa-1/sistema/medicos/menu_med.php" class="voltar-btn">
</a>
<div class="container">
    <div class="card perfil">
        <div class="avatar" onclick="document.getElementById('fotoInput').click();">
    <?php if(!empty($medico['foto'])): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($medico['foto']) ?>" alt="Foto de perfil">
    <?php else: ?>
        <div class="circle-avatar"><?= strtoupper(substr($medico['nome'], 0, 3)) ?></div>
    <?php endif; ?>
</div>

<form id="formFoto" method="POST" action="att_foto_med.php" enctype="multipart/form-data" style="display:none;">
    <input type="file" id="fotoInput" name="foto" accept="image/*" onchange="document.getElementById('formFoto').submit();">
</form>
        <h2><?= $medico['nome'] ?></h2>
        <p><?= $medico['tipo_especialidade'] ?></p>
        <p><?= $medico['crn'] ?></p>
        <p><b>Email:</b> <?= $medico['email'] ?></p>
        <p><b>Telefone:</b> <?= $medico['telefone'] ?></p>
    </div>

    <div class="card editar">
        <h2>Editar Informações</h2>
        <form method="POST" action="<?= htmlspecialchars('atualizar_perfil_med.php') ?>" enctype="multipart/form-data">
    <label>Nome completo:</label>
    <input type="text" name="nome" value="<?= $medico['nome'] ?>">

    <label>Especialidade:</label>
    <input type="text" name="tipo_especialidade" value="<?= $medico['tipo_especialidade'] ?>">

    <label>Email:</label>
    <input type="text" name="email" value="<?= $medico['email'] ?>">

    <label>Telefone:</label>
    <input type="text" name="telefone" value="<?= $medico['telefone'] ?>">

    <label>Registro profissional (CRM):</label>
    <input type="text" name="crn" value="<?= $medico['crn'] ?>">

    <button type="submit">Salvar</button>
</form>
    </div>
</div>

</body>
</html>