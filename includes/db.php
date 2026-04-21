<?php
// Central DB connection — include this in every file
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // XAMPP default
define('DB_PASS', '');            // XAMPP default (empty)
define('DB_NAME', 'eqlab_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}
$conn->set_charset('utf8mb4');
?>