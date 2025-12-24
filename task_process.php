<?php

session_start();
require_once 'db.php';

//check if user is logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$userId = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    $_SESSION['error'] = 'Invalid CSRF.';
    header('Location: dashboard.php');
    exit;
}

action:
$action = $_POST['action'] ?? '';
if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['task_date'] ?? null;
    if ($title === '') {
        $_SESSION['error'] = 'Task title required.';
        header('Location: dashboard.php');
        exit;
    }
    if ($date && strpos($date,'T') !== false) {

        // convert from datetime-local to MySQL datetime
        $date = str_replace('T',' ',$date) . ':00';
    }
    $stmt = $pdo->prepare('INSERT INTO tasks (user_id, title, task_date) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $title, $date ?: null]);
    $_SESSION['success'] = 'Task added.';
    header('Location: dashboard.php');
    exit;
}
// edit task title and due date
if ($action === 'edit') {
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $date = $_POST['task_date'] ?? null;
    if ($title === '') {
        $_SESSION['error'] = 'Task title required.';
        header('Location: dashboard.php');
        exit;
    }
    if ($date && strpos($date,'T') !== false) {
        $date = str_replace('T',' ',$date) . ':00';
    }
    $u = $pdo->prepare('UPDATE tasks SET title = ?, task_date = ? WHERE id = ? AND user_id = ?');
    $u->execute([$title, $date ?: null, $id, $userId]);
    $_SESSION['success'] = 'Task updated.';
    header('Location: dashboard.php');
    exit;
}

if ($action === 'toggle') {
    $id = (int)($_POST['id'] ?? 0);

    // toggle only if  belongs to user
    $stmt = $pdo->prepare('SELECT is_done FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
    $stmt->execute([$id, $userId]);
    $row = $stmt->fetch();
    if (!$row) {


        // respond depending on request type
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Task not found.']);
            exit;
        }
        $_SESSION['error'] = 'Task not found.';
        header('Location: dashboard.php');
        exit;
    }
    $new = $row['is_done'] ? 0 : 1;
    $u = $pdo->prepare('UPDATE tasks SET is_done = ? WHERE id = ? AND user_id = ?');
    $u->execute([$new, $id, $userId]);

    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'is_done' => (bool)$new]);
        exit;
    }

    $_SESSION['success'] = $new ? 'Task marked done.' : 'Task marked pending.';
    header('Location: dashboard.php');
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    $d = $pdo->prepare('DELETE FROM tasks WHERE id = ? AND user_id = ?');
    $d->execute([$id, $userId]);
    $_SESSION['success'] = 'Task deleted.';
    header('Location: dashboard.php');
    exit;
}

// unknown action
$_SESSION['error'] = 'Invalid action.';
header('Location: dashboard.php');
exit;