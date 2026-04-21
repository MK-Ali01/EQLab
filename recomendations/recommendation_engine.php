<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

function get_recommendations($conn, $user_id) {

    // 1. Get latest session's priority facets
    $stmt = $conn->prepare("
        SELECT priority_facets, weaknesses 
        FROM eq_profiles 
        WHERE user_id = ? 
        ORDER BY generated_at DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();

    if (!$profile) return ['status' => 'no_profile'];

    $priority_facets = json_decode($profile['priority_facets'], true);

    // 2. Find lessons for those facets (not yet completed)
    $recommendations = [];

    foreach ($priority_facets as $facet) {
        $stmt = $conn->prepare("
            SELECT l.lesson_id, l.title, l.domain, l.facet, l.difficulty
            FROM lessons l
            WHERE l.facet = ?
            AND l.lesson_id NOT IN (
                SELECT lesson_id FROM progress_logs 
                WHERE user_id = ? AND action = 'lesson_completed'
            )
            ORDER BY l.difficulty ASC
            LIMIT 2
        ");
        $stmt->bind_param("si", $facet, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $recommendations[] = $row;
        }
    }

    // 3. Calculate overall progress
    $progress = get_learning_progress($conn, $user_id);

    return [
        'status'          => 'success',
        'priority_facets' => $priority_facets,
        'lessons'         => $recommendations,
        'progress'        => $progress
    ];
}

function get_learning_progress($conn, $user_id) {
    $total = $conn->query("SELECT COUNT(*) as c FROM lessons")->fetch_assoc()['c'];

    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT lesson_id) as completed 
        FROM progress_logs 
        WHERE user_id = ? AND action = 'lesson_completed'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $completed = $stmt->get_result()->fetch_assoc()['completed'];

    return [
        'total'     => $total,
        'completed' => $completed,
        'percent'   => $total > 0 ? round(($completed / $total) * 100) : 0
    ];
}
?>