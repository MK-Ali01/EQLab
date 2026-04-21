<?php
// test_db.php — put in EQLab root, visit in browser, then DELETE

require_once __DIR__ . '/includes/initiate.php';

$result = $conn->query("SELECT COUNT(*) as total FROM questions");
$row    = $result->fetch_assoc();
echo "DB connected. Questions in table: " . $row['total'];
?>