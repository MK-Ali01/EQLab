<?php
require_once dirname(__DIR__) . '/includes/initiate.php';
require_login();

$user_id = get_logged_in_user_id();

// Find most recent incomplete session
$stmt = $conn->prepare("
    SELECT session_id, started_at
    FROM assessment_sessions
    WHERE user_id = ? AND is_complete = 0
    ORDER BY started_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$session = $stmt->get_result()->fetch_assoc();

if (!$session) {
    json_response(['status' => 'none', 'message' => 'No saved progress found']);
}

// Get saved responses
$stmt = $conn->prepare("
    SELECT question_id, response_value
    FROM responses
    WHERE session_id = ? AND user_id = ?
");
$stmt->bind_param("ii", $session['session_id'], $user_id);
$stmt->execute();
$result = $stmt->get_result();

$responses = [];
while ($row = $result->fetch_assoc()) {
    $responses[$row['question_id']] = intval($row['response_value']);
}

json_response([
    'status'        => 'success',
    'session_id'    => $session['session_id'],
    'last_saved_at' => $session['started_at'],
    'saved_count'   => count($responses),
    'responses'     => $responses
]);
?>