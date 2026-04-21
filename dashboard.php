<?php
require_once __DIR__ . '/includes/initiate.php';

require_login();

$user_id   = get_logged_in_user_id();
$full_name = clean($_SESSION['full_name']);

// Get latest EQ profile if exists
$stmt = $conn->prepare("
    SELECT ep.overall_score, ep.profile_type, ep.generated_at
    FROM eq_profiles ep
    WHERE ep.user_id = ?
    ORDER BY ep.generated_at DESC
    LIMIT 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

// Get total assessments taken
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM assessment_sessions 
    WHERE user_id = ? AND is_complete = 1
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_assessments = $stmt->get_result()->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — EQLab</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <span class="nav-brand">EQLab</span>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="assessment/questions.php">Take Assessment</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>

    <div class="main-container">
        <h2>Welcome back, <?= $full_name ?></h2>

        <div class="card-grid">
            <div class="stat-card">
                <div class="stat-number"><?= $total_assessments ?></div>
                <div class="stat-label">Assessments completed</div>
            </div>

            <?php if ($profile): ?>
            <div class="stat-card">
                <div class="stat-number"><?= round($profile['overall_score']) ?></div>
                <div class="stat-label">Latest EQ score</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= clean($profile['profile_type']) ?></div>
                <div class="stat-label">Your EQ profile type</div>
            </div>
            <?php else: ?>
            <div class="stat-card">
                <div class="stat-number">—</div>
                <div class="stat-label">No assessment yet</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="action-row">
            <a href="assessment/questions.php" class="btn-primary">
                <?= $total_assessments > 0 ? 'Retake Assessment' : 'Start Your Assessment' ?>
            </a>
            <?php if ($profile): ?>
            <a href="profiling/get_profile.php" class="btn-secondary">View Full Profile</a>
            <a href="recommendations/get_recommendations.php" class="btn-secondary">My Lessons</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>