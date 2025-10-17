<?php
// labirinto_corrigido.php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
ensureLoggedIn();

$labirinto = [
    [0, 1, 1, 1, 1, 1],
    [0, 0, 0, 0, 0, 1],
    [1, 1, 1, 1, 0, 1],
    [1, 0, 0, 0, 0, 1],
    [1, 0, 1, 1, 1, 1],
    [1, 0, 0, 0, 0, 0]
];
$labirintoJson = json_encode($labirinto);
$inicio = ['x' => 0, 'y' => 0];
$fim = ['x' => 5, 'y' => 5];
$id_ex = isset($_GET['id_ex']) ? intval($_GET['id_ex']) : 0;
$inicioJson = json_encode($inicio);
$fimJson = json_encode($fim);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Labirinto Corrigido - TCC TEA</title>
    <style>
        body { font-family: 'Arial Rounded MT Bold', 'Arial', sans-serif; text-align: center; background-color: #e8f4f8; padding: 20px; }
        h1 { color: #2c3e50; }
        #canvasLabirinto { border: 3px solid #34495e; background-color: #ecf0f1; margin: 20px auto; display: block; border-radius: 5px; }
        .feedback { font-size: 24px; font-weight: bold; margin: 15px; min-height: 30px; }
        .acerto { color: #27ae60; }
        .erro { color: #c0392b; }
        .instrucao { color: #7f8c8d; margin-bottom: 20px; }
        button { background-color: #3498db; color: white; border: none; padding: 12px 25px; font-size: 18px; margin: 10px; cursor: pointer; border-radius: 50px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s ease; }
        button:hover { background-color: #2980b9; transform: translateY(-2px); box-shadow: 0 6px 8px rgba(0,0,0,0.15); }
        button:active { transform: translateY(0); }
        
        /* Estilo para a mensagem de confirmação */
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
    <h1>🚀 Escape do Labirinto! 🚀</h1>
    <p class="instrucao">Leve o astronauta (🚶) até a nave (🚀) sem encostar nas paredes!</p>
    <canvas id="canvasLabirinto" width="300" height="300"></canvas>
    <div id="feedback" class="feedback"></div>
    <button onclick="reiniciarJogo()">🔄 Novamente</button>
    <button onclick="proximoNivel()">➡️ Próximo</button>
    
    <!-- Mensagem de confirmação que aparecerá no lugar do pop-up -->
    <div id="confirmacaoMsg" class="confirmacao"></div>

    <!-- BOTÕES DE PROGRESSO (APENAS QUANDO CONCLUÍDO) -->
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
            ctx.font = 'bold 14px Arial';
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
                // MOSTRA OS BOTÕES DE PROGRESSO QUANDO CONCLUÍDO
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
                progressoContainer.style.display = 'none'; // ESCONDE BOTÕES NO REINÍCIO
                desenharLabirinto();
            }, 1000);
        }

        function proximoNivel() {
            feedbackEl.textContent = 'Aqui viria um labirinto mais difícil!';
            feedbackEl.className = 'feedback';
        }

        // Event listeners
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
            progressoContainer.style.display = 'none'; // INICIALMENTE ESCONDIDO
        };

        const EX_ID = <?php echo $id_ex; ?>;
        async function marcarProgresso(tipo) {
            const mensagens = {
                'sozinho': '✅ Registrado: Fez sozinho!',
                'ajuda': '🟠 Registrado: Fez com ajuda!',
                'nao-fez': '❌ Registrado: Não conseguiu fazer.'
            };
            
            // Mostrar mensagem de confirmação na interface em vez de um alert
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
                    
                    // Mostrar mensagem de sucesso
                    mostrarConfirmacao('Progresso salvo com sucesso!');
                    
                    // Fechar a janela após um tempo
                   setTimeout(() => { try { window.close(); } catch(e) {} }, 1500);
                }
            } catch (err) {
                console.error('erro ao registrar progresso', err);
                mostrarConfirmacao('Erro ao salvar progresso. Tente novamente.', true);
            }
        }
        
        // Função para mostrar mensagem de confirmação
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
    </script>
</body>
</html>