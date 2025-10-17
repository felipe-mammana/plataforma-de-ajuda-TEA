<?php
session_start();
include("C:xampp/htdocs/tentativa-1/conexao.php");

// garante que o médico está logado
if (!isset($_SESSION['id_med'])) {
    die("Médico não logado.");
}
$id_med = $_SESSION['id_med'];

// pega todos os usuários cadastrados
$sql = "SELECT id_user, nome, sobrenome FROM tb_user";
$res = $cone->query($sql);
$usuarios = [];
while ($row = $res->fetch_assoc()) {
    $usuarios[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Chat Médico</title>
  <style>
:root {
  --sidebar: #168686;
  --sidebar-dark: #0e5e5e;
  --accent: #1fa3a3;
  --white: #ffffff;
  --bg: #f7f6ef;
  --msg-user: #c4f4c4;
  --msg-med: #e5f0f0;
}

body {
  margin: 0;
  font-family: 'Poppins', sans-serif;
  background: var(--bg);
  display: flex;
  height: 100vh;
}

/* ===== SIDEBAR ===== */
.sidebar {
  width: 240px;
  background: var(--sidebar);
  color: var(--white);
  display: flex;
  flex-direction: column;
  padding: 20px 0;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
  position: fixed;
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

/* ===== CONTEÚDO PRINCIPAL ===== */
.main {
  display: flex;
  height: 100vh;
  width: calc(100% - 240px); /* ✅ Ocupa o restante da tela */
  margin-left: 240px; /* ✅ Empurra para o lado da sidebar */
  box-sizing: border-box;
}



/* Lista de usuários (pacientes) */
#usuarios {
  width: 260px;
  background: #fff;
  border-right: 1px solid #e0e0e0;
  overflow-y: auto;
  padding-top: 20px;
  flex-shrink: 0; /* não deixa encolher */
}
#usuarios div {
  padding: 14px 18px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: background 0.2s;
}
#usuarios div:hover {
  background: #f9f9f9;
}
#usuarios img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
}

/* Área do chat */
#chat-area {
  flex: 1; /* ocupa todo o resto */
  display: flex;
  flex-direction: column;
  background: #fafafa;
}
#chat-header {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 24px;
  border-bottom: 1px solid #ddd;
  background: #ffffff;
}
#chat-header img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
}
#chat-header h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #0e5e5e;
}

#chat {
  flex: 1;
  padding: 25px 40px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.mensagem {
  padding: 12px 20px;
  border-radius: 20px;
  max-width: 75%;
  word-wrap: break-word;
  font-size: 15.5px;
  line-height: 1.4;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.enviada {
  background: var(--msg-med); /* médico enviando */
  margin-left: auto;
  border-top-right-radius: 4px;
}
.recebida {
  background: var(--msg-user); /* usuário enviando */
  margin-right: auto;
  border-top-left-radius: 4px;
}

/* Input */
#input-area {
  display: flex;
  padding: 18px 24px;
  background: #ffffff;
  border-top: 1px solid #ddd;
  gap: 12px;
}
#msg {
  flex: 1;
  padding: 12px 18px;
  border: 2px solid #d6e4e4;
  border-radius: 30px;
  font-size: 15px;
}
#msg:focus {
  border-color: var(--sidebar);
  outline: none;
}
#input-area button {
  background: var(--sidebar);
  color: #fff;
  border: none;
  padding: 12px 24px;
  border-radius: 30px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s;
}
#input-area button:hover {
  background: var(--sidebar-dark);
}
.horario {
  font-size: 11px;
  color: #777;
  margin-top: 4px;
  text-align: right;
}
  </style>
</head>
<body>
<body>
  <!-- ===== SIDEBAR ===== -->
  <div class="sidebar">
    <div class="brand">
      <img class="logo-altus" src="\tentativa-1\sistema\funcionalidades\pacientes\Logo.png" alt="Logo Altus">
    </div>

    <ul>
      <li><a href="perfil.php">👤 Perfil</a></li>
      <li><a href="\tentativa-1\sistema\funcionalidades\medicos\pacientes.php">👥 Pacientes</a></li>
      <li><a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a></li>
    </ul>

    <div class="logout">
      <a href="/tentativa-1/logout.php">Sair</a>
    </div>
  </div>

  <!-- ===== CONTEÚDO PRINCIPAL ===== -->
  <div class="main">
  <div id="usuarios">
    <?php foreach ($usuarios as $u): ?>
      <div onclick="abrirChat(<?= $u['id_user'] ?>, '<?= htmlspecialchars($u['nome'] . ' ' . $u['sobrenome']) ?>')">
        <img src="/tentativa-1/sistema/funcionalidades/pacientes/mostrar_foto.php?tipo=user&id=<?= $u['id_user'] ?>" alt="Foto User">
        <span><?= htmlspecialchars($u['nome'] . " " . $u['sobrenome']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- ✅ Fora do foreach, lado a lado com #usuarios -->
  <div id="chat-area">
    <div id="chat-header">
      <img id="foto-cabecalho" src="" alt="Foto Usuário">
      <h3 id="titulo-chat">Selecione um usuário</h3>
    </div>

    <div id="chat"></div>

    <div id="input-area" style="display:none;">
      <input type="text" id="msg" placeholder="Digite sua mensagem">
      <button onclick="enviarMensagem()">Enviar</button>
    </div>
  </div>
</div>


    

</div>
  <script>
    let idUserAtual = null;
    const idMedLogado = <?= $id_med ?>;

    function abrirChat(idUser, nome) {
        idUserAtual = idUser;
        document.getElementById('foto-cabecalho').src = "/tentativa-1/sistema/funcionalidades/pacientes/mostrar_foto.php?tipo=user&id=" + idUser;
        document.getElementById('titulo-chat').innerText = nome;
        document.getElementById('input-area').style.display = "flex";
        carregarMensagens();
    }

    function enviarMensagem() {
        const msg = document.getElementById('msg').value.trim();
        if (msg === "" || !idUserAtual) return;

        fetch('enviar_mensagem_medico.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_user=${idUserAtual}&mensagem=${encodeURIComponent(msg)}`
        })
        .then(r => r.text())
        .then(res => {
            if (res.trim() === "ok") {
                document.getElementById('msg').value = "";
                carregarMensagens();
            }
        });
    }

    function carregarMensagens() {  
        if (!idUserAtual) return;

        fetch(`buscar_mensagem_medico.php?id_user=${idUserAtual}`)
        .then(response => response.json())
        .then(mensagens => {
            const chat = document.getElementById('chat');
            chat.innerHTML = '';

            mensagens.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('mensagem');

                // Corrected logic: Use the `tipo_remetente` to determine the sender
                if (msg.tipo_remetente === 'med') {
                    div.classList.add('enviada');
                } else {
                    div.classList.add('recebida');
                }
                   const horario=document.createElement('div');
                     horario.classList.add('horario');

  // Aqui você pode ajustar o formato:
                    const data = new Date(msg.data_envio);
                    const horaFormatada = data.toLocaleTimeString('pt-BR', {
                    hour: '2-digit',
                    minute: '2-digit'
                   });
                    horario.textContent = horaFormatada;
                div.innerText = msg.mensagem;
                chat.appendChild(div);
                div.appendChild(horario);
            });

            chat.scrollTop = chat.scrollHeight;
        });
    }

    setInterval(carregarMensagens, 2000);
</script>
</html>