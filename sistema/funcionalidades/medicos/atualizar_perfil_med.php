<?php
session_start();
include("c:/xampp/htdocs/tentativa-1/conexao.php");

// Verifica se o usuário está logado
if (!isset($_SESSION['id_med'])) {
    header("Location: login_med.php");
    exit;
}

$id_medico = $_SESSION['id_med'];

// Apenas processa se for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: perfil.php");
    exit;
}

// Coleta os campos enviados
$nome = $_POST['nome'] ?? '';
$tipo_especialidade = $_POST['tipo_especialidade'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$crn = $_POST['crn'] ?? '';

// Verifica campos obrigatórios
if (empty($nome) || empty($tipo_especialidade) || empty($email)) {
    header("Location: perfil.php?erro=Campos obrigatórios não preenchidos");
    exit;
}

// Prepara atualização da foto
$fotoSQL = "";
if (!empty($_FILES['foto']['tmp_name'])) {
    $foto = addslashes(file_get_contents($_FILES['foto']['tmp_name']));
    $fotoSQL = ", foto='$foto'";
}

// Atualiza tabela tb_medico
$sqlMedico = "UPDATE tb_medico 
              SET nome='$nome',
                  tipo_especialidade='$tipo_especialidade',
                  telefone='$telefone',
                  crn='$crn'
                  $fotoSQL
              WHERE id_med = $id_medico";

if (!$cone->query($sqlMedico)) {
    die("Erro ao atualizar médico: " . $cone->error);
}

// Atualiza email na tabela tb_login
$sqlLogin = "UPDATE tb_login 
             SET email='$email'
             WHERE id_med = $id_medico";

if (!$cone->query($sqlLogin)) {
    die("Erro ao atualizar login: " . $cone->error);
}

// Redireciona para o perfil
header("Location: perfil.php?sucesso=1");
exit;