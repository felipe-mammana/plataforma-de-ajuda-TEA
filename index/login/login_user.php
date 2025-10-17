<?php
session_start();
require_once 'C:xampp/htdocs/tentativa-1/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];
    
    // Select user from 'tb_login'
    $sql = "SELECT id_user, email, senha FROM tb_login WHERE email = ? AND senha = ? AND tipo = 'user'";
    $stmt = mysqli_prepare($cone, $sql);
    
    // Check if the prepare statement was successful
    if ($stmt === false) {
        $erro = "Erro ao preparar a consulta de login: " . mysqli_error($cone);
    } else {
        mysqli_stmt_bind_param($stmt, "ss", $email, $senha);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($resultado) >= 1) {
            $usuario = mysqli_fetch_assoc($resultado);
            $id_user = $usuario['id_user'];

            // Check if payment is approved for the user
            $sql_pagamento = "SELECT status FROM tb_pagamentos WHERE id_user = ? ORDER BY criado_em DESC LIMIT 1";
            $stmt_pagamento = mysqli_prepare($cone, $sql_pagamento);
            
            if ($stmt_pagamento === false) {
                 $erro = "Erro ao preparar a consulta de pagamento: " . mysqli_error($cone);
            } else {
                mysqli_stmt_bind_param($stmt_pagamento, "i", $id_user);
                mysqli_stmt_execute($stmt_pagamento);
                $resultado_pagamento = mysqli_stmt_get_result($stmt_pagamento);
                $pagamento = mysqli_fetch_assoc($resultado_pagamento);

                // Check if payment is approved
                if ($pagamento && $pagamento['status'] === 'aprovado') {
                    // Save session data
                    $_SESSION['logado'] = true;
                    $_SESSION['id_user'] = $id_user;
                    $_SESSION['email'] = $usuario['email'];

                    // Redirect to the user's chat
                    header("Location: /tentativa-1/sistema/pacientes/sistema.php");
                    exit();
                } else {
                    $erro = "Seu pagamento ainda não foi aprovado.";
                }

                mysqli_stmt_close($stmt_pagamento);
            }
        } else {
            $erro = "Email ou senha incorretos!";
        }
        
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../css/login-user.css">
</head>
<body>
  <div class="page">
    <div class="card" role="main" aria-labelledby="tituloLogin">
      <h1 id="tituloLogin">Bem vindo!</h1>
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
          <img src="../img/coruja.png" alt="Mascote Altus — Coruja" id="corujaImg"
                  onerror="console.error('Coruja não encontrada em ../img/coruja.png');" />
        </div>
        
        <button class="btn" type="submit">Entrar</button>

        <p class="register">Medico da plataforma? <a href="/tentativa-1/index/login/login_med.php" >Entre aqui</a></p>
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