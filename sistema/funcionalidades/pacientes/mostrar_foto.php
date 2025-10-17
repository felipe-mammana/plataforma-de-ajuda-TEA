<?php
include("C:xampp/htdocs/tentativa-1/conexao.php");

if (!isset($_GET['tipo']) || !isset($_GET['id'])) {
    http_response_code(400);
    exit("Parâmetros inválidos");
}

$tipo = $_GET['tipo']; // "user" ou "medico"
$id   = intval($_GET['id']);

if ($tipo == "user") {
    $sql = "SELECT foto FROM tb_user WHERE id_user = ?";
} elseif ($tipo == "medico") {
    $sql = "SELECT foto FROM tb_medico WHERE id_med = ?";
} else {
    http_response_code(400);
    exit("Tipo inválido");
}

$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    header("Content-Type: image/jpeg"); // ou image/png, depende do que você salva
    echo $row['foto'];
} else {
    // fallback: mostra uma imagem padrão
    header("Content-Type: image/png");
    readfile("../imagens/default.png");
}