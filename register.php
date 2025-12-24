<?php
session_start();
// CSRF token helper
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Plan It — Register</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="centered">
    <div class="card">
      <h1 class="title">Create your account</h1>
        <p class="subtitle">Join Plan It — start managing your tasks today.</p>

      <form id="registerForm" action="register_process.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">

        <label class="field">
          <span class="label-text">Username</span>
          <input id="username" name="username" type="text" required minlength="3" maxlength="50" autocomplete="username" />
          <small class="field-error" id="usernameError"></small>
        </label>

        <label class="field">
          <span class="label-text">Password</span>
          <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" />
          <small class="field-error" id="passwordError"></small>
        </label>

        <label class="field">
          <span class="label-text">Confirm Password</span>
          <input id="confirm" name="confirm" type="password" required minlength="8" autocomplete="new-password" />
          <small class="field-error" id="confirmError"></small>
        </label>

        <div class="actions">
          <button id="submitBtn" class="btn primary" type="submit">Register</button>
          <a class="btn ghost" href="login.php">Have an account? Log in</a>
        </div>

        <div id="serverMsg" class="server-msg"><?php if (!empty($_SESSION['error'])) { echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); } ?></div>
      </form>

    </div>

  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>