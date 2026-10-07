<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

$lesson_id = intval(get_param('id'));

$stmt = $conn->prepare("SELECT * FROM lessons WHERE lesson_id = ?");
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) {
    json_response(['status' => 'error', 'message' => 'Lesson not found'], 404);
}

json_response(['status' => 'success', 'lesson' => $lesson]);
?>