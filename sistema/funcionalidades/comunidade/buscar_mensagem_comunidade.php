<?php
// buscar_mensagem_comunidade.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'C:xampp/htdocs/tentativa-1/helpers.php';

// Verifica se o usuário é um médico ou um usuário comum para garantir que está logado
if (isset($_SESSION['id_user']) && !empty($_SESSION['id_user'])) {
    ensureLoggedInUser();
} elseif (isset($_SESSION['id_med']) && !empty($_SESSION['id_med'])) {
    ensureLoggedInMedico();
} else {
    // Se não estiver logado, retorna um erro para a requisição AJAX
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

$sql = "SELECT m.id_mensagem, 
               m.tipo_user AS role_autor,
               CASE 
                   WHEN m.tipo_user = 'user' THEN m.id_user
                   ELSE m.id_med
               END AS id_autor,
               COALESCE(u.nome, CONCAT('Dr ', md.nome)) AS nome,
               m.mensagem, 
               m.data_envio
         FROM tb_mensagens_comunidade m
         LEFT JOIN tb_user u ON u.id_user = m.id_user
         LEFT JOIN tb_medico md ON md.id_med = m.id_med
         ORDER BY m.data_envio ASC";

$result = $cone->query($sql);

$mensagens = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $mensagens[] = $row;
    }
}

if (empty($mensagens)) {
    echo json_encode([]); // Retorna um array vazio para o front-end
    exit;
}

header('Content-Type: application/json');
echo json_encode($mensagens);
exit;