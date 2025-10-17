<?php
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
$id_user = ensureLoggedInUser();

include "C:xampp/htdocs/tentativa-1/conexao.php"; 

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Get the file content
    $foto = file_get_contents($_FILES['foto']['tmp_name']);
    
    // Use a prepared statement to prevent SQL injection
    $sql = "UPDATE tb_user SET foto = ? WHERE id_user = ?";
    
    // Prepare the statement
    $stmt = $cone->prepare($sql);
    if ($stmt === false) {
        die("Erro na preparação: " . $cone->error);
    }
    
    // Bind the parameters: 's' for string (long data), 'i' for integer
    // The photo data is a binary string, and the ID is an integer
    $stmt->bind_param("si", $foto, $id_user);
    
    // Execute the statement
    if ($stmt->execute()) {
        // Success
        header("Location: perfil_user.php?success=Foto atualizada com sucesso!");
        exit();
    } else {
        // Error
        header("Location: perfil_user.php?error=Erro ao atualizar a foto.");
        exit();
    }
    
    // Close the statement
    $stmt->close();
} else {
    // No file uploaded or an error occurred
    header("Location: perfil_user.php?error=Nenhuma foto enviada ou ocorreu um erro.");
    exit();
}

// Close the database connection
$cone->close();
?>