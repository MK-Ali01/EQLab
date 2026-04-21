<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

function generate_eq_profile($conn, $session_id, $user_id, $domain_totals, $overall) {

    // Identify strengths and weaknesses
    $strengths  = [];
    $weaknesses = [];

    foreach ($domain_totals as $domain => $score) {
        if ($score >= 70)      $strengths[]  = $domain;
        elseif ($score < 45)   $weaknesses[] = $domain;
    }

    // Rule-based profile type assignment
    $profile_type = determine_profile_type($domain_totals);

    // Priority facets for recommendation engine
    $priority_facets = get_priority_facets($conn, $session_id, $user_id);

    // Store profile
    $strengths_json      = json_encode($strengths);
    $weaknesses_json     = json_encode($weaknesses);
    $priority_facets_json= json_encode($priority_facets);

    $stmt = $conn->prepare("
        INSERT INTO eq_profiles 
        (session_id, user_id, overall_score, profile_type, strengths, weaknesses, priority_facets)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iidssss",
        $session_id, $user_id, $overall, $profile_type,
        $strengths_json, $weaknesses_json, $priority_facets_json
    );
    $stmt->execute();

    return [
        'profile_type'    => $profile_type,
        'overall_score'   => $overall,
        'strengths'       => $strengths,
        'weaknesses'      => $weaknesses,
        'priority_facets' => $priority_facets
    ];
}

function determine_profile_type($domain_totals) {
    $sa  = $domain_totals['Self-Awareness']       ?? 50;
    $sm  = $domain_totals['Self-Management']      ?? 50;
    $soa = $domain_totals['Social Awareness']     ?? 50;
    $rm  = $domain_totals['Relationship Management'] ?? 50;

    // Rule matrix — order matters (most specific first)
    if ($sa >= 70 && $soa >= 70 && $rm >= 70)
        return 'Empathetic Leader';
    if ($sa >= 70 && $sm >= 70 && $soa < 50)
        return 'Self-Disciplined Achiever';
    if ($soa >= 70 && $rm >= 70 && $sa < 50)
        return 'Social Navigator';
    if ($sm >= 70 && $sa < 50 && $soa < 50)
        return 'Controlled Introvert';
    if ($sa < 45 && $sm < 45)
        return 'Emotionally Developing';
    if ($rm < 45 && $soa < 45)
        return 'Analytical Independent';
    return 'Balanced Processor'; // default
}

function get_priority_facets($conn, $session_id, $user_id) {
    // Fetch weakest facets (lowest normalized scores)
    $stmt = $conn->prepare("
        SELECT facet, normalized_score 
        FROM scores
        WHERE session_id = ? AND user_id = ? AND level = 'Low'
        ORDER BY normalized_score ASC
        LIMIT 3
    ");
    $stmt->bind_param("ii", $session_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $facets = [];
    while ($row = $result->fetch_assoc()) {
        $facets[] = $row['facet'];
    }
    return $facets;
}
?>