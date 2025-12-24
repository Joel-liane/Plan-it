<?php
//authentication process.
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    $_SESSION['error'] = 'Invalid CSRF.';
    header('Location: login.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = 'Enter username and password.';
    header('Location: login.php');
    exit;
}

// find user and display error if not found
$stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Invalid credentials.';
    header('Location: login.php');
    exit;
}


$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];

header('Location: dashboard.php');
exit;