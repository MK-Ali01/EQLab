<?php

require_once dirname(__DIR__) . '/includes/initiate.php';
require_once '../scoring/scoring_engine.php';
require_once '../profiling/profiling_engine.php';

require_login();
header('Content-Type: application/json');

if (!is_post()) {
    json_response(['status' => 'error', 'message' => 'POST required'], 405);
}

$user_id   = get_logged_in_user_id();
$responses = $_POST['responses'] ?? [];

// Validate responses exist
if (empty($responses)) {
    json_response(['status' => 'error', 'message' => 'No responses received'], 400);
}

// Validate all values are in range 1-5
foreach ($responses as $qid => $val) {
    if (!valid_range($val, 1, 5)) {
        json_response([
            'status'  => 'error',
            'message' => 'Invalid response value for question ' . intval($qid)
        ], 400);
    }
}

// 1. Close any previous incomplete sessions for this user
$conn->prepare("
    UPDATE assessment_sessions SET is_complete = 0
    WHERE user_id = ? AND is_complete = 0
")->execute();

// 2. Create new completed session
$stmt = $conn->prepare("
    INSERT INTO assessment_sessions (user_id, started_at, completed_at, is_complete)
    VALUES (?, NOW(), NOW(), 1)
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$session_id = $conn->insert_id;

// 3. Insert all raw responses
$stmt = $conn->prepare("
    INSERT INTO responses (session_id, user_id, question_id, response_value)
    VALUES (?, ?, ?, ?)
");
foreach ($responses as $question_id => $value) {
    $qid = intval($question_id);
    $val = intval($value);
    $stmt->bind_param("iiii", $session_id, $user_id, $qid, $val);
    $stmt->execute();
}

// 4. Run scoring engine
$score_result = calculate_scores($conn, $session_id, $user_id);

// 5. Run profiling engine
$profile_result = generate_eq_profile(
    $conn,
    $session_id,
    $user_id,
    $score_result['domain_totals'],
    $score_result['overall']
);

// 6. Redirect to results page (or return JSON for AJAX)
// If form submitted directly → redirect
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    header('Location: ../profiling/results.php?session=' . $session_id);
    exit();
}

// If AJAX call → return JSON
json_response([
    'status'     => 'success',
    'session_id' => $session_id,
    'scores'     => $score_result,
    'profile'    => $profile_result
]);
?>