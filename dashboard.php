<?php
// dashboard.php - simple tasks dashboard
session_start();
if (empty($_SESSION['user_id'])) {
    // not logged in -> go to login
    header('Location: login.php');
    exit;
}
require_once 'db.php';
$userId = (int)$_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'user';

// create CSRF if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf_token'];

// all / done / pending tasks
$filter = $_GET['filter'] ?? 'all';
$sql = 'SELECT * FROM tasks WHERE user_id = ?';
$params = [$userId];
if ($filter === 'done') {
    $sql .= ' AND is_done = 1';
} elseif ($filter === 'pending') {
    $sql .= ' AND (is_done = 0 OR is_done IS NULL)';
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

// counting of tasks(all, done, pending)
$total = count($tasks);
$allCountStmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ?');
$allCountStmt->execute([$userId]);
$allCount = (int)$allCountStmt->fetchColumn();

$doneCountStmt = $pdo->prepare('SELECT COUNT(*) FROM tasks WHERE user_id = ? AND is_done = 1');
$doneCountStmt->execute([$userId]);
$doneCount = (int)$doneCountStmt->fetchColumn();

$pendingCount = max(0, $allCount - $doneCount);
?>

<!--  -->
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Plan It — Dashboard</title>
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <main class="wrap">
    <header class="header">
      <div class="brand">
        <h1>Plan It</h1>
        <div class="subtitle">Hi, <?php echo htmlspecialchars($username); ?> — your tasks</div>
      </div>
      <div class="meta">
        <div class="count">Tasks: <strong><?php echo $allCount; ?></strong></div>
        <div class="actions">
          <a class="btn ghost" href="logout.php">Log out</a>
        </div>
      </div>
    </header>

    <section class="controls">
      <!-- Add tasks form -->
      <form class="add" action="task_process.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
        <input type="hidden" name="action" value="add">
        <input name="title" type="text" placeholder="New task title" required>
        <input name="task_date" type="datetime-local">
        <button class="btn primary" type="submit">Add</button>
      </form>

      <div class="filters">
        <a class="filter all <?php echo $filter==='all' ? 'active':''; ?>" href="?filter=all">All (<?php echo $allCount; ?>)</a>
        <a class="filter pending <?php echo $filter==='pending' ? 'active':''; ?>" href="?filter=pending">Pending (<?php echo $pendingCount; ?>)</a>
        <a class="filter done <?php echo $filter==='done' ? 'active':''; ?>" href="?filter=done">Done (<?php echo $doneCount; ?>)</a>
      </div>
    </section>

    <?php // session management messages.
     if (!empty($_SESSION['error'])): ?>
      <div class="server-msg"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="server-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>

    <section class="tasks">
      <?php if (empty($tasks)): ?>
        <div class="empty">No tasks yet — add your first task above.</div>
      <?php else: ?>
        <?php foreach ($tasks as $t): ?>
          <?php
            $due = $t['task_date'] ?: null;
            $isDone = (int)$t['is_done'] === 1;

            // deadline color: red overdue, yellow urgent (<=2 days), default muted
            $deadlineClass = '';
            if ($due) {
                $dueTs = strtotime($due);
                $now = time();
                $diffDays = ($dueTs - $now) / (60*60*24);
                if (!$isDone && $diffDays < 0) $deadlineClass = 'overdue';
                elseif (!$isDone && $diffDays <= 2) $deadlineClass = 'urgent';
            }
          ?>
          <div class="task <?php echo $isDone ? 'done' : 'pending'; ?>">
            <div class="task-col task-main">
              <div class="task-title"><?php echo htmlspecialchars($t['title']); ?></div>

              <div class="task-actions">
                <form action="task_process.php" method="post" class="inline-form" style="display:inline-block;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                  <input type="hidden" name="action" value="toggle">
                  <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                  <button class="btn done small" type="submit"><?php echo $isDone ? 'Undo':'Done'; ?></button>
                </form>

                <button class="btn edit small" type="button" data-action="edit" data-id="<?php echo (int)$t['id']; ?>">Edit</button>

                <form action="task_process.php" method="post" onsubmit="return confirm('Delete this task?');" style="display:inline-block;">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                  <button class="btn danger small" type="submit">Delete</button>
                </form>
              </div>
        <!-- 
                Edit form (edit task title and due date)
              -->
              <form class="edit-form" action="task_process.php" method="post" style="display:none;margin-top:8px;">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?php echo (int)$t['id']; ?>">
                <input name="title" type="text" value="<?php echo htmlspecialchars($t['title']); ?>" required>
                <input name="task_date" type="datetime-local" value="<?php echo $t['task_date'] ? date('Y-m-d\\TH:i', strtotime($t['task_date'])) : ''; ?>">
                <div class="edit-actions" style="margin-top:8px;">
                  <button class="btn primary small" type="submit">Save</button>
                  <button class="btn ghost small" type="button" data-action="cancel">Cancel</button>
                </div>
              </form>
            </div>

            <div class="task-col task-due">
              <?php if ($due): ?>
                <div class="deadline <?php echo $deadlineClass; ?>"><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($due))); ?></div>
              <?php else: ?>
                <div class="deadline none">No due date</div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <footer class="footer"></footer>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>