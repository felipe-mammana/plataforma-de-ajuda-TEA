<?php

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

$id = intval($_GET['id']);
$sql = "SELECT foto FROM tb_exercicios WHERE id_ex = $id";
$result = $cone->query($sql);

if ($row = $result->fetch_assoc()) {
   
    header("Content-Type: image/jpeg");
    echo $row['foto'];
}
