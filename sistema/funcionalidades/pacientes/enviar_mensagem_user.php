<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

if (!isset($_POST['id_med']) || !isset($_POST['mensagem'])) {
    die("Dados inválidos");
}

$id_med = intval($_POST['id_med']);
$mensagem = trim($_POST['mensagem']);

if ($mensagem != "") {
    // CHANGE: Use `id_user` and `id_med` columns from your table
    $sql = "INSERT INTO tb_mensagens (id_user, id_med, mensagem, tipo_remetente, tipo_destinatario, data_envio)
             VALUES (?, ?, ?, 'user', 'med', NOW())";
    
    $stmt = $cone->prepare($sql);
    
    if ($stmt === false) {
        die("Erro na preparação do SQL: " . $cone->error);
    }
    
    // CHANGE: The parameters should now be `$id_user` and `$id_med`
    $stmt->bind_param("iis", $id_user, $id_med, $mensagem);
    
    if ($stmt->execute()) {
        echo "ok";
    } else {
        echo "erro";
    }
    
    $stmt->close();
} else {
    echo "vazio";
}
?>