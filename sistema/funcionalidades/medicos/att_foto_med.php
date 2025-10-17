<?php
include ("c:/xampp/htdocs/tentativa-1/conexao.php");

$id_medico = 3; // ideal pegar da sessão

if (!empty($_FILES['foto']['tmp_name'])) {
    $foto = addslashes(file_get_contents($_FILES['foto']['tmp_name']));

    $sql = "UPDATE tb_medico SET foto='$foto' WHERE id_med = $id_medico";

    if (!$cone->query($sql)) {
        die("Erro ao atualizar foto: " . $cone->error);
    }
}

header("Location: perfil.php");
exit;
?>