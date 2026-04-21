<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

require_login();

$session_id = intval(get_param('session_id', 0));
$user_id    = get_logged_in_user_id();

if (!$session_id) {
    json_response(['status' => 'error', 'message' => 'session_id required'], 400);
}

$stmt = $conn->prepare("
    SELECT domain, facet, raw_score, normalized_score, percentile, level
    FROM scores
    WHERE session_id = ? AND user_id = ?
    ORDER BY domain, facet
");
$stmt->bind_param("ii", $session_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

$scores        = [];
$domain_totals = [];

while ($row = $result->fetch_assoc()) {
    $scores[$row['domain']][$row['facet']] = [
        'raw'        => round($row['raw_score'], 2),
        'normalized' => round($row['normalized_score'], 2),
        'percentile' => round($row['percentile'], 2),
        'level'      => $row['level']
    ];
    $domain_totals[$row['domain']][] = $row['normalized_score'];
}

// Compute domain averages
$domain_averages = [];
foreach ($domain_totals as $domain => $values) {
    $domain_averages[$domain] = round(array_sum($values) / count($values), 2);
}

json_response([
    'status'          => 'success',
    'session_id'      => $session_id,
    'facets'          => $scores,
    'domain_averages' => $domain_averages
]);
?>