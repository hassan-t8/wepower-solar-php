<?php
require_once __DIR__ . '/../includes/auth.php';
if (isAdminLoggedIn()) { header('Location: /admin/dashboard.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — WePower Solar</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" type="image/svg+xml" href="/assets/images/favicon.svg">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= h(asset('/assets/css/global.css')) ?>">
<link rel="stylesheet" href="<?= h(asset('/assets/css/admin.css')) ?>">
</head>
<body>

<div class="adm-login-page">
  <div class="adm-login-card">
    <div class="adm-login-logo">
      <img src="/assets/images/logo.png" alt="WePower Solar">
      <h2>Admin Panel</h2>
      <p>Sign in to manage your website</p>
    </div>

    <div class="adm-login-error" id="loginError" hidden><i class="bi bi-exclamation-circle"></i> <span></span></div>

    <form id="loginForm">
      <div class="field">
        <label>Email Address</label>
        <input type="email" name="email" required autofocus placeholder="admin@wepower.pk">
      </div>
      <div class="field">
        <label>Password</label>
        <input type="password" name="password" required placeholder="••••••••">
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px" id="loginBtn">
        <i class="bi bi-shield-lock"></i> Sign In
      </button>
    </form>
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function (e) {
  e.preventDefault();
  var btn = document.getElementById('loginBtn');
  var errBox = document.getElementById('loginError');
  errBox.hidden = true;
  btn.disabled = true;
  var orig = btn.innerHTML;
  btn.innerHTML = '<span class="adm-spinner"></span> Signing in…';

  var form = e.target;
  var payload = { email: form.email.value, password: form.password.value };
  try {
    var res = await fetch('/admin/api/login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
      body: JSON.stringify(payload),
    });
    var data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Login failed');
    window.location.href = '/admin/dashboard.php';
  } catch (err) {
    errBox.hidden = false;
    errBox.querySelector('span').textContent = err.message;
    btn.disabled = false;
    btn.innerHTML = orig;
  }
});
</script>
</body>
</html>
