
(function(){
  

  // Register form validation 
  var reg = document.getElementById('registerForm');
  if (reg) {
    var username = document.getElementById('username');
    var password = document.getElementById('password');
    var confirm = document.getElementById('confirm');
    var submit = document.getElementById('submitBtn');
    function check(){
      var ok = true;
      if (!username.value || username.value.trim().length < 3) ok = false;
      if (!password.value || password.value.length < 8) ok = false;
      if (password.value !== confirm.value) ok = false;
      if (submit) { submit.disabled = !ok; submit.style.opacity = ok ? '1' : '0.7'; }
      return ok;
    }
    username && username.addEventListener('input', check);
    password && password.addEventListener('input', check);
    confirm && confirm.addEventListener('input', check);
    reg && reg.addEventListener('submit', function(e){ if (!check()) e.preventDefault(); });
  }

  // Login form tiny check
  var login = document.getElementById('loginForm');
  if (login) {
    var u = document.getElementById('username');
    var p = document.getElementById('password');
    login.addEventListener('submit', function(e){
      if (!u.value || !p.value) e.preventDefault();
    });
  }

  //  show edit form inside a task
  document.addEventListener('click', function(e){
    var editBtn = e.target.closest('button[data-action="edit"]');
    if (editBtn) {
      var task = editBtn.closest('.task');
      if (!task) return;
      var form = task.querySelector('.edit-form');
      var titleEl = task.querySelector('.task-title');
      if (form && titleEl) {
        form.style.display = 'block';
        titleEl.style.display = 'none';
        var inpt = form.querySelector('input[name="title"]');
        inpt && inpt.focus();
      }
    }

    if (e.target && e.target.matches('button[data-action="cancel"]')) {
      var task = e.target.closest('.task');
      if (!task) return;
      var form = task.querySelector('.edit-form');
      var titleEl = task.querySelector('.task-title');
      if (form && titleEl) {
        form.style.display = 'none';
        titleEl.style.display = '';
      }
    }
  });

  // Inline togglle for instant feedback
  document.addEventListener('submit', function(e){
    var f = e.target;
    if (!f || !f.classList || !f.classList.contains('inline-form')) return;
    e.preventDefault();
    var data = new FormData(f);
    fetch(f.action, {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: {'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
    }).then(function(res){
      return res.json();
    }).then(function(json){
      if (json && json.success) {
        var task = f.closest('.task');
        if (!task) { window.location.reload(); return; }
        if (json.is_done) {
          task.classList.add('done'); task.classList.remove('pending');
        } else {
          task.classList.remove('done'); task.classList.add('pending');
        }
        var btn = f.querySelector('button[type="submit"]');
        if (btn) btn.textContent = json.is_done ? 'Undo' : 'Done';
      } else {
        window.location.reload();
      }
    }).catch(function(){ window.location.reload(); });
  });

  // Notifications for overdue tasks (dashboard)
  var tasksEl = document.querySelector('.tasks');
  if (tasksEl) {
    var overdue = document.querySelectorAll('.deadline.overdue').length;
    if (overdue > 0 && 'Notification' in window) {
      function notify(){ new Notification('Plan It — Overdue tasks', {body: 'You have ' + overdue + ' overdue task(s) — check your dashboard.'}); }
      if (Notification.permission === 'granted') notify();
      else if (Notification.permission !== 'denied') {
        Notification.requestPermission().then(function(p){ if (p === 'granted') notify(); });
      }
    }

    // fade out success messages
    var s = document.querySelector('.server-success');
    if (s) {
      setTimeout(function(){ s.style.transition = 'opacity .6s'; s.style.opacity = '0'; }, 3000);
      setTimeout(function(){ s.remove(); }, 3800);
    }
  }

})();