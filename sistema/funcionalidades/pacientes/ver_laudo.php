<?php
include "C:xampp/htdocs/tentativa-1/conexao.php";

$id = $_GET['id']; // ID do paciente

$sql = "SELECT Laudo_médico FROM cadastro_altus WHERE id = ?";
$stmt = $cone->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($laudo);
$stmt->fetch();

header("Content-Type: image/jpeg"); // ou image/png, dependendo do tipo da imagem
echo $laudo;

$stmt->close();
$cone->close();
?>