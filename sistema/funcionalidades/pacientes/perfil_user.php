<?php

require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser(); // Use the specific function for users

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

$sql = "SELECT u.nome, u.sobrenome, u.idade, u.plano, u.foto AS foto_perfil, 
               r.nome AS nome_responsavel, r.cpf AS cpfr, r.telefone AS telefoner, r.email AS email_responsavel, r.foto AS foto_responsavel,
               m.dificuldades
         FROM tb_user u
         LEFT JOIN tb_responsavel r ON r.id_user = u.id_user
         LEFT JOIN tb_user_med m ON m.id_user = u.id_user
         WHERE u.id_user = $id_user";
$result = $cone->query($sql);

if (!$result) {
    die("Erro na query: " . $cone->error);
}

$user = $result->fetch_assoc();

// Check if a user was found
if (!$user) {
    // If no user found, redirect or show an error
    header('Location: /tentativa-1/index/login/login_user.php?error=user_not_found');
    exit();
}

// Processar dificuldades para o checkbox
$dificuldades_array = array();
if (!empty($user['dificuldades'])) {
    $dificuldades_array = explode(', ', $user['dificuldades']);
}

// Função para verificar se uma dificuldade está selecionada
function isChecked($dificuldade, $dificuldades_array) {
    return in_array($dificuldade, $dificuldades_array) ? 'checked' : '';
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Usuário - ALTUS</title>
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
/* Container principal */
.container {
  flex: 1;
  padding: 30px;
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  align-items: flex-start;
}

/* Cards */
.card {
  background: var(--card);
  border-radius: 16px;
  box-shadow: 0 6px 15px rgba(0,0,0,0.1);
  padding: 25px;
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 25px rgba(0,0,0,0.15);
}

.perfil {
  width: 300px;
  text-align: center;
}

.editar {
  flex: 1;
  min-width: 500px;
}

/* Avatar */
.avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  margin: 0 auto 20px;
  overflow: hidden;
  cursor: pointer;
  border: 4px solid var(--accent);
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f1f1;
}

.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.circle-avatar {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--accent);
  color: white;
  font-size: 2rem;
  font-weight: bold;
}

/* Títulos */
h2 {
  color: var(--sidebar-dark);
  margin-bottom: 20px;
  font-size: 22px;
  font-weight: 700;
}

h3 {
  color: var(--accent);
  margin: 15px 0;
  font-size: 18px;
}

/* Informações do perfil */
.profile-info {
  margin: 15px 0;
  font-size: 16px;
  color: var(--text-dark);
}

.profile-info p {
  margin: 8px 0;
}

.profile-info strong {
  color: var(--sidebar-dark);
}

/* Formulários */
form {
  display: flex;
  flex-direction: column;
}

label {
  margin-top: 15px;
  margin-bottom: 5px;
  font-weight: 600;
  color: var(--sidebar-dark);
}

input, select {
  padding: 12px 15px;
  border: 1px solid #ddd;
  border-radius: 8px;
  font-size: 15px;
  transition: border 0.3s;
}

input:focus, select:focus {
  border-color: var(--accent);
  outline: none;
}

