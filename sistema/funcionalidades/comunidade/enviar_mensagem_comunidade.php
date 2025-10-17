<?php
// enviar_mensagem_comunidade.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'C:xampp/htdocs/tentativa-1/helpers.php';

// Verifica se o usuário é um médico ou um usuário comum
if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    $id = ensureLoggedInUser();
    $role = 'user';
} elseif (isset($_SESSION['id_med']) && !empty($_SESSION['id_med'])) {
    $id = ensureLoggedInMedico();
    $role = 'medico';
} else {
    // Se não estiver logado, retorna um erro para a requisição AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

if (!isset($_POST['mensagem'])) {
    echo "Dados inválidos";
    exit;
}

$mensagem = trim($_POST['mensagem']);
if ($mensagem === "") {
    echo "vazio";
    exit;
}

if ($role === 'user') {
    // Insere o id_user e deixa id_med como NULL
    $sql = "INSERT INTO tb_mensagens_comunidade (id_user, id_med, tipo_user, mensagem, data_envio) VALUES (?, NULL, 'user', ?, NOW())";
    $stmt = $cone->prepare($sql);
    $stmt->bind_param("is", $id, $mensagem);
} else { // medico
    // Insere o id_med e deixa id_user como NULL
    $sql = "INSERT INTO tb_mensagens_comunidade (id_user, id_med, tipo_user, mensagem, data_envio) VALUES (NULL, ?, 'medico', ?, NOW())";
    $stmt = $cone->prepare($sql);
    $stmt->bind_param("is", $id, $mensagem);
}


if ($stmt->execute()) {
    echo "ok";
} else {
    // Esta linha irá imprimir a mensagem de erro exata do MySQL.
    echo "erro: " . $stmt->error;
}
$stmt->close();
?>
