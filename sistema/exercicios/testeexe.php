<?php
// exercicio_pontos_desafio.php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';

// Verifica o tipo de usuário logado e chama a função de autenticação correta
if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    $id = ensureLoggedInUser();
    $role = 'user';
} elseif (isset($_SESSION['id_med']) && !empty($_SESSION['id_med'])) {
    $id = ensureLoggedInMedico();
    $role = 'medico';
} else {
    // Se não houver ninguém logado, redireciona para a página de login do usuário
    header('Location: /tentativa-1/index/login/login_user.php');
    exit();
}

$numeroDePontos = 8;
$pontos = [];
$larguraCanvas = 400;
$alturaCanvas = 500;
$espacamentoMinimo = 60;

for ($i = 0; $i < $numeroDePontos; $i++) {
    $tentativas = 0;
    $posicaoValida = false;
    
    while (!$posicaoValida && $tentativas < 100) {
        $x = rand(30, $larguraCanvas - 30);
        $y = rand(30, $alturaCanvas - 30);
        
        $posicaoValida = true;
        
        foreach ($pontos as $pontoExistente) {
            $distancia = sqrt(pow($x - $pontoExistente['pos_x'], 2) + pow($y - $pontoExistente['pos_y'], 2));
            if ($distancia < $espacamentoMinimo) {
                $posicaoValida = false;
                break;
            }
        }
        
        $tentativas++;
    }
    
    $pontos[] = [
        'numero' => $i + 1,
        'pos_x' => $x,
        'pos_y' => $y
    ];
}

$pontosEmbaralhados = $pontos;
shuffle($pontosEmbaralhados);