/* Botão */
button {
  margin-top: 25px;
  padding: 14px;
  background: var(--accent);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover {
  background: #00977d;
}

/* Grupos de inputs */
.info-group {
  display: flex;
  gap: 15px;
}

.info-group > div {
  flex: 1;
}

hr {
  margin: 20px 0;
  border: none;
  border-top: 1px solid #eee;
}

/* Checkbox */
.checkbox-group {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
  gap: 12px;
  margin: 10px 0;
}

.checkbox-item {
  display: flex;
  align-items: center;
  padding: 8px;
  background: #f9f9f9;
  border-radius: 6px;
  transition: background 0.2s;
}

.checkbox-item:hover {
  background: #f0f0f0;
}

.checkbox-item input {
  margin-right: 10px;
}

/* Upload */
.file-upload {
  display: flex;
  flex-direction: column;
  margin-top: 10px;
}

.file-upload-label {
  display: inline-block;
  padding: 12px 20px;
  background: var(--accent);
  color: white;
  border-radius: 8px;
  cursor: pointer;
  text-align: center;
  transition: background 0.3s;
  margin-top: 5px;
}

.file-upload-label:hover {
  background: #00977d;
}

.file-name {
  margin-top: 8px;
  font-size: 14px;
  color: #666;
}

/* Mensagens */
.message {
  padding: 15px;
  margin: 20px 0;
  border-radius: 8px;
  text-align: center;
  font-weight: 500;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Responsividade */
@media (max-width: 900px) {
  body {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
  }

  .sidebar ul {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
  }

  .container {
    padding: 20px;
    flex-direction: column;
  }

  .perfil, .editar {
    width: 100%;
    min-width: auto;
  }

  .info-group {
    flex-direction: column;
  }

  .checkbox-group {
    grid-template-columns: 1fr;
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
.section-title {
  font-family: 'Poppins', sans-serif;
  font-weight: 600;
  font-size: 20px;
  color: #1e2f2e;       /* cor escura, elegante */
  margin: 20px 0 15px;  /* espaçamento em cima e embaixo */
  padding-bottom: 8px;  /* espaço abaixo do texto */
  border-bottom: 2px solid #00c896; /* linha de destaque */
  display: inline-block; /* underline só do tamanho do texto */
}
    </style>
</head>
<body>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="brand">
      <img class="logo-altus" src="Logo.png" alt="Logo Altus">
    </div>
    <ul>
      <li><a href="perfil_user.php">👤 Perfil </a></li>
      <li><a href="calendario.php">📅 Agenda</a></li>
    </ul>
    <div class="logout">
      <a href="/tentativa-1/sistema/pacientes/sistema.php" 
     style="display:inline-block; padding:10px 16px; background:#168686; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">
     ⬅ Voltar
  </a>
    </div>
  </div>
<div class="container">
    <div class="card perfil">
        <div class="avatar" onclick="document.getElementById('fotoInput').click();">
    <?php if(!empty($user['foto_perfil'])): ?>
        <img src="data:image/jpeg;base64,<?= base64_encode($user['foto_perfil']) ?>" alt="Foto de perfil">
    <?php else: ?>
        <div class="circle-avatar"><?= strtoupper(substr($user['nome'] ?? '', 0, 1)) . strtoupper(substr($user['sobrenome'] ?? '', 0, 1)) ?></div>
    <?php endif; ?>
</div>

        <form id="formFoto" method="POST" action="att_foto_user.php" enctype="multipart/form-data" style="display:none;">
            <input type="file" id="fotoInput" name="foto" accept="image/*" onchange="document.getElementById('formFoto').submit();">
        </form>

        <h2><?= htmlspecialchars($user['nome'] ?? '') ?> <?= htmlspecialchars($user['sobrenome'] ?? '') ?></h2>
        
        <div class="profile-info">
            <p><strong>Idade:</strong> <?= !empty($user['idade']) ? htmlspecialchars($user['idade']) . ' anos' : 'Não informada' ?></p>
            <p><strong>Plano:</strong> <?= !empty($user['plano']) ? htmlspecialchars($user['plano']) : 'Não definido' ?></p>
        </div>
    </div>

    <div class="card editar">
        <h2>Editar Informações</h2>
        
        <?php
        // Exibir mensagens de sucesso/erro se existirem
        if (isset($_GET['success'])) {
            echo '<div class="message success">' . htmlspecialchars($_GET['success']) . '</div>';
        } elseif (isset($_GET['error'])) {
            echo '<div class="message error">' . htmlspecialchars($_GET['error']) . '</div>';
        }
        ?>
        
        <form method="POST" action="atualizar_perfil_user.php" enctype="multipart/form-data">
            <div class="section-title">Dados Pessoais</div>
            <div class="info-group">
                <div>
                    <label>Nome:</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Sobrenome:</label>
                    <input type="text" name="sobrenome" value="<?= htmlspecialchars($user['sobrenome'] ?? '') ?>" required>
                </div>
            </div>

            <div class="info-group">
                <div>
                    <label>Idade:</label>
                    <input type="number" name="idade" value="<?= htmlspecialchars($user['idade'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Plano:</label>
                    <select name="plano" required>
                        <option value="Prata" <?= ($user['plano'] ?? '') == 'Prata' ? 'selected' : '' ?>>Prata</option>
                        <option value="Ouro" <?= ($user['plano'] ?? '') == 'Ouro' ? 'selected' : '' ?>>Ouro</option>
                    </select>
                </div>
            </div>
            <hr>

            <div class="section-title">Responsável</div>
            <label>Nome do Responsável:</label>
            <input type="text" name="nome_responsavel" value="<?= htmlspecialchars($user['nome_responsavel'] ?? '') ?>" required>

            <div class="info-group">
                <div>
                    <label>CPF:</label>
                    <input type="text" name="cpf" value="<?= htmlspecialchars($user['cpfr'] ?? '') ?>" required>
                </div>
                <div>
                    <label>Telefone:</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($user['telefoner'] ?? '') ?>" required>
                </div>
            </div>

            <label>Email:</label>
            <input type="email" name="email_responsavel" value="<?= htmlspecialchars($user['email_responsavel'] ?? '') ?>" required>

            <hr>

            <div class="section-title">Informações Médicas</div>
            <div class="info-group">
                <div>
                    
                </div>
                <div>
                    <label>Laudo Médico (opcional):</label>
                    <div class="file-upload">
                        <label class="file-upload-label">
                            Escolher Arquivo
                            <input type="file" name="laudo" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                        </label>
                        <div class="file-name">Nenhum arquivo selecionado</div>
                    </div>
                </div>
            </div>

            <label>Dificuldades:</label>
            <div class="checkbox-group">
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Comunicação" <?= isChecked('Comunicação', $dificuldades_array) ?>> Comunicação
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Socialização" <?= isChecked('Socialização', $dificuldades_array) ?>> Socialização
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Concentração" <?= isChecked('Concentração', $dificuldades_array) ?>> Concentração
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Controle Emocional" <?= isChecked('Controle Emocional', $dificuldades_array) ?>> Controle Emocional
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Autonomia" <?= isChecked('Autonomia', $dificuldades_array) ?>> Autonomia
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Organização" <?= isChecked('Organização', $dificuldades_array) ?>> Organização
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Habilidades Motoras" <?= isChecked('Habilidades Motoras', $dificuldades_array) ?>> Habilidades Motoras
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Leitura" <?= isChecked('Leitura', $dificuldades_array) ?>> Leitura
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Escrita" <?= isChecked('Escrita', $dificuldades_array) ?>> Escrita
                </label>
                <label class="checkbox-item">
                    <input type="checkbox" name="dificuldades[]" value="Ansiedade" <?= isChecked('Ansiedade', $dificuldades_array) ?>> Ansiedade
                </label>
            </div>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<script>
    // Script para mostrar o nome do arquivo selecionado
    document.querySelector('input[name="laudo"]').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : 'Nenhum arquivo selecionado';
        document.querySelector('.file-name').textContent = fileName;
    });
</script>

</body>
</html>
