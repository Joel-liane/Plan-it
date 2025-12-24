<?php

/*Pan_it
plan your tasks efficiently
features: user registration, login, task management with due dates, CSRF protection, session management
 */

session_start();
$loggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? null;
?>



<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Plan It</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="centered">
    <div class="card">
      <?php if ($loggedIn): ?>
        <h1 class="title">Welcome back, <?php echo htmlspecialchars($username); ?> 🎉</h1>
      <?php else: ?>
        <h1 class="title">Welcome to Plan It</h1>
        <p class="subtitle">Plan all your tasks efficiently</p>
        <div class="actions">
          <a class="btn primary" href="register.php">Register</a>
          <a class="btn ghost" href="login.php">Log in</a>
        </div>
      <?php endif; ?>
    </div>
  </main>
  <script src="assets/js/app.js"></script>
</body>
</html>