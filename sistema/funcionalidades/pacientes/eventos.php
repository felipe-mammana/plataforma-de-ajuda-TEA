<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

$eventos = [];

// pega só consultas do usuário logado
$sql = "SELECT c.id_consulta, 
               m.nome AS nome_med, 
               u.nome AS nome_paciente, 
               u.sobrenome AS sobrenome_paciente,
               c.data_consulta, 
               c.horario
        FROM tb_consulta AS c
        INNER JOIN tb_medico AS m ON c.id_med = m.id_med
        INNER JOIN tb_user  AS u ON c.id_user = u.id_user
        WHERE c.id_user = '$id_user'
        ORDER BY c.data_consulta, c.horario";

$res = mysqli_query($cone, $sql);

while ($row = mysqli_fetch_assoc($res)) {
    $nomePaciente = $row['nome_paciente'] . ' ' . $row['sobrenome_paciente'];

    $eventos[] = [
        'id'       => $row['id_consulta'],
        'title'    => $row['horario'] . " - Dr(a). " . $row['nome_med'], 
        'start'    => $row['data_consulta'] . "T" . $row['horario'],
        'nome_med' => $row['nome_med'],
        'paciente' => $nomePaciente,
        'horario'  => $row['horario'],
        'data'     => $row['data_consulta']
    ];
}

header('Content-Type: application/json');
echo json_encode($eventos);
