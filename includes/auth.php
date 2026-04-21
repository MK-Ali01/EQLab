<?php

session_start();

function register_user($conn, $name, $email, $password, $age, $gender) {
    // Check duplicate email
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        return ['status' => 'error', 'message' => 'Email already registered'];
    }
    $stmt->close();
    
    // Hash password — NEVER store plain text
    $hashed = password_hash($password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare(
        "INSERT INTO users (full_name, email, password, age, gender) 
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("sssss", $name, $email, $hashed, $age, $gender);
    
    if ($stmt->execute()) {
        return ['status' => 'success', 'user_id' => $conn->insert_id];
    }
    return ['status' => 'error', 'message' => 'Registration failed'];
}

function login_user($conn, $email, $password) {
    $stmt = $conn->prepare(
        "SELECT user_id, full_name, password FROM users WHERE email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['status' => 'error', 'message' => 'User not found'];
    }
    
    $user = $result->fetch_assoc();
    
    if (!password_verify($password, $user['password'])) {
        return ['status' => 'error', 'message' => 'Incorrect password'];
    }
    
    // Set session
    $_SESSION['user_id']   = $user['user_id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['logged_in'] = true;
    
    return ['status' => 'success', 'user' => $user];
}

function require_login() {
    if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
        header('Location: /index.php');
        exit();
    }
}

function logout_user() {
    session_destroy();
    header('Location: /index.php');
    exit();
}
?>