<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agenda - Altus</title>
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/pt-br.js"></script>
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

    .calendar-container {
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    .calendar-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding: 0 10px;
    }

    .calendar-title {
      font-size: 28px;
      font-weight: 600;
      color: var(--primary-dark);
      margin: 0;
    }

    /* Estilização do FullCalendar */
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

    .fc .fc-button:active {
      transform: translateY(0);
    }

    .fc .fc-daygrid-day-number {
      color: var(--text-dark);
      font-weight: 500;
    }

    .fc .fc-day-today {
      background-color: rgba(0, 193, 160, 0.1) !important;
    }

    .fc-event {
      background-color: var(--primary);
      border: none;
      border-radius: 6px;
      padding: 4px 8px;
      font-size: 0.9em;
    }

    .fc-event:hover {
      background-color: var(--primary-dark);
    }

    /* Consultas do dia */
    .consultas-container {
      margin-top: 30px;
    }

    .consultas-title {
      font-size: 22px;
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 20px;
      padding: 0 10px;
    }

    .consultas-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .consulta-card {
      background: var(--card);
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      border-left: 4px solid var(--accent);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .consulta-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    .consulta-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 15px;
    }

    .consulta-data {
      font-size: 16px;
      font-weight: 600;
      color: var(--primary);
      margin: 0;
    }

    .consulta-horario {
      font-size: 14px;
      color: var(--text-light);
      background: rgba(0, 193, 160, 0.1);
      padding: 4px 10px;
      border-radius: 20px;
      font-weight: 500;
    }

    .consulta-medico {
      font-size: 18px;
      font-weight: 600;
      color: var(--text-dark);
      margin: 10px 0;
    }

    .consulta-motivo {
      color: var(--text-light);
      margin: 10px 0;
      line-height: 1.5;
    }

    .consulta-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }

    .btn {
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      font-size: 14px;
    }

    .btn-primary {
      background: var(--primary);
      color: white;
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    .btn-outline {
      background: transparent;
      color: var(--primary);
      border: 1px solid var(--primary);
    }

    .btn-outline:hover {
      background: var(--primary);
      color: white;
    }

    .no-consultas {
      text-align: center;
      padding: 40px;
      background: var(--card);
      border-radius: 16px;
      color: var(--text-light);
    }

    .no-consultas h3 {
      color: var(--text-dark);
      margin-bottom: 10px;
    }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      backdrop-filter: blur(4px);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: var(--card);
      padding: 30px;
      border-radius: 16px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.2);
      position: relative;
    }

    .close {
      position: absolute;
      top: 15px;
      right: 20px;
      font-size: 24px;
      cursor: pointer;
      color: var(--text-light);
      transition: color 0.3s ease;
    }

    .close:hover {
      color: var(--error);
    }

    .modal-title {
      font-size: 24px;
      font-weight: 600;
      color: var(--primary-dark);
      margin-bottom: 25px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-dark);
    }

    .form-control {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 16px;
      transition: border-color 0.3s ease;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px rgba(0, 193, 160, 0.1);
    }

    .btn-submit {
      width: 100%;
      padding: 12px;
      background: var(--primary);
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      background: var(--primary-dark);
      transform: translateY(-2px);
    }

    /* Responsividade */
    @media (max-width: 768px) {
      .calendar-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
      }
      
      .consultas-grid {
        grid-template-columns: 1fr;
      }
      
      .modal-content {
        width: 95%;
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
<div class="sidebar">
    <div class="brand">
      <img class="logo-altus" src="/tentativa-1/sistema/pacientes/icons/logo.png" alt="Logo Altus">
    </div>
    <ul>
  
      
      <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/perfil_user.php">👤 Perfil</a></li>
       <li><a href="/tentativa-1/sistema/funcionalidades/pacientes/calendario.php">📅 Agenda </a></li>
      <li onclick="window.location.href='/tentativa-1/sistema/pacientes/sistema.php'">⬅ Voltar</li>
    </ul>
  </div>


  <!-- Conteúdo principal -->
  <div class="main">
    <div class="calendar-container">
      <div class="calendar-header">
        <h1 class="calendar-title">📅 Minha Agenda</h1>
      </div>

      <div id="calendar"></div>

      <div class="consultas-container">
        <h2 class="consultas-title">Consultas do Dia Selecionado</h2>
        <div id="consultasDia"></div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div id="modal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="fecharModal()">&times;</span>
      <h3 class="modal-title">Agendar Nova Consulta</h3>
      <form method="post" action="/tentativa-1/sistema/funcionalidades/pacientes/agendar.php">
        <input type="hidden" name="etapa" value="marcar">
        <input type="hidden" name="data_consulta" id="data_consulta">
        
        <div class="form-group">
          <label>Horário:</label>
          <input type="time" name="horario" class="form-control" required>
        </div>
        
        <div class="form-group">
          <label>Médico:</label>
          <select name="id_med" class="form-control" required>
            <?php
            include("C:xampp/htdocs/tentativa-1/conexao.php");
            $res = mysqli_query($cone, "SELECT id_med, nome FROM tb_medico");
            while($row = mysqli_fetch_assoc($res)) {
                echo "<option value='{$row['id_med']}'>{$row['nome']}</option>";
            }
            ?>
          </select>
        </div>
        
        <div class="form-group">
          <label>Motivo:</label>
          <input type="text" name="motivo" class="form-control" required>
        </div>
        
        <button type="submit" class="btn-submit">Agendar Consulta</button>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      var calendarEl = document.getElementById('calendar');
      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        events: '/tentativa-1/sistema/funcionalidades/pacientes/eventos.php',
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
          var eventosDoDia = calendar.getEvents().filter(ev => 
            ev.startStr.startsWith(info.dateStr)
          );

          let consultasHTML = '';
          if (eventosDoDia.length > 0) {
            consultasHTML = '<div class="consultas-grid">';
            eventosDoDia.forEach(ev => {
              let inicio = ev.startStr.replace(/[-:]/g, "").split(".")[0];
              let fimDate = new Date(ev.start.getTime() + 60*60*1000);
              let fim = fimDate.toISOString().replace(/[-:]/g, "").split(".")[0];
              
              let linkGoogle = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" 
                + encodeURIComponent(ev.title) 
                + "&dates=" + inicio + "/" + fim
                + "&details=" + encodeURIComponent("Consulta com " + ev.extendedProps.nome_med);

              consultasHTML += `
                <div class="consulta-card">
                  <div class="consulta-header">
                    <h3 class="consulta-data">${info.dateStr}</h3>
                    <span class="consulta-horario">${ev.extendedProps.horario}</span>
                  </div>
                  <h4 class="consulta-medico">Dr(a). ${ev.extendedProps.nome_med}</h4>
                  <p class="consulta-motivo">${ev.title}</p>
                  <div class="consulta-actions">
                    <a href="${linkGoogle}" target="_blank" class="btn btn-outline">📅 Google Agenda</a>
                  </div>
                </div>
              `;
            });
            consultasHTML += '</div>';
          } else {
            consultasHTML = `
              <div class="no-consultas">
                <h3>Nenhuma consulta agendada para ${info.dateStr}</h3>
                <p>Que tal agendar uma consulta para este dia?</p>
                <button class="btn btn-primary" onclick="abrirModal('${info.dateStr}')">Agendar Consulta</button>
              </div>
            `;
          }

          document.getElementById("consultasDia").innerHTML = consultasHTML;
        }
      });
      calendar.render();
    });

    function abrirModal(data) {
      document.getElementById("modal").style.display = "flex";
      document.getElementById("data_consulta").value = data;
    }

    function fecharModal() {
      document.getElementById("modal").style.display = "none";
    }

    // Fechar modal clicando fora dele
    window.onclick = function(event) {
      const modal = document.getElementById('modal');
      if (event.target === modal) {
        fecharModal();
      }
    }
  </script>
</body>
</html>