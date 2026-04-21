<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

require_login();

$user_id = get_logged_in_user_id();

// Get all completed sessions with their domain averages
$stmt = $conn->prepare("
    SELECT
        s.session_id,
        s.completed_at,
        sc.domain,
        AVG(sc.normalized_score) as domain_avg,
        AVG(sc.percentile)       as domain_percentile
    FROM assessment_sessions s
    JOIN scores sc ON s.session_id = sc.session_id
    WHERE s.user_id = ? AND s.is_complete = 1
    GROUP BY s.session_id, s.completed_at, sc.domain
    ORDER BY s.completed_at ASC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$sessions = [];
while ($row = $result->fetch_assoc()) {
    $sid = $row['session_id'];
    if (!isset($sessions[$sid])) {
        $sessions[$sid] = [
            'session_id'   => $sid,
            'date'         => $row['completed_at'],
            'domain_scores'=> [],
            'overall'      => 0
        ];
    }
    $sessions[$sid]['domain_scores'][$row['domain']] = round($row['domain_avg'], 2);
}

// Compute overall per session
foreach ($sessions as &$s) {
    $vals      = array_values($s['domain_scores']);
    $s['overall'] = count($vals) > 0
        ? round(array_sum($vals) / count($vals), 2)
        : 0;
}

json_response([
    'status'   => 'success',
    'history'  => array_values($sessions),
    'count'    => count($sessions)
]);
?>