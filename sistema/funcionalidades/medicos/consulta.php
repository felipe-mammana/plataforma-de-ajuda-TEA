<?php
session_start();

// Se não houver login, volta para a tela de login
if (!isset($_SESSION['id_med'])) {
    header("Location: login_med.php");
    exit();
}

include("c:/xampp/htdocs/tentativa-1/conexao.php");

$id_medico = $_SESSION['id_med'];

// Pega todas as consultas
$sql = "SELECT c.id_consulta, 
               c.id_user, 
               u.nome, 
               u.sobrenome, 
               c.data_consulta, 
               c.horario, 
               c.motivo
        FROM tb_consulta AS c
        INNER JOIN tb_user AS u 
            ON c.id_user = u.id_user
        ORDER BY c.data_consulta, c.horario";
$result = mysqli_query($cone, $sql);

$consultas = [];
while ($row = mysqli_fetch_assoc($result)) {
    $nomeCompleto = $row['nome'] . " " . $row['sobrenome'];
    $consultas[] = [
        "id" => $row['id_consulta'],
        "title" => $nomeCompleto . " - " . $row['horario'],
        "start" => $row['data_consulta'],
        "extendedProps" => [
            "usuario" => $nomeCompleto,
            "horario" => $row['horario'],
            "motivo" => $row['motivo']
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Agenda do Médico</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- FullCalendar CSS -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">

  <!-- Fonte e CSS base -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link href="/tentativa-1/sistema/pacientes/css/sistema.css" rel="stylesheet">

  <style>
    :root {
      --primary: #168686;
      --primary-dark: #0e5a59;
      --accent: #00c1a0;
      --bg: #f7f6ed;
      --card: #ffffff;
      --text-dark: #333;
      --text-light: #666;
      --success: #27ae60;
      --warning: #f39c12;
      --error: #e74c3c;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg);
      margin: 0;
      display: flex;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background: var(--primary);
      color: white;
      padding: 20px;
      height: 100vh;
      position: fixed;
      left: 0;
      top: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .sidebar .brand {
      text-align: center;
      margin-bottom: 40px;
    }
    .sidebar .brand img {
      width: 100px;
      margin-bottom: 10px;
    }
    .sidebar h2 {
      font-weight: 600;
      margin: 0;
    }
    .sidebar ul {
      list-style: none;
      padding: 0;
      margin: 0;
      width: 100%;
    }
    .sidebar ul li {
      margin: 15px 0;
      text-align: center;
    }
    .sidebar ul li a {
      color: white;
      text-decoration: none;
      font-weight: 500;
      padding: 10px 15px;
      display: block;
      border-radius: 8px;
      transition: background 0.3s;
    }
    .sidebar ul li a:hover {
      background: var(--primary-dark);
    }

    /* Conteúdo principal */
    .main {
      margin-left: 250px;
      padding: 30px;
      width: 100%;
    }

    .calendar-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .calendar-title {
      font-size: 28px;
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 25px;
    }

    /* FullCalendar custom */
    #calendar {
      background: var(--card);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      border: 1px solid rgba(0,0,0,0.05);
    }
    .fc .fc-toolbar-title {
      font-size: 1.5em;
      font-weight: 600;
      color: var(--primary-dark);
    }
    .fc .fc-button {
      background-color: var(--primary);
      border: none;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }
    .fc .fc-button:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
    }
    .fc .fc-daygrid-day-number {
      color: var(--text-dark);
      font-weight: 500;
    }
    .fc .fc-day-today {
      background-color: rgba(0, 193, 160, 0.1) !important;
    }

    /* Consultas do dia */
    #detalhes {
      margin-top: 30px;
      padding: 20px;
      background: var(--card);
      border-radius: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      display: none;
    }
    #detalhes h3 {
      font-size: 22px;
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 15px;
    }
    #btnGoogleAgenda {
      margin-top: 15px;
      padding: 10px 15px;
      background: #4285F4;
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      display: none;
      font-weight: 600;
    }
    #btnGoogleAgenda:hover {
      background: #3367D6;
    }

    /* Destaque do dia */
    .dia-selecionado {
      background: #b3e6ff !important;
      border: 2px solid #0077cc !important;
    }

    @media (max-width: 768px) {
      .sidebar { width: 200px; }
      .main { margin-left: 200px; padding: 15px; }
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
.sidebar .brand {
  display: flex;
  align-items: center;
  justify-content: flex-start;
  gap: 8px;
  margin-bottom: 20px;
  padding: 0 20px;
}
.sidebar .brand img.logo-altus {
  width: 32px;
  height: 32px;
  object-fit: contain;
}
.sidebar h2 { font-size: 20px; font-weight: 600; }

.sidebar ul { list-style: none; padding: 0; }
.sidebar ul li {
  padding: 12px 20px;
  margin: 4px 0;
  cursor: pointer;
  transition: background-color 0.2s;
  color: var(--white);
  font-weight: 600;
}
.sidebar ul li:hover { background: var(--sidebar-dark); }
.sidebar ul li a {
  text-decoration: none;
  color: var(--white);
  display: block;
  width: 100%;
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
      <li><a href="/tentativa-1/sistema/funcionalidades/medicos/pacientes.php">👥 Pacientes</a></li>
      <li><a href="/tentativa-1/sistema/funcionalidades/medicos/perfil.php">👤 Perfil</a></li>
      <li onclick="window.location.href='/tentativa-1/sistema/medicos/menu_med.php'">🚪 Voltar</li>
    </ul>
  </div>

  <!-- Conteúdo principal -->
  <div class="main">
    <div class="calendar-container">
      <h1 class="calendar-title">📅 Agenda do Médico</h1>
      <div id="calendar"></div>
      <div id="detalhes">
        <h3>Consultas do dia <span id="dataSelecionada"></span></h3>
        <div id="listaConsultas"></div>
        <button id="btnGoogleAgenda" onclick="enviarParaGoogleAgenda()">📅 Enviar para Google Agenda</button>
      </div>
    </div>
  </div>

  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
  <script>
    let consultasDoDiaSelecionado = [];
    let dataSelecionadaAtual = '';

    document.addEventListener('DOMContentLoaded', function() {
      let calendarEl = document.getElementById('calendar');
      let eventos = <?php echo json_encode($consultas); ?>;
      let dataSelecionadaAnterior = null;

      let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        events: eventos,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: {
          today: 'Hoje',
          month: 'Mês',
          week: 'Semana',
          day: 'Dia'
        },
        dateClick: function(info) {
          let dataSelecionada = info.dateStr;
          dataSelecionadaAtual = dataSelecionada;

          if (dataSelecionadaAnterior) {
            let cellAntiga = document.querySelector(`[data-date="${dataSelecionadaAnterior}"]`);
            if (cellAntiga) cellAntiga.classList.remove("dia-selecionado");
          }
          let cellNova = document.querySelector(`[data-date="${dataSelecionada}"]`);
          if (cellNova) cellNova.classList.add("dia-selecionado");
          dataSelecionadaAnterior = dataSelecionada;

          consultasDoDiaSelecionado = eventos.filter(ev => ev.start === dataSelecionada);
          document.getElementById('dataSelecionada').innerText = new Date(dataSelecionada).toLocaleDateString("pt-BR");

          let lista = document.getElementById('listaConsultas');
          lista.innerHTML = "";

          if (consultasDoDiaSelecionado.length > 0) {
            consultasDoDiaSelecionado.forEach(c => {
              let div = document.createElement("div");
              div.innerHTML = `<b>${c.extendedProps.usuario}</b> 
                               <br>Horário: ${c.extendedProps.horario} 
                               <br>Motivo: ${c.extendedProps.motivo}<hr>`;
              lista.appendChild(div);
            });
            document.getElementById('btnGoogleAgenda').style.display = 'block';
          } else {
            lista.innerHTML = "<i>Nenhuma consulta neste dia.</i>";
            document.getElementById('btnGoogleAgenda').style.display = 'none';
          }

          document.getElementById('detalhes').style.display = "block";
        }
      });

      calendar.render();
    });

    function enviarParaGoogleAgenda() {
      if (consultasDoDiaSelecionado.length === 0) {
        alert('Não há consultas para enviar.');
        return;
      }

      const consulta = consultasDoDiaSelecionado[0];
      const dataHoraInicio = new Date(dataSelecionadaAtual + 'T' + consulta.extendedProps.horario);
      const dataHoraFim = new Date(dataHoraInicio);
      dataHoraFim.setHours(dataHoraFim.getHours() + 1);

      const formatarParaGoogle = (data) => {
        return data.toISOString().replace(/-|:|\.\d+/g, '');
      };

      const inicioFormatado = formatarParaGoogle(dataHoraInicio);
      const fimFormatado = formatarParaGoogle(dataHoraFim);

      const detalhesEvento = `Consulta com ${consulta.extendedProps.usuario}. Motivo: ${consulta.extendedProps.motivo}`;
      
      const urlGoogleAgenda = [
        'https://calendar.google.com/calendar/r/eventedit',
        '?text=' + encodeURIComponent('Consulta - ' + consulta.extendedProps.usuario),
        '&dates=' + inicioFormatado + '/' + fimFormatado,
        '&details=' + encodeURIComponent(detalhesEvento),
        '&location=' + encodeURIComponent('Consultório'),
        '&sf=true',
        '&output=xml'
      ].join('');

      window.open(urlGoogleAgenda, '_blank');
    }
  </script>
</body>
</html>
