<?php
// quebracabeca.php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
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

// Simulando dados que viriam do banco. Cada palavra tem uma imagem e suas letras.
$palavras = [
    'caminhão' => [
        'imagem' => 'caminhao.jpg', // Caminho para a imagem
        'letras' => ['C', 'A', 'M', 'I', 'N', 'H', 'Ã', 'O'] // Letras que formam a palavra
    ],
];

$palavraEscolhida = 'caminhão';
$dadosPalavra = $palavras[$palavraEscolhida];

// Transforma os dados em JSON para o JavaScript usar
$letrasJson = json_encode($dadosPalavra['letras']);
$imagemJson = json_encode($dadosPalavra['imagem']);
$palavraJson = json_encode($palavraEscolhida);

$id_ex = isset($_GET['id_ex']) ? intval($_GET['id_ex']) : 0; // optional exercise id via GET
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Quebra-Cabeça de Palavras - TCC TEA</title>
<style>
body {
    font-family: 'Comic Sans MS', 'Chalkboard SE', sans-serif;
    text-align: center;
    background-color: #f0f9ff;
    padding: 20px;
    position: relative;
    min-height: 100vh;
}
h1 { color: #ff6b6b; text-shadow: 2px 2px 0px #ffe66d; }
.instrucao { color: #1a535c; font-size: 18px; margin-bottom: 20px; }
#areaJogo { width: 500px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 20px; box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); }
#imagemContainer { margin-bottom: 20px; }
#imagemObjeto { max-width: 200px; max-height: 200px; border: 5px solid #4ecdc4; border-radius: 15px; }
#caixasPalavra { display: flex; justify-content: center; gap: 6px; margin-bottom: 30px; min-height: 60px; flex-wrap: nowrap; overflow-x: auto; padding: 10px 0; }
.caixa-letra { width: 42px; height: 42px; border: 3px dashed #ff6b6b; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; background-color: #ffefef; flex-shrink: 0; }
.caixa-preenchida { border: 3px solid #4ecdc4; background-color: #f7fff7; }
#containerLetras { display: flex; justify-content: center; gap: 6px; flex-wrap: wrap; }
.letra { width: 42px; height: 42px; background-color: #ffe66d; border: none; border-radius: 10px; font-size: 20px; font-weight: bold; cursor: grab; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); user-select: none; flex-shrink: 0; }
.letra:active { cursor: grabbing; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); transform: translateY(2px); }
.feedback { font-size: 22px; font-weight: bold; margin: 15px; min-height: 30px; }
.acerto { color: #4ecdc4; }
.erro { color: #ff6b6b; }
button { 
    background-color: #ff6b6b; 
    color: white; 
    border: none; 
    padding: 12px 25px; 
    font-size: 18px; 
    margin: 10px; 
    cursor: pointer; 
    border-radius: 50px; 
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
    transition: all 0.3s ease; 
}
button:hover { 
    background-color: #ff4f4f; 
    transform: translateY(-2px); 
    box-shadow: 0 6px 8px rgba(0,0,0,0.15); 
}
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

/* Botão Reiniciar sempre visível */
#btnReiniciar {
    background-color: #ff6b6b;
    margin: 20px auto;
    display: block;
}

/* Botões de progresso */
.botoes-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 10px;
    margin-top: 10px;
}

.botao-progresso {
    flex: 1;
    min-width: 150px;
    max-width: 200px;
}

/* Botão Voltar */
.btn-voltar {
    position: fixed;
    left: 20px;
    bottom: 20px;
    padding: 12px 20px;
    background: #168686;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.btn-voltar:hover {
    background: #126969;
    transform: translateY(-2px);
    transition: all 0.3s ease;
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

<h1>🧩 Quebra-Cabeça de Palavras 🧩</h1>
<p class="instrucao">Arraste as letras para montar o nome da imagem!</p>

<div id="areaJogo">
    <div id="imagemContainer">
        <img id="imagemObjeto" src="<?php echo $dadosPalavra['imagem']; ?>" alt="Caminhão">
    </div>

    <div id="caixasPalavra"></div>
    <div id="containerLetras"></div>
</div>

<div id="feedback" class="feedback"></div>

<!-- Botão Reiniciar sempre visível -->
<button id="btnReiniciar" onclick="reiniciarJogo()">🔄 Reiniciar</button>

<!-- Container para os botões de progresso (inicialmente oculto) -->
<div id="progressoContainer" class="botoes-container" style="display: none;">
    <button class="botao-progresso" onclick="marcarProgresso('sozinho')" style="background-color: #27ae60;">✅ Fez Sozinho</button>
    <button class="botao-progresso" onclick="marcarProgresso('ajuda')" style="background-color: #f39c12;">🟠 Fez com Ajuda</button>
    <button class="botao-progresso" onclick="marcarProgresso('nao-fez')" style="background-color: #e74c3c;">❌ Não Fez</button>
</div>

<div id="confirmacaoMsg" class="confirmacao"></div>

<!-- Botão Voltar -->
<a href="/tentativa-1/sistema/exercicios/exercicios.php" class="btn-voltar">
    ⬅ Voltar
</a>

<script>
const letras = <?php echo $letrasJson; ?>;
const palavraCorreta = <?php echo $palavraJson; ?>;
const feedbackEl = document.getElementById('feedback');
const caixasPalavraEl = document.getElementById('caixasPalavra');
const containerLetrasEl = document.getElementById('containerLetras');
const progressoContainer = document.getElementById('progressoContainer');
const toast = document.getElementById('toast');
const EX_ID = <?php echo $id_ex; ?>;
const userRole = '<?php echo $role; ?>'; // Adicionado: Passa o tipo de usuário

let letrasEmbaralhadas = [];
let caixas = [];
let jogoConcluido = false;

function embaralharArray(array){ for(let i=array.length-1;i>0;i--){ const j=Math.floor(Math.random()*(i+1)); [array[i],array[j]]=[array[j],array[i]]; } return array; }

function inicializarJogo(){
    caixasPalavraEl.innerHTML='';
    containerLetrasEl.innerHTML='';
    feedbackEl.textContent='';
    feedbackEl.className='feedback';
    progressoContainer.style.display='none';
    jogoConcluido=false;

    letrasEmbaralhadas=embaralharArray([...letras]);

    caixas=[];
    for(let i=0;i<letras.length;i++){
        const caixa=document.createElement('div');
        caixa.className='caixa-letra';
        caixa.dataset.index=i;
        caixasPalavraEl.appendChild(caixa);
        caixas.push(caixa);
    }

    letrasEmbaralhadas.forEach(letra=>{
        const el=document.createElement('div');
        el.className='letra';
        el.textContent=letra;
        el.draggable=true;
        el.dataset.letra=letra;
        el.addEventListener('dragstart', dragStart);
        containerLetrasEl.appendChild(el);
    });
}

function dragStart(event){ if(jogoConcluido) return; event.dataTransfer.setData('text/plain',event.target.dataset.letra); event.dataTransfer.effectAllowed='move'; }

caixasPalavraEl.addEventListener('dragover', event=>{ event.preventDefault(); if(jogoConcluido) return; event.dataTransfer.dropEffect='move'; });

caixasPalavraEl.addEventListener('drop', event=>{ 
    event.preventDefault(); 
    if(jogoConcluido) return; 
    const letra=event.dataTransfer.getData('text/plain'); 
    const caixa=event.target.closest('.caixa-letra'); 
    if(caixa && !caixa.hasChildNodes()){
        const el=document.createElement('div'); el.className='letra'; el.textContent=letra; caixa.appendChild(el); caixa.classList.add('caixa-preenchida'); 
        verificarPalavra();
    }
});

function verificarPalavra(){
    const todasPreenchidas = caixas.every(c=>c.hasChildNodes());
    if(todasPreenchidas){
        let palavraFormada='';
        caixas.forEach(caixa=>palavraFormada+=caixa.textContent);
        if(palavraFormada.toLowerCase()===palavraCorreta){
            feedbackEl.textContent='Parabéns! Você acertou! 🎉';
            feedbackEl.classList.add('acerto');
            jogoConcluido=true;
            progressoContainer.style.display='flex';
        }else{
            feedbackEl.textContent='Ops! Tente novamente.';
            feedbackEl.classList.add('erro');
        }
    }
}

function reiniciarJogo(){ 
    inicializarJogo(); 
    
    
    // Reiniciar animação
    confirmacao.style.animation = 'none';
    setTimeout(() => {
        confirmacao.style.animation = 'fadeInOut 3s forwards';
    }, 10);
}

window.onload=inicializarJogo;

async function marcarProgresso(tipo){
    // Se for médico, apenas mostra a mensagem e fecha a janela
    if (userRole === 'medico') {
        mostrarConfirmacao('Fechando a aba...', false);
        setTimeout(() => { try { window.close(); } catch(e) {} }, 1000);
        return;
    }
    
    // Se for usuário, executa a lógica de salvamento
    if (userRole === 'user') {
        const mensagens = {
            'sozinho':'✅ Registrado: Fez sozinho!',
            'ajuda':'🟠 Registrado: Fez com ajuda!',
            'nao-fez':'❌ Registrado: Não conseguiu fazer.'
        };

        mostrarConfirmacao(mensagens[tipo] || 'Progresso registrado!');

        try{
            const res = await fetch('/tentativa-1/sistema/funcionalidades/pacientes/registrar_progresso.php',{
                method:'POST',
                credentials:'include',
                headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},
                body:JSON.stringify({id_ex:EX_ID, autonomia:tipo})
            });
            const json = await res.json();
            if(json && json.success){
                mostrarConfirmacao('Progresso salvo com sucesso!');
                setTimeout(() => { try { window.close(); } catch(e) {} }, 1500);
            }
        }catch(err){ 
            console.error(err); 
        }
    }
}

function mostrarConfirmacao(mensagem, isError=false) {
    const confirmacao = document.getElementById('confirmacaoMsg');
    confirmacao.textContent = mensagem;
    confirmacao.style.backgroundColor = isError ? 'rgba(231,76,60,0.9)' : 'rgba(46,204,113,0.9)';
    confirmacao.style.display = 'block';

    // reiniciar animação
    confirmacao.style.animation = 'none';
    setTimeout(() => {
        confirmacao.style.animation = 'fadeInOut 3s forwards';
    }, 10);
}
</script>
</body>
</html>