$pontosJson = json_encode($pontos);
$pontosEmbaralhadosJson = json_encode($pontosEmbaralhados);
$id_ex = isset($_GET['id_ex']) ? intval($_GET['id_ex']) : 1;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ligar os Pontos - Desafio 🚀</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(#e8f4f8, #c1d5e0);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        h1 { color: #2c3e50; margin-bottom: 10px; font-size: 28px; }
        .dificuldade { color: #e74c3c; font-weight: bold; margin-bottom: 20px; font-size: 16px; }
        #canvasJogo {
            border: 3px solid #34495e;
            background-color: #FFF;
            margin: 20px auto;
            display: block;
            border-radius: 10px;
            cursor: pointer;
        }
        .feedback {
            font-size: 20px;
            font-weight: bold;
            margin: 15px 0;
            min-height: 30px;
            padding: 10px;
            border-radius: 10px;
        }
        .acerto { color: #27ae60; background: #d4edda; }
        .erro { color: #e74c3c; background: #f8d7da; }
        .info { color: #3498db; background: #d1ecf1; }
        .controles { display: flex; gap: 15px; justify-content: center; margin: 15px 0; flex-wrap: wrap; }
        button {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            font-weight: bold;
        }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0,0,0,0.15); }
        #btnReiniciar { background-color: #e74c3c; }
        .btn-progresso { margin: 5px; }
        .progresso { margin: 15px 0; font-weight: bold; color: #2c3e50; }
        .timer { font-size: 18px; color: #e74c3c; font-weight: bold; margin: 10px 0; }
        #progressoContainer { display: none; margin-top: 15px; }
        .confirmacao {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(46, 204, 113, 0.9);
            color: white;
            padding: 15px 25px;
            border-radius: 30px;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            animation: fadeInOut 3s forwards;
            display: none;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; top: 0; }
            10% { opacity: 1; top: 20px; }
            90% { opacity: 1; top: 20px; }
            100% { opacity: 0; top: 0; display: none; }
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>🔗 Ligar os Pontos - Modo Desafio 🚀</h1>
        <div class="dificuldade">Nível: Avançado (<?php echo $numeroDePontos; ?> pontos)</div>
        
        <div class="timer" id="timer">Tempo: 0s</div>
        <div class="progresso" id="progresso">Progresso: 0/<?php echo $numeroDePontos; ?></div>
        
        <canvas id="canvasJogo" width="400" height="500"></canvas>
        
        <div id="feedback" class="feedback"></div>
        <div id="confirmacaoMsg" class="confirmacao"></div>
        <div class="controles">
            <button id="btnReiniciar" onclick="reiniciarJogo()">🔄 Reiniciar</button>
        </div>
        
        <!-- BOTÕES DE PROGRESSO (APENAS QUANDO CONCLUÍDO) -->
        <div id="progressoContainer" class="controles">
            <button class="btn-progresso" onclick="marcarProgresso('sozinho')" style="background-color: #27ae60;">✅ Fez Sozinho</button>
            <button class="btn-progresso" onclick="marcarProgresso('ajuda')" style="background-color: #f39c12;">🟠 Fez com Ajuda</button>
            <button class="btn-progresso" onclick="marcarProgresso('nao-fez')" style="background-color: #e74c3c;">❌ Não Fez</button>
        </div>
    </div>

    <script>
        const pontos = <?php echo $pontosJson; ?>;
        const pontosEmbaralhados = <?php echo $pontosEmbaralhadosJson; ?>;
        const EX_ID = <?php echo $id_ex; ?>;
        const userRole = '<?php echo $role; ?>'; // Passa o tipo de usuário para o JS

        const canvas = document.getElementById('canvasJogo');
        const ctx = canvas.getContext('2d');
        const feedbackEl = document.getElementById('feedback');
        const progressoEl = document.getElementById('progresso');
        const timerEl = document.getElementById('timer');
        const progressoContainer = document.getElementById('progressoContainer');

        const raioPonto = 18;
        const corPontoNormal = '#3498db';
        const corPontoAcerto = '#2ecc71';
        const corPontoErro = '#e74c3c';
        const corLinhaAcerto = '#2ecc71';
        const corLinhaErro = '#e74c3c';
        const larguraLinha = 4;

        let sequenciaCorreta = 0;
        let pontosClicados = [];
        let jogoConcluido = false;
        let aguardandoReset = false;
        let tempoInicio = null;
        let timerInterval = null;

        function desenharTela() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            if (pontosClicados.length > 1) {
                ctx.beginPath();
                ctx.lineWidth = larguraLinha;
                for (let i = 0; i < pontosClicados.length - 1; i++) {
                    const pontoAtual = pontosClicados[i];
                    const proximoPonto = pontosClicados[i + 1];
                    ctx.moveTo(pontoAtual.pos_x, pontoAtual.pos_y);
                    ctx.strokeStyle = (i + 1 < sequenciaCorreta) ? corLinhaAcerto : corLinhaErro;
                    ctx.lineTo(proximoPonto.pos_x, proximoPonto.pos_y);
                    ctx.stroke();
                }
            }

            pontosEmbaralhados.forEach((ponto) => {
                const pontoOriginal = pontos.find(p => p.numero === ponto.numero);
                let corDoPonto = corPontoNormal;
                const pontoFoiClicado = pontosClicados.some(p => p.numero === ponto.numero);
                if (pontoFoiClicado) {
                    corDoPonto = (ponto.numero <= sequenciaCorreta) ? corPontoAcerto : corPontoErro;
                }
                ctx.beginPath();
                ctx.arc(pontoOriginal.pos_x, pontoOriginal.pos_y, raioPonto, 0, Math.PI * 2);
                ctx.fillStyle = corDoPonto;
                ctx.fill();
                ctx.strokeStyle = '#2980b9';
                ctx.lineWidth = 2;
                ctx.stroke();
                ctx.font = 'bold 16px Arial';
                ctx.fillStyle = 'white';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(ponto.numero, pontoOriginal.pos_x, pontoOriginal.pos_y);
            });

            progressoEl.textContent = `Progresso: ${sequenciaCorreta}/${pontos.length}`;
        }

        function verificarClique(x, y) {
            if (jogoConcluido || aguardandoReset) return;
            pontosEmbaralhados.forEach((ponto) => {
                const pontoOriginal = pontos.find(p => p.numero === ponto.numero);
                const distancia = Math.sqrt(Math.pow(x - pontoOriginal.pos_x, 2) + Math.pow(y - pontoOriginal.pos_y, 2));
                if (distancia < raioPonto) {
                    pontosClicados.push(ponto);
                    if (ponto.numero === sequenciaCorreta + 1) {
                        sequenciaCorreta++;
                        feedbackEl.textContent = '';
                        feedbackEl.className = 'feedback';
                        if (sequenciaCorreta === pontos.length) {
                            const tempoTotal = Math.floor((Date.now() - tempoInicio) / 1000);
                            feedbackEl.textContent = `🎉 Parabéns! Você completou em ${tempoTotal} segundos!`;
                            feedbackEl.classList.add('acerto');
                            jogoConcluido = true;
                            clearInterval(timerInterval);
                            // MOSTRA OS BOTÕES DE PROGRESSO QUANDO CONCLUÍDO
                            progressoContainer.style.display = 'flex';
                        }
                    } else {
                        feedbackEl.textContent = 'Ordem Incorreta! Tente novamente.';
                        feedbackEl.classList.add('erro');
                        aguardandoReset = true;
                        desenharTela();
                        setTimeout(() => { reiniciarJogo(); }, 1500);
                    }
                    desenharTela();
                }
            });
        }

        function reiniciarJogo() {
            sequenciaCorreta = 0;
            pontosClicados = [];
            jogoConcluido = false;
            aguardandoReset = false;
            feedbackEl.textContent = '';
            feedbackEl.className = 'feedback';
            progressoContainer.style.display = 'none'; // ESCONDE BOTÕES NO REINÍCIO
            if (timerInterval) clearInterval(timerInterval);
            tempoInicio = Date.now();
            timerInterval = setInterval(updateTimer, 1000);
            updateTimer();
            desenharTela();
        }

        function updateTimer() {
            if (tempoInicio && !jogoConcluido) {
                const segundos = Math.floor((Date.now() - tempoInicio) / 1000);
                timerEl.textContent = `Tempo: ${segundos}s`;
            }
        }

        async function marcarProgresso(tipo) {
            // Se for médico, apenas mostra a mensagem e fecha a janela
            if (userRole === 'medico') {
                mostrarConfirmacao('Fechando a aba...', false);
                setTimeout(() => { try { window.close(); } catch(e) {} }, 1000);
                return;
            }

            // Se for usuário, executa a lógica de salvamento
            if (userRole === 'user') {
                const mensagens = {
                    'sozinho': '✅ Registrado: Fez sozinho!',
                    'ajuda': '🟠 Registrado: Fez com ajuda!',
                    'nao-fez': '❌ Registrado: Não conseguiu fazer.'
                };
                
                mostrarConfirmacao(mensagens[tipo] || 'Progresso registrado!');
                
                try {
                    const res = await fetch('/tentativa-1/sistema/funcionalidades/pacientes/registrar_progresso.php', {
                        method: 'POST',
                        credentials: 'include',
                        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                        body: JSON.stringify({ id_ex: EX_ID, autonomia: tipo })
                    });
                    const json = await res.json();
                    console.log('registrar_progresso response', json);
                    if (json && json.success) {
                        mostrarConfirmacao('Progresso salvo com sucesso!');
                        
                        setTimeout(() => { try { window.close(); } catch(e) {} }, 1500);
                    }
                } catch (err) {
                    console.error('erro ao registrar progresso', err);
                    feedbackEl.textContent = 'Erro ao salvar progresso. Tente novamente.';
                    feedbackEl.classList.add('erro');
                }
            }
        }
            function mostrarConfirmacao(mensagem, isError = false) {
            const confirmacao = document.getElementById('confirmacaoMsg');
            confirmacao.textContent = mensagem;
            confirmacao.style.backgroundColor = isError ? 'rgba(231, 76, 60, 0.9)' : 'rgba(46, 204, 113, 0.9)';
            confirmacao.style.display = 'block';
                
    // Reiniciar a animação
    confirmacao.style.animation = 'none';
    setTimeout(() => {
        confirmacao.style.animation = 'fadeInOut 3s forwards';
    }, 10);
}
            canvas.addEventListener('click', (evento) => {
            const rect = canvas.getBoundingClientRect();
            const x = evento.clientX - rect.left;
            const y = evento.clientY - rect.top;
            verificarClique(x, y);
        });

        window.onload = () => { 
            reiniciarJogo();
            progressoContainer.style.display = 'none'; // INICIALMENTE ESCONDIDO
        };
    </script>
</body>

<div style="position: absolute; top: 950px; left: 20px;">
    <a href="javascript:void(0);" 
        onclick="window.close();" 
        style="display:inline-block; padding:10px 16px; background:#168686; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">
        ⬅ Voltar
    </a>
    </a>
</div>
</html>
