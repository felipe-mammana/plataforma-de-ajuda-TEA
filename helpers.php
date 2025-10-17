<?php
// helpers.php - shared helpers
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Detecta se a requisição é AJAX / fetch
 */
function isAjaxRequest() {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        return true;
    }
    if (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        return true;
    }
    return false;
}

/**
 * Garante login do USUÁRIO (id_user)
 * Retorna o ID do usuário se logado, senão redireciona
 */
function ensureLoggedInUser()
{
    if (!isset($_SESSION['id_user']) || empty($_SESSION['id_user'])) {
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'not_logged_in_user']);
            exit;
        }
        header('Location: /tentativa-1/index/login/login_user.php');
        exit;
    }
    $_SESSION['user_role'] = 'user';
    return intval($_SESSION['id_user']);
}

/**
 * Garante login do MÉDICO (id_med)
 * Retorna o ID do médico se logado, senão redireciona
 */
function ensureLoggedInMedico()
{
    if (!isset($_SESSION['id_med']) || empty($_SESSION['id_med'])) {
        if (isAjaxRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'not_logged_in_med']);
            exit;
        }
        header('Location: /tentativa-1/index/login/login_medico.php');
        exit;
    }
    $_SESSION['user_role'] = 'medico';
    return intval($_SESSION['id_med']);
}