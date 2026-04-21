<?php
require_once __DIR__ . '/includes/initiate.php';

// Already logged in → redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    header('Location: /EQLab/dashboard.php');
    exit();
}

$error   = '';
$success = '';

if (is_post()) {
    $name     = clean(post('full_name'));
    $email    = clean(post('email'));
    $password = post('password');
    $age      = intval(post('age', 0));
    $gender   = clean(post('gender'));

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $result = register_user($conn, $name, $email, $password, $age, $gender);
        if ($result['status'] === 'success') {
            $success = 'Account created! <a href="index.php">Login now</a>';
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
    <title>Register — EQLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Create your EQLab account</h1>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php else: ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" 
                       value="<?= clean(post('full_name', '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" 
                       value="<?= clean(post('email', '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Password * (min 6 characters)</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" min="10" max="100"
                       value="<?= intval(post('age', '')) ?>">
            </div>
            <div class="form-group">
                <label>Gender</label>
                <select name="gender">
                    <option value="">Prefer not to say</option>
                    <option value="Male"   <?= post('gender')==='Male'?'selected':'' ?>>Male</option>
                    <option value="Female" <?= post('gender')==='Female'?'selected':'' ?>>Female</option>
                    <option value="Other"  <?= post('gender')==='Other'?'selected':'' ?>>Other</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Create Account</button>
        </form>
        <?php endif; ?>

        <p class="auth-link">Already have an account? <a href="index.php">Login</a></p>
    </div>
</body>
</html>