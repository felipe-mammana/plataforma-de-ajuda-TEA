<?php
session_start();
include "C:/xampp/htdocs/tentativa-1/conexao.php";

if (empty($_SESSION['id_temp']) && empty($_GET['id_temp'])) {
    header("Location: /tentativa-1/index/login/login_user.php");
    exit();
}

$id_temp = $_SESSION['id_temp'] ?? $_GET['id_temp'];

$sql = "SELECT id_temp, nome, sobrenome, plano FROM tb_user_temp WHERE id_temp = ?";
$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id_temp);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) die("Usuário não encontrado.");

$chavePix = "48020835806"; 
$descricao = "Pagamento do plano Altus";

$plano = strtolower($user['plano']); 
switch ($plano) {
    case "ouro":
        $valor = "64.99";
        $qrCodePix = "00020101021126330014br.gov.bcb.pix011148020835806520400005303986540564.995802BR5923FELIPE WESTPHAL MAMMANA6009SAO PAULO62070503***6304AC76";
        break;

    case "prata":
    default:
        $valor = "49.99";
        $qrCodePix = "00020101021126330014br.gov.bcb.pix011148020835806520400005303986540549.995802BR5923FELIPE WESTPHAL MAMMANA6009SAO PAULO62070503***63044AED";
        break;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Pagamento via PIX</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f5f9f9;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
    }
    .container {
      background: #ffffff;
      padding: 35px;
      border-radius: 20px;
      box-shadow: 0 6px 18px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 500px;
      width: 100%;
    }
    h2 {
      font-weight: 600;
      color: #1e2f2e;
      margin-bottom: 15px;
    }
    p {
      color: #3a4b4a;
      margin: 8px 0;
      font-size: 15px;
    }
    .highlight {
      font-weight: 600;
      color: #00c896;
    }
    .pix-code {
      word-wrap: break-word;
      background: #eef6f5;
      padding: 12px;
      border-radius: 10px;
      margin: 15px 0;
      font-size: 14px;
      text-align: left;
      border: 1px solid #dbe6e4;
    }
    .qrcode img {
      border-radius: 12px;
      border: 4px solid #eef6f5;
    }
    input[type="file"] {
      margin: 15px 0;
      padding: 8px;
      border: 1px solid #dbe6e4;
      border-radius: 10px;
      width: 100%;
    }
    button {
      background: #00c896;
      border: none;
      padding: 12px 20px;
      color: #fff;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      transition: background 0.3s;
    }
    button:hover {
      background: #00a97a;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Pagamento via PIX</h2>
    <p>Olá, <span class="highlight"><?php echo htmlspecialchars($user['nome']); ?></span>!</p>
    <p><b>Plano:</b> <?php echo ucfirst($plano); ?></p>

    <p><b>Chave PIX:</b> <?php echo $chavePix; ?></p>
    <p><b>Valor:</b> R$ <?php echo str_replace(".", ",", $valor); ?></p>

    <div class="qrcode">
      <img src="https://api.qrserver.com/v1/create-qr-code/?data=<?php echo urlencode($qrCodePix); ?>&size=200x200" alt="QR Code PIX">
    </div>
     
    <p><b>Código PIX copia e cola:</b></p>
    <div class="pix-code"><?php echo $qrCodePix; ?></div>

    <form method="POST" action="upload_pagamento.php" enctype="multipart/form-data">
      <input type="hidden" name="id_temp" value="<?php echo $id_temp; ?>">
      <input type="hidden" name="valor" value="<?php echo $valor; ?>">
      <input type="hidden" name="plano" value="<?php echo ucfirst($plano); ?>">
      <input type="hidden" name="codigo_pix" value="<?php echo $qrCodePix; ?>">
      <p>Após o pagamento, envie o comprovante:</p>
      <input type="file" name="comprovante" accept="image/*,application/pdf" required>  
      <button type="submit">Enviar Comprovante</button>
    </form>
  </div>
</body>
</html>
