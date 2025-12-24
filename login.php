<?php
session_start();
// create a CSRF token if we don't have one
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>

<!-- login page form-->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Plan It — Login</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="centered">
    <div class="card">
      <h1 class="title">Log in</h1>
      <p class="subtitle">Welcome back — enter your username and password.</p>

      <form id="loginForm" action="login_process.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">

        <label class="field">
          <span class="label-text">Username</span>
          <input id="username" name="username" type="text" required autocomplete="username">
          <small class="field-error" id="usernameError"></small>
        </label>

        <label class="field">
          <span class="label-text">Password</span>
          <input id="password" name="password" type="password" required autocomplete="current-password">
          <small class="field-error" id="passwordError"></small>
        </label>

        <div class="actions">
          <button class="btn primary" type="submit">Log in</button>
          <a class="btn ghost" href="register.php">Create account</a>
        </div>

        <div id="serverMsg" class="server-msg"><?php if (!empty($_SESSION['error'])) { echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); } ?></div>
      </form>

    </div>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>