<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (!empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        $db = Database::getConnection();
        $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'name' => $admin['name'],
                'role' => $admin['role']
            ];
            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    } else {
        $error = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login | <?php echo BUSINESS_NAME; ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/admin.css">
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="admin-body">

  <div class="admin-login-wrap">
    <div class="admin-login-box">
      
      <div style="text-align: center; margin-bottom: 28px;">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.8rem; color: #fff;">
          JAMBHO HARIDWAR <span style="color: var(--primary);">TRAVELS</span>
        </h2>
        <p style="color: var(--text-dim); font-size: 0.85rem; margin-top: 4px;">Admin Control & Booking Management</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error" style="margin-bottom: 20px;">
          <span>❌ <?php echo htmlspecialchars($error); ?></span>
        </div>
      <?php endif; ?>

      <form action="<?php echo BASE_URL; ?>/admin/login.php" method="POST">
        <div class="form-group" style="margin-bottom: 16px;">
          <label class="form-label" for="username">👤 Admin Username</label>
          <input type="text" name="username" id="username" class="form-control" placeholder="admin" value="admin" required>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
          <label class="form-label" for="password">🔒 Password</label>
          <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" value="admin123" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-bottom: 16px;">
          <span>Login to Dashboard ➔</span>
        </button>

        <div style="text-align: center; font-size: 0.8rem; color: var(--text-dim);">
          Default credentials: <strong>admin</strong> / <strong>admin123</strong><br>
          <a href="<?php echo BASE_URL; ?>/index.php" style="color: var(--primary); display: inline-block; margin-top: 10px;">← Back to Website</a>
        </div>
      </form>

    </div>
  </div>

</body>
</html>
