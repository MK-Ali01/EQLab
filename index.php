
<?php

require_once __DIR__ . '/includes/initiate.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: /EQLab/dashboard.php');
    exit();
}

$error = '';

if (is_post()) {
    $email    = clean(post('email'));
    $password = post('password');

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $result = login_user($conn, $email, $password);
        if ($result['status'] === 'success') {
            header('Location: /EQLab/dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login — EQLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Welcome to EQLab</h1>
        <p class="subtitle">Emotional Intelligence Assessment Platform</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" 
                       value="<?= clean(post('email', '')) ?>" 
                       autofocus required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
        </form>

        <p class="auth-link">No account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>