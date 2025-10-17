<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

if (!isset($_GET['id_med'])) {
    die("Médico inválido");
}
$id_med = intval($_GET['id_med']);

// CHANGE: Use id_user and id_med in the WHERE clause
$sql = "SELECT id_user, id_med, mensagem, data_envio, tipo_remetente
        FROM tb_mensagens
        WHERE (id_user = ? AND id_med = ? AND tipo_remetente = 'user')
           OR (id_user = ? AND id_med = ? AND tipo_remetente = 'med')
        ORDER BY data_envio ASC";

$stmt = $cone->prepare($sql);
// CHANGE: The parameters should be the logged-in user and the selected doctor
$stmt->bind_param("iiii", $id_user, $id_med, $id_user, $id_med);
$stmt->execute();
$result = $stmt->get_result();

$mensagens = [];
while ($row = $result->fetch_assoc()) {
    $mensagens[] = $row;
}

header('Content-Type: application/json');
echo json_encode($mensagens);
?>