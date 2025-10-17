<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 


// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: perfil_user.php?error=Acesso inválido");
    exit();
}

$id_user = $_SESSION['id_user'];

// Sanitizar e validar os dados de entrada
$nome        = isset($_POST['nome']) ? $cone->real_escape_string(trim($_POST['nome'])) : '';
$sobrenome   = isset($_POST['sobrenome']) ? $cone->real_escape_string(trim($_POST['sobrenome'])) : '';
$idade       = isset($_POST['idade']) ? $cone->real_escape_string(trim($_POST['idade'])) : '';
$plano       = isset($_POST['plano']) ? $cone->real_escape_string(trim($_POST['plano'])) : '';


$nome_resp   = isset($_POST['nome_responsavel']) ? $cone->real_escape_string(trim($_POST['nome_responsavel'])) : '';
$cpf         = isset($_POST['cpf']) ? $cone->real_escape_string(trim($_POST['cpf'])) : '';
$email       = isset($_POST['email_responsavel']) ? $cone->real_escape_string(trim($_POST['email_responsavel'])) : '';
$telefone    = isset($_POST['telefone']) ? $cone->real_escape_string(trim($_POST['telefone'])) : '';

$grau        = isset($_POST['grau']) ? $cone->real_escape_string(trim($_POST['grau'])) : '';

// Processar as dificuldades (que vêm como array)
$dificuldades_array = isset($_POST['dificuldades']) ? $_POST['dificuldades'] : array();
// Sanitizar cada item do array
$dificuldades_sanitized = array();
foreach ($dificuldades_array as $dificuldade) {
    $dificuldades_sanitized[] = $cone->real_escape_string(trim($dificuldade));
}
$dificuldades = implode(', ', $dificuldades_sanitized);

// Iniciar transação para garantir que todas as operações sejam bem-sucedidas
$cone->begin_transaction();

// ==================== UPDATE tb_user ====================
try {
$sql_user = "UPDATE tb_user 
             SET nome='$nome', sobrenome='$sobrenome', idade='$idade', 
                 plano='$plano'
             WHERE id_user = $id_user";

if (!$cone->query($sql_user)) {
    throw new Exception("Erro ao atualizar usuário: " . $cone->error);
}

// ==================== UPDATE tb_pagamentos ====================

    // ==================== UPDATE tb_responsavel ====================
    // Primeiro verifica se existe um registro
    $check_resp = $cone->query("SELECT id_user FROM tb_responsavel WHERE id_user = $id_user");
    
    if ($check_resp->num_rows > 0) {
        // Atualiza se existir
        $sql_resp = "UPDATE tb_responsavel 
                     SET nome='$nome_resp', cpf='$cpf', email='$email', telefone='$telefone'
                     WHERE id_user = $id_user";
    } else {
        // Insere se não existir
        $sql_resp = "INSERT INTO tb_responsavel (id_user, nome, cpf, email, telefone)
                     VALUES ($id_user, '$nome_resp', '$cpf', '$email', '$telefone')";
    }
    
    if (!$cone->query($sql_resp)) {
        throw new Exception("Erro ao atualizar responsável: " . $cone->error);
    }

    // ==================== UPDATE tb_user_med ====================
    // Primeiro verifica se existe um registro
    $check_med = $cone->query("SELECT id_user FROM tb_user_med WHERE id_user = $id_user");
    
    if (isset($_FILES['laudo']) && $_FILES['laudo']['error'] === UPLOAD_ERR_OK) {
        $laudo = $cone->real_escape_string(file_get_contents($_FILES['laudo']['tmp_name']));
        
        if ($check_med->num_rows > 0) {
            $sql_user_med = "UPDATE tb_user_med 
                             SET grau='$grau', dificuldades='$dificuldades', laudo='$laudo'
                             WHERE id_user = $id_user";
        } else {
            $sql_user_med = "INSERT INTO tb_user_med (id_user, grau, dificuldades, laudo)
                             VALUES ($id_user, '$grau', '$dificuldades', '$laudo')";
        }
    } else {
        if ($check_med->num_rows > 0) {
            $sql_user_med = "UPDATE tb_user_med 
                             SET grau='$grau', dificuldades='$dificuldades'
                             WHERE id_user = $id_user";
        } else {
            $sql_user_med = "INSERT INTO tb_user_med (id_user, grau, dificuldades)
                             VALUES ($id_user, '$grau', '$dificuldades')";
        }
    }
    
    if (!$cone->query($sql_user_med)) {
        throw new Exception("Erro ao atualizar informações médicas: " . $cone->error);
    }

    // Se tudo deu certo, confirma as alterações
    $cone->commit();
    header("Location: perfil_user.php?success=Perfil atualizado com sucesso");
    exit();
    
}catch (Exception $e) {
    // Em caso de erro, reverte todas as alterações
    $cone->rollback();
    header("Location: perfil_user.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>