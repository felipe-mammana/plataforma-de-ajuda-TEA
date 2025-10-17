<?php
session_start();
include("c:/xampp/htdocs/tentativa-1/conexao.php");

if (!isset($_SESSION['id_med'])) {
    die("Médico não logado.");
}
$id_med = $_SESSION['id_med'];

if (!isset($_GET['id_user'])) {
    die("Usuário inválido");
}
$id_user = intval($_GET['id_user']);

// CHANGE: Use id_user and id_med in the WHERE clause
$sql = "SELECT id_user, id_med, mensagem, data_envio, tipo_remetente
        FROM tb_mensagens
        WHERE (id_med = ? AND id_user = ? AND tipo_remetente = 'med')
           OR (id_med = ? AND id_user = ? AND tipo_remetente = 'user')
        ORDER BY data_envio ASC";

$stmt = $cone->prepare($sql);
// CHANGE: The parameters should be the logged-in doctor and the selected user
$stmt->bind_param("iiii", $id_med, $id_user, $id_med, $id_user);
$stmt->execute();
$result = $stmt->get_result();

$mensagens = [];
while ($row = $result->fetch_assoc()) {
    $mensagens[] = $row;
}

header('Content-Type: application/json');
echo json_encode($mensagens);
?>