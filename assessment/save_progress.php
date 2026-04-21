<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

require_login();

if (!is_post()) {
    json_response(['status' => 'error', 'message' => 'POST required'], 405);
}

$user_id   = get_logged_in_user_id();
$responses = $_POST['responses'] ?? [];

if (empty($responses)) {
    json_response(['status' => 'error', 'message' => 'No responses to save'], 400);
}

// Delete any existing incomplete session + its responses for this user
$stmt = $conn->prepare("
    SELECT session_id FROM assessment_sessions
    WHERE user_id = ? AND is_complete = 0
    ORDER BY started_at DESC LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$existing = $stmt->get_result()->fetch_assoc();

if ($existing) {
    $old_id = $existing['session_id'];
    $conn->prepare("DELETE FROM responses WHERE session_id = ?")
         ->execute() || $conn->query("DELETE FROM responses WHERE session_id = $old_id");
    $conn->query("DELETE FROM assessment_sessions WHERE session_id = $old_id");
}

// Create new incomplete session
$stmt = $conn->prepare("
    INSERT INTO assessment_sessions (user_id, started_at, is_complete)
    VALUES (?, NOW(), 0)
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$session_id = $conn->insert_id;

// Save responses
$stmt  = $conn->prepare("
    INSERT INTO responses (session_id, user_id, question_id, response_value)
    VALUES (?, ?, ?, ?)
");
$saved = 0;
foreach ($responses as $question_id => $value) {
    if (!valid_range($value, 1, 5)) continue;
    $qid = intval($question_id);
    $val = intval($value);
    $stmt->bind_param("iiii", $session_id, $user_id, $qid, $val);
    $stmt->execute();
    $saved++;
}

json_response([
    'status'     => 'success',
    'message'    => $saved . ' responses saved. You can safely close and resume later.',
    'session_id' => $session_id,
    'saved_count'=> $saved
]);
?>