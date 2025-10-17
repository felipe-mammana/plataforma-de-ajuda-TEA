<?php
session_start();
include "C:/xampp/htdocs/tentativa-1/conexao.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'C:/xampp/htdocs/tentativa-1/vendor/autoload.php';

$id_temp = $_POST['id_temp'] ?? null;
$valor = $_POST['valor'] ?? null;
$qrcodepix = $_POST['codigo_pix'] ?? null;

if (empty($id_temp) || empty($valor)) die("Dados inválidos.");

// Diretório de uploads
$dir = "C:/xampp/htdocs/tentativa-1/comprovantes/";
if (!is_dir($dir)) mkdir($dir, 0777, true);

if (isset($_FILES['comprovante']) && $_FILES['comprovante']['error'] == 0) {
    $nomeArquivo = time() . "_" . basename($_FILES['comprovante']['name']);
    $caminho = $dir . $nomeArquivo;
    $tipo = $_FILES['comprovante']['type'];
    $tamanho = $_FILES['comprovante']['size'];

    if (move_uploaded_file($_FILES['comprovante']['tmp_name'], $caminho)) {
        $cone->begin_transaction();
        try {
            // Buscar usuário temporário
            $sql = "SELECT * FROM tb_user_temp WHERE id_temp = ?";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação da busca por usuário temp: " . $cone->error);
            $stmt->bind_param("i", $id_temp);
            $stmt->execute();
            $dadosTemp = $stmt->get_result()->fetch_assoc();
            if (!$dadosTemp) throw new Exception("Usuário temporário não encontrado.");
            $stmt->close();

            $chavePix = "48020835806";
            $codigoPix = $qrcodepix;

            // Pagamento
            $sql = "INSERT INTO tb_pagamentos (id_user, valor, chave_pix, codigo_pix, status, criado_em) 
                     VALUES (?, ?, ?, ?, 'pendente', NOW())";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação do pagamento: " . $cone->error);
            $stmt->bind_param("idss", $id_temp, $valor, $chavePix, $codigoPix);
            $stmt->execute();
            $id_pagamento = $stmt->insert_id;
            $stmt->close();

            // Comprovante
            $sql = "INSERT INTO tb_comprovantes (id_pagamento, nome_arquivo, caminho, tipo, tamanho, enviado_em) 
                     VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação do comprovante: " . $cone->error);
            $stmt->bind_param("isssi", $id_pagamento, $nomeArquivo, $caminho, $tipo, $tamanho);
            $stmt->execute();
            $stmt->close();

            // Migrar usuário
            $sql = "INSERT INTO tb_user (nome, sobrenome, idade, cpf, plano, data_cadastro, foto) 
                     VALUES (?, ?, ?, ?, ?, NOW(), ?)";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação da migração de usuário: " . $cone->error);
            $stmt->bind_param("ssisss", $dadosTemp['nome'], $dadosTemp['sobrenome'], $dadosTemp['idade'],
                                        $dadosTemp['cpf'], $dadosTemp['plano'], $dadosTemp['foto_perfil']);
            $stmt->execute();
            $id_user = $stmt->insert_id;
            $stmt->close();

            // Login
            $sql = "INSERT INTO tb_login (id_user, email, senha, tipo) VALUES (?, ?, ?, 'user')";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação do login: " . $cone->error);
            $stmt->bind_param("iss", $id_user, $dadosTemp['email'], $dadosTemp['senha']);
            $stmt->execute();
            $stmt->close();

            // Médicos
            $sql = "INSERT INTO tb_user_med (id_user, grau, dificuldades, laudo) VALUES (?, ?, ?, ?)";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação dos dados médicos: " . $cone->error);
            $stmt->bind_param("isss", $id_user, $dadosTemp['grau_autismo'], $dadosTemp['dificuldades'], $dadosTemp['laudo']);
            $stmt->execute();
            $stmt->close();

            // Responsável
            $sql = "INSERT INTO tb_responsavel (id_user, nome, email, telefone, cpf, foto) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação do responsável: " . $cone->error);
            $stmt->bind_param("isssss", $id_user, $dadosTemp['responsavel'], $dadosTemp['emailR'], 
                                        $dadosTemp['telefoner'], $dadosTemp['cpfr'], $dadosTemp['ftresponsavel']);
            $stmt->execute();
            $stmt->close();

            // Limpa temp
            $sql = "DELETE FROM tb_user_temp WHERE id_temp = ?";
            $stmt = $cone->prepare($sql);
            if ($stmt === false) throw new Exception("Erro na preparação da limpeza de dados temporários: " . $cone->error);
            $stmt->bind_param("i", $id_temp);
            $stmt->execute();
            $stmt->close();

            $cone->commit();

            // E-mail
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'contatofelipewmammana@gmail.com';
            $mail->Password = 'wbhp jekz yzgm behz'; // senha app Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('noreply@altus.com.br', 'Sistema Altus');
            $mail->addAddress($dadosTemp['emailR']);

            $mail->isHTML(true);
            $mail->Subject = "Confirmação de pagamento";
            $mail->Body = "
                <h3>Confirmação de Cadastro</h3>
                <p>Usuário <b>{$dadosTemp['nome']} {$dadosTemp['sobrenome']}</b> concluiu o cadastro.</p>
                <p>Plano: {$dadosTemp['plano']} - R$ ".number_format($valor,2,",",".")."</p>
                <p>Status do pagamento: <b style='color:orange;'>EM ANÁLISE</b></p>
            ";
            $mail->send();

            echo "<script>
                    alert('Comprovante enviado! Cadastro confirmado e e-mail enviado ao responsável.');
                    window.location.href='/tentativa-1/index/login/login_user.php';
                  </script>";

        } catch (Exception $e) {
            $cone->rollback();
            die("Erro: " . $e->getMessage());
        } finally {
            if (isset($cone) && $cone instanceof mysqli) {
                $cone->close();
            }
        }
    } else {
        echo "Erro ao salvar o arquivo.";
    }
} else {
    echo "Nenhum arquivo enviado.";
}
?>