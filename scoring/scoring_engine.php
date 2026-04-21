<?php
// OECD EQ Domains and Facets mapping
// Adjust question IDs to match your actual questions table
require_once dirname(__DIR__) . '/includes/initiate.php';
function get_domain_facet_map() {
    return [
        'Self-Awareness' => [
            'Emotional Identification',
            'Emotional Understanding',
            'Emotional Expression'
        ],
        'Self-Management' => [
            'Emotional Regulation',
            'Stress Management',
            'Impulse Control'
        ],
        'Social Awareness' => [
            'Empathy',
            'Social Perception',
            'Organizational Awareness'
        ],
        'Relationship Management' => [
            'Conflict Resolution',
            'Collaboration',
            'Influence'
        ]
    ];
}

function calculate_scores($conn, $session_id, $user_id) {
    // Fetch all responses with question metadata for this session
    $stmt = $conn->prepare("
        SELECT r.question_id, r.response_value,
               q.domain, q.facet, q.is_reverse, q.scale_max
        FROM responses r
        JOIN questions q ON r.question_id = q.question_id
        WHERE r.session_id = ? AND r.user_id = ?
    ");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $facet_data = []; // [domain][facet] = [scores]

    while ($row = $result->fetch_assoc()) {
        $value = intval($row['response_value']);

        // Handle reverse scoring
        if ($row['is_reverse']) {
            $value = ($row['scale_max'] + 1) - $value;
        }

        $facet_data[$row['domain']][$row['facet']][] = $value;
    }

    $all_scores    = [];
    $domain_totals = [];

    foreach ($facet_data as $domain => $facets) {
        $domain_scores = [];

        foreach ($facets as $facet => $values) {
            $raw_score         = array_sum($values) / count($values); // mean
            $normalized        = normalize_score($raw_score, 1, 5);   // 0–100
            $percentile        = estimate_percentile($normalized);
            $level             = classify_level($percentile);

            // Save to DB
            save_score($conn, $session_id, $user_id, $domain, $facet,
                       $raw_score, $normalized, $percentile, $level);

            $domain_scores[] = $normalized;
            $all_scores[]    = $normalized;

            $facet_data[$domain][$facet] = [
                'raw'        => round($raw_score, 2),
                'normalized' => round($normalized, 2),
                'percentile' => round($percentile, 2),
                'level'      => $level
            ];
        }

        $domain_totals[$domain] = round(array_sum($domain_scores) / count($domain_scores), 2);
    }

    $overall = round(array_sum($all_scores) / count($all_scores), 2);

    return [
        'facets'       => $facet_data,
        'domain_totals'=> $domain_totals,
        'overall'      => $overall
    ];
}

function normalize_score($raw, $min, $max) {
    // Convert Likert mean (1–5) to 0–100 scale
    return (($raw - $min) / ($max - $min)) * 100;
}

function estimate_percentile($normalized_score) {
    // Simplified normal distribution approximation
    // Mean=60, SD=15 (typical EQ distribution assumption)
    $mean = 60;
    $sd   = 15;
    $z    = ($normalized_score - $mean) / $sd;
    return round(normal_cdf($z) * 100, 2);
}

function normal_cdf($z) {
    // Abramowitz and Stegun approximation
    $t = 1 / (1 + 0.2316419 * abs($z));
    $d = 0.3989423 * exp(-$z * $z / 2);
    $p = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.7814779 + $t * (-1.8212560 + $t * 1.3302744))));
    return ($z > 0) ? 1 - $p : $p;
}

function classify_level($percentile) {
    if ($percentile >= 70) return 'High';
    if ($percentile >= 35) return 'Average';
    return 'Low';
}

function save_score($conn, $session_id, $user_id, $domain, $facet,
                    $raw, $normalized, $percentile, $level) {
    $stmt = $conn->prepare("
        INSERT INTO scores 
        (session_id, user_id, domain, facet, raw_score, normalized_score, percentile, level)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissddds",
        $session_id, $user_id, $domain, $facet,
        $raw, $normalized, $percentile, $level
    );
    $stmt->execute();
}
?>