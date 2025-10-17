<?php
// registrar_progresso.php - endpoint para registrar progresso de exercícios
header('Content-Type: application/json');
require_once 'C:xampp/htdocs/tentativa-1/helpers.php';
include "C:xampp/htdocs/tentativa-1/conexao.php"; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'method_not_allowed']);
    exit;
}

$id_user = ensureLoggedInUser(); 
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'invalid_json', 'raw' => $raw]);
    exit;
}

$id_ex = isset($input['id_ex']) ? intval($input['id_ex']) : 0;
$autonomia = isset($input['autonomia']) ? trim(substr($input['autonomia'], 0, 255)) : '';

if ($id_ex <= 0 || $autonomia === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'missing_fields',
        'fields' => ['id_ex' => $id_ex, 'autonomia' => $autonomia]
    ]);
    exit;
}

// Verifica se já existe progresso desse usuário para este exercício
$stmt = $cone->prepare("
    SELECT id_prog 
    FROM tb_progresso 
    WHERE id_user = ? AND id_ex = ?
    LIMIT 1
");
$stmt->bind_param('ii', $id_user, $id_ex);
$stmt->execute();
$stmt->bind_result($id_prog);
$existing = $stmt->fetch() ? $id_prog : null;
$stmt->close();

if ($existing) {
    // Se já existe, só atualiza a autonomia
    $stmt = $cone->prepare("
        UPDATE tb_progresso 
        SET autonomia = ?
        WHERE id_prog = ?
    ");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'db_prepare_update_failed', 'detail' => $cone->error]);
        exit;
    }
    $stmt->bind_param('si', $autonomia, $existing);
    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'db_update_failed', 'detail' => $err]);
        exit;
    }
    $stmt->close();

    echo json_encode([
        'success' => true,
        'action' => 'updated_existing',
        'id_prog' => $existing,
        'autonomia' => $autonomia
    ]);
    exit;
}

// Se não existe, cria um registro
$stmt = $cone->prepare("
    INSERT INTO tb_progresso (id_user, id_ex, autonomia) 
    VALUES (?, ?, ?)
");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'db_prepare_insert_failed', 'detail' => $cone->error]);
    exit;
}
$stmt->bind_param('iis', $id_user, $id_ex, $autonomia);
if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'db_insert_failed', 'detail' => $err]);
    exit;
}
$new_id_prog = $stmt->insert_id;
$stmt->close();

echo json_encode([
    'success' => true,
    'action' => 'inserted_first',
    'id_prog' => $new_id_prog,
    'autonomia' => $autonomia
]);
exit;
