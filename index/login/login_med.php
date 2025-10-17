<?php
session_start();
require_once 'C:xampp/htdocs/tentativa-1/conexao.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $senha = trim($_POST["senha"]);
    
    $sql = "SELECT l.id_med, l.email, l.senha, m.nome
            FROM tb_login l
            JOIN tb_medico m ON m.id_med = l.id_med
            WHERE l.email = ? AND l.tipo = 'med'";
    

    $stmt = mysqli_prepare($cone, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if ($resultado && mysqli_num_rows($resultado) === 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        
        if ($usuario['senha'] === $senha) {
            // Salva os dados principais na sessão
            $_SESSION['logado'] = true;
            $_SESSION['id_med'] = $usuario['id_med']; // ESSENCIAL
            $_SESSION['email'] = $usuario['email'];
            $_SESSION['nome_med'] = $usuario['nome'];

            // Redireciona para o menu do médico
            header("location: /tentativa-1/sistema/medicos/menu_med.php");
            exit();
        } else {
            $erro = "Senha incorreta!";
        }
    } else {
        $erro = "Email não encontrado!";
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="/tentativa-1/index/css/login-med.css">
</head>
<body>
  <div class="page">
    <div class="card" role="main" aria-labelledby="tituloLogin">
      <h1 id="tituloLogin">Bem vindo Medico! </h1>
      <?php if (!empty($erro)) { echo '<div class="alert-error" role="alert">'.htmlspecialchars($erro).'</div>'; } ?>

      <form action="" method="POST">
        <div class="field">
          <input type="text" name="email" placeholder="Email do usuário" autocomplete="username" required>
          <span class="icon" aria-hidden="true">👤</span>
        </div>

        <div class="field">
          <input type="password" name="senha" placeholder="Senha" autocomplete="current-password" required>
          <span class="icon" aria-hidden="true">🔒</span>
        </div>

        <div class="options">
          <label><input type="checkbox" name="lembrar"> <span>Lembrar senha</span></label>
          <a href="#" style="color:inherit;opacity:0.95;text-decoration:none;font-weight:600">Esqueci a senha</a>
        </div>

        <div class="coruja-wrap">
          <img src="coruja-med.png" alt="Mascote Altus — Coruja" id="corujaImg"
               onerror="console.error('Coruja não encontrada em ../img/coruja.png');" />
        </div>
        
        <button class="btn" type="submit">Entrar</button>

       
      </form>
    </div>
  </div>

  <script>
    (function(){
      const img = document.getElementById('corujaImg');
      img.addEventListener('load', ()=> console.log('Coruja carregada OK:', img.src));
      img.addEventListener('error', ()=> console.warn('Erro ao carregar coruja em:', img.src));
    })();
  </script>
</body>
</html>
