<?php
require_once 'config.php';
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_name'] = $admin['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DreamBrixs CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #0a1628; min-height: 100vh; display: flex; align-items: center; }
        .login-card { background: #fff; border-radius: 16px; padding: 40px; max-width: 420px; width: 100%; box-shadow: 0 20px 50px rgba(0,0,0,0.3); }
        .btn-login { background: linear-gradient(135deg, #F5B800, #E89B00); border: none; color: #101010; font-weight: 600; }
        .btn-login:hover { filter: brightness(1.05); color: #101010; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card mx-auto">
            <div class="text-center mb-4">
                <h3 class="fw-bold">DreamBrixs CRM</h3>
                <p class="text-muted">Projects Management</p>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-login w-100 py-2">Login</button>
            </form>
            <p class="text-center text-muted small mt-3">Default: admin / admin123</p>
        </div>
    </div>
</body>
</html>
