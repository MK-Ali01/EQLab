<?php
require_once dirname(__DIR__) . '/includes/initiate.php';
require_login();

if (is_post()) {
    $user_id = get_logged_in_user_id();
    $lesson_id = intval(post('lesson_id'));
    $action = clean(post('action')); // 'Started' or 'Completed'

    $stmt = $conn->prepare("INSERT INTO progress_logs (user_id, lesson_id, action) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $lesson_id, $action);
    $stmt->execute();

    json_response(['status' => 'success', 'message' => 'Progress logged']);
}
?>