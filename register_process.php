<?php
session_start();
require_once 'db.php';


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// CSRF check
$csrf = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $csrf)) {
    $_SESSION['error'] = 'Invalid CSRF token.';
    header('Location: register.php');
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm'] ?? '';

if (strlen($username) < 3 || strlen($username) > 50) {
    $_SESSION['error'] = 'Username must be between 3 and 50 characters.';
    header('Location: register.php');
    exit;
}
if (strlen($password) < 8) {
    $_SESSION['error'] = 'Password must be at least 8 characters.';
    header('Location: register.php');
    exit;
}
if ($password !== $confirm) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: register.php');
    exit;
}

// Check username uniqueness and display error if taken
$stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$stmt->execute([$username]);
if ($stmt->fetch()) {
    $_SESSION['error'] = 'Username already taken.';
    header('Location: register.php');
    exit;
}

// Insert user
$hash = password_hash($password, PASSWORD_DEFAULT);
$insert = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
$insert->execute([$username, $hash]);


$userId = $pdo->lastInsertId();
$_SESSION['user_id'] = $userId;
$_SESSION['username'] = $username;

// Redirect to dashboard 
$_SESSION['success'] = 'Welcome, ' . $username . '!';
header('Location: dashboard.php');
exit;
