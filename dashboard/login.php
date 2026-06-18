<?php
session_start();
require_once __DIR__ . '/admin_config.php';

$config = require __DIR__ . '/admin_config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === $config['admin_email'] && password_verify($password, $config['admin_password_hash'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        header('Location: index.php');
        exit;
    } else {
        $error = 'الإيميل أو الباسورد غلط.';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>تسجيل الدخول - Dashboard</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: linear-gradient(135deg, #0A1D37, #071428);
      color: #fff;
      font-family: 'Cairo', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .login-box {
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(0,194,199,0.25);
      border-radius: 20px;
      padding: 40px 32px;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.4);
    }
    .login-title {
      font-size: 1.8rem;
      font-weight: 800;
      color: #D4AF37;
      margin-bottom: 28px;
      text-align: center;
    }
    .form-group {
      margin-bottom: 20px;
    }
    .form-group label {
      display: block;
      font-size: 0.85rem;
      color: rgba(255,255,255,0.7);
      margin-bottom: 6px;
      letter-spacing: 1px;
    }
    .form-group input {
      width: 100%;
      background: rgba(255,255,255,0.07);
      border: 1px solid rgba(255,255,255,0.15);
      border-radius: 10px;
      padding: 14px 16px;
      color: #fff;
      font-size: 1rem;
      transition: all 0.3s;
    }
    .form-group input:focus {
      outline: none;
      border-color: #00C2C7;
      box-shadow: 0 0 0 3px rgba(0,194,199,0.1);
    }
    .btn-login {
      width: 100%;
      background: linear-gradient(135deg, #D4AF37, #b8942e);
      color: #0A1D37;
      border: none;
      padding: 16px;
      border-radius: 12px;
      font-size: 1rem;
      font-weight: 800;
      cursor: pointer;
      transition: all 0.3s;
      letter-spacing: 1px;
    }
    .btn-login:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(212,175,55,0.4);
    }
    .error {
      background: rgba(255,80,80,0.1);
      border: 1px solid rgba(255,80,80,0.4);
      border-radius: 10px;
      padding: 14px;
      color: #ff6060;
      font-size: 0.9rem;
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h1 class="login-title">Dashboard Login</h1>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
      <div class="form-group">
        <label>الإيميل</label>
        <input type="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($config['admin_email']) ?>">
      </div>
      <div class="form-group">
        <label>الباسورد</label>
        <input type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-login">دخول</button>
    </form>
  </div>
</body>
</html>
