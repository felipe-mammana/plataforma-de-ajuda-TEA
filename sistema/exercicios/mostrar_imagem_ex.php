<?php
// mostrar_imagem_ex.php
// Retorna a imagem do exercício (campo 'foto') para o id_ex informado via GET.
include "C:xampp/htdocs/tentativa-1/conexao.php"; 

$id = intval($_GET['id']);
$sql = "SELECT foto FROM tb_exercicios WHERE id_ex = $id";
$result = $cone->query($sql);

if ($row = $result->fetch_assoc()) {
    // Retorna bytes da imagem; ajuste Content-Type se armazenar PNG
    header("Content-Type: image/jpeg");
    echo $row['foto'];
}