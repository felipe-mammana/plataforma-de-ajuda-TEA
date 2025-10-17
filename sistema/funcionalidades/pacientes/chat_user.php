<?php
require_once  'C:xampp/htdocs/tentativa-1//helpers.php';
$id_user = ensureLoggedInUser();

include  "C:xampp/htdocs/tentativa-1//conexao.php"; 



// pega informações do usuário logado (nome + foto)
$sql_user = "SELECT nome FROM tb_user WHERE id_user = ?";
$stmt_user = $cone->prepare($sql_user);
$stmt_user->bind_param("i", $id_user);
$stmt_user->execute();
$res_user = $stmt_user->get_result()->fetch_assoc();
$nome_user = $res_user['nome'] ?? "Usuário";
$stmt_user->close();

// pega todos os médicos cadastrados
$sql = "SELECT id_med, nome FROM tb_medico";
$res = $cone->query($sql);
$medicos = [];
while ($row = $res->fetch_assoc()) {
    $medicos[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Chat Usuário</title>
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
  margin-left: 240px;
  flex: 1;
  display: flex;
  height: 100vh;
}

/* Lista de médicos */
#medicos {
  width: 260px;
  background: #fff;
  border-right: 1px solid #e0e0e0;
  overflow-y: auto;
  padding-top: 20px;
}
#medicos div {
  padding: 14px 18px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  display: flex;
  align-items: center;
  gap: 12px;
  transition: background 0.2s;
}
#medicos div:hover {
  background: #f9f9f9;
}
#medicos img {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
}

/* Área do chat */
#chat-area {
  flex: 1;
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
  padding: 25px 40px;   /* mais espaço lateral para um chat mais largo */
  overflow-y: auto;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.mensagem {
  padding: 12px 20px;
  border-radius: 20px;
  max-width: 75%;       /* mensagens mais largas */
  word-wrap: break-word;
  font-size: 15.5px;
  line-height: 1.4;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}
.enviada {
  background: var(--msg-user);
  margin-left: auto;
  border-top-right-radius: 4px; /* balão estilo mensageiro */
}
.recebida {
  background: var(--msg-med);
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
      <img class="logo-altus" src="Logo.png" alt="Logo Altus">
    </div>

    <ul>
      <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/perfil_user.php">👤 Perfil</a></li>
      <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/calendario.php">📅 Agenda</a></li>
      <li><a href="/tentativa-1/sistema/pacientes/sistema.php">⬅ Voltar</a></li>
    </ul>
  </div> <!-- 👈 Faltava este fechamento -->

  <!-- ===== CONTEÚDO PRINCIPAL ===== -->
  <div class="main">
    <div id="medicos">
      <?php foreach($medicos as $m): ?>
        <div onclick="abrirChat(<?= $m['id_med'] ?>, '<?= htmlspecialchars($m['nome']) ?>')">
          <img src="mostrar_foto.php?tipo=medico&id=<?= $m['id_med'] ?>" alt="Foto Médico">
          <span><?= htmlspecialchars($m['nome']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <div id="chat-area">
      <div id="chat-header">
        <img id="foto-cabecalho" src="mostrar_foto.php?tipo=user&id=<?= $id_user ?>" alt="Foto Usuário">
        <h3 id="titulo-chat">Selecione um médico</h3>
      </div>

      <div id="chat"></div>

      <div id="input-area" style="display:none;">
        <input type="text" id="msg" placeholder="Digite sua mensagem">
        <button onclick="enviarMensagem()">Enviar</button>
      </div>
    </div>
  </div>
</body>

<script>
    let idMedAtual = null;
    const idUserLogado = <?= $id_user ?>;

    function abrirChat(idMed, nome) {
        idMedAtual = idMed;
        document.getElementById('foto-cabecalho').src = "mostrar_foto.php?tipo=medico&id=" + idMed;
        document.getElementById('titulo-chat').innerText = "Dr. " + nome;
        document.getElementById('input-area').style.display = "flex";
        carregarMensagens();
    }

    function enviarMensagem() {
        const msg = document.getElementById('msg').value.trim();
        if (msg === "" || !idMedAtual) return;

        fetch('/tentativa-1/sistema/funcionalidades/pacientes/enviar_mensagem_user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id_med=${idMedAtual}&mensagem=${encodeURIComponent(msg)}`
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
        if (!idMedAtual) return;

        fetch(`buscar_mensagem_user.php?id_med=${idMedAtual}`)
        .then(response => response.json())
        .then(mensagens => {
            const chat = document.getElementById('chat');
            chat.innerHTML = '';

            mensagens.forEach(msg => {
                const div = document.createElement('div');
                div.classList.add('mensagem');

                // Corrected logic: Use the `tipo_remetente` to determine the sender
                if (msg.tipo_remetente === 'user') {
                    div.classList.add('enviada');
                } else {
                    div.classList.add('recebida');
                }
                 const horario=document.createElement('div');
                  horario.classList.add('horario');

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
</body>
</html>