<?php
session_start();
include("c:/xampp/htdocs/tentativa-1/conexao.php");

if (!isset($_SESSION['id_med'])) {
    die("Médico não logado.");
}
$id_med = $_SESSION['id_med'];

if (!isset($_POST['id_user']) || !isset($_POST['mensagem'])) {
    die("Dados inválidos");
}

$id_user = intval($_POST['id_user']);
$mensagem = trim($_POST['mensagem']);

if ($mensagem != "") {
    // CHANGE: Use `id_user` and `id_med` columns from your table
    $sql = "INSERT INTO tb_mensagens (id_user, id_med, mensagem, tipo_remetente, tipo_destinatario, data_envio)
             VALUES (?, ?, ?, 'med', 'user', NOW())";
             
    $stmt = $cone->prepare($sql);
    $stmt->bind_param("iis", $id_user, $id_med, $mensagem);
    
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "erro";
    }
} else {
    echo "vazio";
}
?>