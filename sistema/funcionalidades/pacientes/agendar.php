<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 


$id_user = $_SESSION['id_user'];
$id_med  = $_POST['id_med'];
$data    = $_POST['data_consulta'];
$hora    = $_POST['horario'];   
$motivo  = $_POST['motivo'];


$sql_check = "SELECT COUNT(*) as total 
              FROM tb_consulta 
              WHERE id_med = '$id_med' 
                AND data_consulta = '$data'
                AND ABS(TIMESTAMPDIFF(MINUTE, horario, '$hora')) < 60";
$res_check = mysqli_query($cone, $sql_check);
$row_check = mysqli_fetch_assoc($res_check);

if ($row_check['total'] > 0) {
    echo "<script>alert('Já existe uma consulta para este médico em até 1h desse horário!'); window.history.back();</script>";
    exit;
}


$sql = "INSERT INTO tb_consulta (id_user, id_med, data_consulta, horario, motivo) 
        VALUES ('$id_user', '$id_med', '$data', '$hora', '$motivo')";

if (mysqli_query($cone, $sql)) {
    
    header("Location: /tentativa-1/sistema/funcionalidades/pacientes/calendario.php");
    exit;
} else {
    echo "Erro: " . mysqli_error($cone);
}
