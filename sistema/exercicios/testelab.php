<?php

require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
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

$labirinto = [
    [0,1,1,1,1,1,1,1,1,1],
    [0,0,0,0,1,0,0,0,0,1],
    [1,1,1,0,1,0,1,1,0,1],
    [1,0,0,0,0,0,1,0,0,1],
    [1,0,1,1,1,1,1,0,1,1],
    [1,0,1,0,0,0,0,0,1,1],
    [1,0,1,0,1,1,1,1,1,1],
    [1,0,1,0,0,0,0,0,0,1],
    [1,0,0,0,1,1,1,1,0,1],
    [1,1,1,1,1,1,1,1,0,0]
];

$labirintoJson = json_encode($labirinto);
$inicio = ['x' => 0, 'y' => 0]; 
$fim = ['x' => 9, 'y' => 9];    
$id_ex = isset($_GET['id_ex']) ? intval($_GET['id_ex']) : 0;
$inicioJson = json_encode($inicio);
$fimJson = json_encode($fim);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labirinto Difícil - TCC TEA</title>
    <style>
        body { font-family: 'Arial Rounded MT Bold', 'Arial', sans-serif; text-align: center; background-color: #e8f4f8; padding: 20px; }
        h1 { color: #2c3e50; }
        #canvasLabirinto { border: 3px solid #34495e; background-color: #ecf0f1; margin: 20px auto; display: block; border-radius: 5px; }
        .feedback { font-size: 24px; font-weight: bold; margin: 15px; min-height: 30px; }
        .acerto { color: #27ae60; }
        .erro { color: #c0392b; }
        .instrucao { color: #7f8c8d; margin-bottom: 20px; }
        button { background-color: #e74c3c; color: white; border: none; padding: 12px 25px; font-size: 18px; margin: 10px; cursor: pointer; border-radius: 50px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; }
        button:hover { background-color: #e74c3c; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0,0,0,0.15); }
        button:active { transform: translateY(0); }
        
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
        
        #progressoContainer { margin-top: 30px; display: none; }
        .btn-progresso { margin: 5px; }
    </style>
</head>
<body>


    <h1>🚀 Escape do Labirinto Difícil! 🚀</h1>
    <p class="instrucao">Leve o astronauta (🚶) até a nave (🚀) sem encostar nas paredes!</p>
    <canvas id="canvasLabirinto" width="500" height="500"></canvas>
    <div id="feedback" class="feedback"></div>
    <button onclick="reiniciarJogo()">🔄 Reiniciar</button>
    
    
    <div id="confirmacaoMsg" class="confirmacao"></div>

    <div id="progressoContainer">
        <button class="btn-progresso" onclick="marcarProgresso('sozinho')" style="background-color: #27ae60;">✅ Fez Sozinho</button>
        <button class="btn-progresso" onclick="marcarProgresso('ajuda')" style="background-color: #f39c12;">🟠 Fez com Ajuda</button>
        <button class="btn-progresso" onclick="marcarProgresso('nao-fez')" style="background-color: #e74c3c;">❌ Não Fez</button>
    </div>

    <script>
        const labirinto = <?php echo $labirintoJson; ?>;
        const inicio = <?php echo $inicioJson; ?>;
        const fim = <?php echo $fimJson; ?>;
        const canvas = document.getElementById('canvasLabirinto');
        const ctx = canvas.getContext('2d');
        const feedbackEl = document.getElementById('feedback');
        const confirmacaoMsg = document.getElementById('confirmacaoMsg');
        const progressoContainer = document.getElementById('progressoContainer');
        const tamanhoCelula = canvas.width / labirinto[0].length;
        const corCaminho = '#ecf0f1';
        const corParede = '#34495e';
        const corInicio = '#2ecc71';
        const corFim = '#e74c3c';
        const corRastro = '#3498db';
        const larguraLinha = 4;
        let posicaoAtual = { ...inicio };
        let caminhoPercorrido = [];
        let jogoConcluido = false;
        let mousePressionado = false;

        const EX_ID = <?php echo $id_ex; ?>;
        const userRole = '<?php echo $role; ?>'; 

        function desenharLabirinto() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let y = 0; y < labirinto.length; y++) {
                for (let x = 0; x < labirinto[y].length; x++) {
                    ctx.beginPath();
                    ctx.rect(x * tamanhoCelula, y * tamanhoCelula, tamanhoCelula, tamanhoCelula);
                    ctx.fillStyle = labirinto[y][x] === 1 ? corParede : corCaminho;
                    ctx.fill();
                    ctx.strokeStyle = '#bdc3c7';
                    ctx.stroke();
                }
            }
            ctx.beginPath();
            ctx.arc((inicio.x + 0.5) * tamanhoCelula, (inicio.y + 0.5) * tamanhoCelula, tamanhoCelula / 3, 0, Math.PI * 2);
            ctx.fillStyle = corInicio;
            ctx.fill();
            ctx.font = 'bold 18px Arial';
            ctx.fillStyle = 'white';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('🚶', (inicio.x + 0.5) * tamanhoCelula, (inicio.y + 0.5) * tamanhoCelula);
            ctx.beginPath();
            ctx.arc((fim.x + 0.5) * tamanhoCelula, (fim.y + 0.5) * tamanhoCelula, tamanhoCelula / 3, 0, Math.PI * 2);
            ctx.fillStyle = corFim;
            ctx.fill();
            ctx.fillText('🚀', (fim.x + 0.5) * tamanhoCelula, (fim.y + 0.5) * tamanhoCelula);
            if (caminhoPercorrido.length > 1) {
                ctx.beginPath();
                ctx.lineWidth = larguraLinha;
                ctx.strokeStyle = corRastro;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.moveTo((caminhoPercorrido[0].x + 0.5) * tamanhoCelula, (caminhoPercorrido[0].y + 0.5) * tamanhoCelula);
                for (let i = 1; i < caminhoPercorrido.length; i++) {
                    ctx.lineTo((caminhoPercorrido[i].x + 0.5) * tamanhoCelula, (caminhoPercorrido[i].y + 0.5) * tamanhoCelula);
                }
                ctx.stroke();
            }
        }

        function coordenadasParaPosicao(x, y) {
            const rect = canvas.getBoundingClientRect();
            const mouseX = x - rect.left;
            const mouseY = y - rect.top;
            return {
                x: Math.floor(mouseX / tamanhoCelula),
                y: Math.floor(mouseY / tamanhoCelula)
            };
        }

        function verificarMovimento(x, y) {
            if (jogoConcluido) return;

            const novaPosicao = coordenadasParaPosicao(x, y);

            if (novaPosicao.x < 0 || novaPosicao.x >= labirinto[0].length || novaPosicao.y < 0 || novaPosicao.y >= labirinto.length) {
                return;
            }

            if (labirinto[novaPosicao.y][novaPosicao.x] === 1) {
                feedbackEl.textContent = 'Ops! Encostou na parede! 😵';
                feedbackEl.className = 'feedback erro';
                reiniciarJogo();
                return;
            }

            const ultimaPosValida = caminhoPercorrido[caminhoPercorrido.length - 1];
            const diffX = Math.abs(novaPosicao.x - ultimaPosValida.x);
            const diffY = Math.abs(novaPosicao.y - ultimaPosValida.y);

            const movimentoValido = (diffX === 1 && diffY === 0) || (diffY === 1 && diffX === 0);

            if (!movimentoValido) {
                return;
            }

            caminhoPercorrido.push({ ...novaPosicao });
            posicaoAtual = { ...novaPosicao };

            if (posicaoAtual.x === fim.x && posicaoAtual.y === fim.y) {
                feedbackEl.textContent = 'Missão Cumprida! Astronauta salvo! 🎉';
                feedbackEl.className = 'feedback acerto';
                jogoConcluido = true;
                progressoContainer.style.display = 'block';
            }

            desenharLabirinto();
        }

        function reiniciarJogo() {
            setTimeout(() => {
                posicaoAtual = { ...inicio };
                caminhoPercorrido = [{ ...inicio }];
                jogoConcluido = false;
                feedbackEl.textContent = 'Vamos lá, tente novamente!';
                feedbackEl.className = 'feedback';
                progressoContainer.style.display = 'none';
                desenharLabirinto();
            }, 1000);
        }

        async function marcarProgresso(tipo) {
          
            if (userRole === 'medico') {
                mostrarConfirmacao('Fechando a aba...', false);
                setTimeout(() => { try { window.close(); } catch(e) {} }, 1000);
                return;
            }

            
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
                        if (window.opener && typeof window.opener.markExerciseDone === 'function') {
                            try { window.opener.markExerciseDone(EX_ID); } catch(e) { console.warn(e); }
                        }
                        try { if (window.opener) window.opener.location.reload(); } catch(e) { console.warn(e); }
                        
                        mostrarConfirmacao('Progresso salvo com sucesso!');
                        
                        setTimeout(() => { 
                            try { window.close(); } catch(e) {
                                console.warn('Não foi possível fechar a janela automaticamente');
                                mostrarConfirmacao('Você pode fechar esta janela agora.');
                            } 
                        }, 2500);
                    }
                } catch (err) {
                    console.error('erro ao registrar progresso', err);
                    mostrarConfirmacao('Erro ao salvar progresso. Tente novamente.', true);
                }
            }
        }
        
        function mostrarConfirmacao(mensagem, isError = false) {
            const confirmacao = document.getElementById('confirmacaoMsg');
            confirmacao.textContent = mensagem;
            confirmacao.style.backgroundColor = isError ? 'rgba(231, 76, 60, 0.9)' : 'rgba(46, 204, 113, 0.9)';
            confirmacao.style.display = 'block';
            
            confirmacao.style.animation = 'none';
            setTimeout(() => {
                confirmacao.style.animation = 'fadeInOut 3s forwards';
            }, 10);
        }
        
        canvas.addEventListener('mousedown', (e) => {
            if (!jogoConcluido) {
                mousePressionado = true;
                verificarMovimento(e.clientX, e.clientY);
            }
        });
        canvas.addEventListener('mousemove', (e) => {
            if (mousePressionado && !jogoConcluido) {
                verificarMovimento(e.clientX, e.clientY);
            }
        });
        canvas.addEventListener('mouseup', () => {
            mousePressionado = false;
        });
        canvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            if (!jogoConcluido) {
                mousePressionado = true;
                const touch = e.touches[0];
                verificarMovimento(touch.clientX, touch.clientY);
            }
        });
        canvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            if (mousePressionado && !jogoConcluido) {
                const touch = e.touches[0];
                verificarMovimento(touch.clientX, touch.clientY);
            }
        });
        canvas.addEventListener('touchend', () => {
            mousePressionado = false;
        });
        window.onload = () => {
            caminhoPercorrido.push({ ...inicio });
            desenharLabirinto();
            progressoContainer.style.display = 'none';
        };
    </script>
    <div style="position: absolute; top: 950px; left: 20px;">
        <a href="javascript:void(0);" 
            onclick="window.close();" 
            style="display:inline-block; padding:10px 16px; background:#168686; color:#fff; text-decoration:none; border-radius:8px; font-weight:600;">
            ⬅ Voltar
        </a>
    </div>
</body>
</html>
