<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
require_once 'C:xampp/htdocs/tentativa-1/conexao.php';

if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    $id = ensureLoggedInUser();
    $role = 'user';
} elseif (isset($_SESSION['id_med']) && !empty($_SESSION['id_med'])) {
    $id = ensureLoggedInMedico();
    $role = 'medico';
} else {
    header('Location: /tentativa-1/index/login/login_user.php');
    exit();
}

$sql_user = ($role === 'user')
    ? "SELECT nome FROM tb_user WHERE id_user = ?"
    : "SELECT nome FROM tb_medico WHERE id_med = ?";

$stmt_user = $cone->prepare($sql_user);
$stmt_user->bind_param("i", $id);
$stmt_user->execute();
$res_user = $stmt_user->get_result()->fetch_assoc();
$nome_user = $res_user['nome'] ?? ($role === 'user' ? "Usuário" : "Médico");
$stmt_user->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Chat da Comunidade</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
  display:flex;
  height:100vh;
  font-family:"Segoe UI",Arial,sans-serif;
  background:#fafaf6;
  color:#333;
}

/* ===== SIDEBAR ===== */
.sidebar{
  width:220px;
  background:#1f8c8c;
  color:#fff;
  display:flex;
  flex-direction:column;
  padding:20px 0;
}
.brand{text-align:center;margin-bottom:30px;}
.brand img.logo-altus{
  width: 40px;
  height: 40px;
  object-fit: contain;
}
.brand h2{font-size:1.4rem;font-weight:600;}
.sidebar ul{list-style:none;padding:0;}
.sidebar li{margin:15px 0;}
.sidebar a{
  color:#fff;
  text-decoration:none;
  padding:10px 20px;
  display:block;
  transition:background .2s;
}
.sidebar a:hover{
  background:rgba(255,255,255,0.15);
  border-radius:8px;
}

/* ===== MAIN CHAT AREA ===== */
.main{
  flex:1;
  display:flex;
  flex-direction:column;
  border-left:1px solid #e0e0e0;
  background:#fdfdf9;
}
#chat-header{
  display:flex;
  align-items:center;
  gap:10px;
  padding:15px 20px;
  border-bottom:1px solid #ddd;
  background:#fff;
}
#chat-header img{
  width:48px;
  height:48px;
  border-radius:50%;
  object-fit:cover;
}
#chat-header h3{
  font-size:1.1rem;
  font-weight:600;
}
#chat{
  flex:1;
  padding:20px;
  overflow-y:auto;
}
.mensagem{
  margin:8px 0;
  padding:10px 14px;
  border-radius:12px;
  max-width:70%;
  line-height:1.4;
  word-wrap:break-word;
}
.enviada{background:#d1ffd1;margin-left:auto;}
.recebida{background:#f1f1f1;margin-right:auto;}
.autor{font-size:12px;color:#555;margin-bottom:2px;}
#input-area{
  display:flex;
  padding:15px 20px;
  border-top:1px solid #ddd;
  background:#fff;
}
#msg{
  flex:1;
  padding:10px;
  border:1px solid #ccc;
  border-radius:8px;
  font-size:14px;
}
button{
  padding:10px 16px;
  margin-left:10px;
  background:#1f8c8c;
  color:#fff;
  border:none;
  border-radius:8px;
  cursor:pointer;
  transition:background .2s;
}
button:hover{background:#177070;}
.horario {
  font-size: 11px;
  color: #777;
  margin-top: 4px;
  text-align: right;
}
</style>
</head>
<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
  <div class="brand">
    <img class="logo-altus" src="\tentativa-1\sistema\funcionalidades\pacientes\Logo.png" alt="Logo Altus">
  </div>
  <?php if ($role === 'medico'): ?>
    <a href="/tentativa-1/sistema/medicos/menu_med.php">⬅ Voltar</a>
  <?php else: ?>
    <a href="/tentativa-1/sistema/pacientes/sistema.php">⬅ Voltar</a>
  <?php endif; ?>
</li>
  </ul>
</div>

<!-- ===== MAIN CHAT ===== -->
<div class="main">
  <div id="chat-header">
    <img src="/tentativa-1/sistema/funcionalidades/pacientes/mostrar_foto.php?tipo=<?= $role ?>&id=<?= $id ?>" alt="Foto Usuário">
    <h3>Olá <?= htmlspecialchars($nome_user) ?> - Chat da Comunidade</h3>
  </div>

  <div id="chat"></div>

  <div id="input-area">
    <input type="text" id="msg" placeholder="Digite sua mensagem">
    <button onclick="enviarMensagem()">Enviar</button>
  </div>
</div>

<script>
const currentId   = <?= json_encode($id) ?>;
const currentRole = <?= json_encode($role) ?>;

function enviarMensagem(){
  const msgEl=document.getElementById('msg');
  const msg=msgEl.value.trim();
  if(msg==="")return;
  fetch('/tentativa-1/sistema/funcionalidades/comunidade/enviar_mensagem_comunidade.php',{
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:"mensagem="+encodeURIComponent(msg)
  })
  .then(r=>r.text())
  .then(res=>{
    if(res.trim()==="ok"){msgEl.value="";carregarMensagens();}
    else{console.error('Erro ao enviar:',res);}
  })
  .catch(err=>console.error('fetch enviar erro',err));
}

function carregarMensagens(){
  fetch('/tentativa-1/sistema/funcionalidades/comunidade/buscar_mensagem_comunidade.php')
  .then(r=>r.json())
  .then(mensagens=>{
    const chat=document.getElementById('chat');
    chat.innerHTML='';
    mensagens.forEach(msg=>{
      const div=document.createElement('div');
      div.classList.add('mensagem');
      if(msg.role_autor===currentRole && msg.id_autor==currentId){
        div.classList.add('enviada');
      }else{
        div.classList.add('recebida');
      }
      const autor=document.createElement('div');
      autor.classList.add('autor');
      autor.textContent=msg.nome;
      const texto=document.createElement('div');
      texto.textContent=msg.mensagem;
      const horario=document.createElement('div');
      horario.classList.add('horario');

      const data = new Date(msg.data_envio);
      const horaFormatada = data.toLocaleTimeString('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
  });
  horario.textContent = horaFormatada;
      div.appendChild(autor);
      div.appendChild(texto);
      chat.appendChild(div);
      div.appendChild(horario);
    });
    chat.scrollTop=chat.scrollHeight;
  })
  .catch(err=>console.error('fetch carregar erro',err));
}

setInterval(carregarMensagens,2000);
carregarMensagens();
</script>
</body>
</html>
