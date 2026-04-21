<?php
require_once dirname(__DIR__) . '/includes/initiate.php';

require_login();

// Fetch all questions ordered by domain and facet
$result = $conn->query("
    SELECT question_id, question_text, domain, facet, scale_min, scale_max
    FROM questions
    ORDER BY domain, facet, question_id
");

$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}

// Check for saved progress
$user_id = get_logged_in_user_id();
$stmt    = $conn->prepare("
    SELECT r.question_id, r.response_value
    FROM responses r
    JOIN assessment_sessions s ON r.session_id = s.session_id
    WHERE r.user_id = ? AND s.is_complete = 0
    ORDER BY s.started_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$saved_result = $stmt->get_result();

$saved_responses = [];
while ($row = $saved_result->fetch_assoc()) {
    $saved_responses[$row['question_id']] = $row['response_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EQ Assessment — EQLab</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <span class="nav-brand">EQLab</span>
        <a href="../dashboard.php">Back to Dashboard</a>
    </nav>

    <div class="main-container">
        <h2>Emotional Intelligence Assessment</h2>
        <p>Rate each statement from 1 (Strongly Disagree) to 5 (Strongly Agree).</p>

        <?php if (!empty($saved_responses)): ?>
        <div class="alert alert-info">
            Your previous progress has been restored. Continue from where you left off.
        </div>
        <?php endif; ?>

        <form method="POST" action="submit.php" id="assessment-form">
            <?php
            $current_domain = '';
            $current_facet  = '';
            foreach ($questions as $q):
                // Print domain heading when it changes
                if ($q['domain'] !== $current_domain):
                    if ($current_domain !== '') echo '</div>'; // close prev domain
                    $current_domain = $q['domain'];
                    echo '<div class="domain-section">';
                    echo '<h3>' . clean($q['domain']) . '</h3>';
                    $current_facet = '';
                endif;

                // Print facet subheading when it changes
                if ($q['facet'] !== $current_facet):
                    $current_facet = $q['facet'];
                    echo '<h4>' . clean($q['facet']) . '</h4>';
                endif;
            ?>

            <div class="question-block">
                <p class="question-text"><?= clean($q['question_text']) ?></p>
                <div class="likert-scale">
                    <span class="scale-label">Strongly Disagree</span>
                    <?php for ($i = $q['scale_min']; $i <= $q['scale_max']; $i++): ?>
                    <label class="likert-option">
                        <input
                            type="radio"
                            name="responses[<?= $q['question_id'] ?>]"
                            value="<?= $i ?>"
                            <?= (isset($saved_responses[$q['question_id']]) &&
                                $saved_responses[$q['question_id']] == $i) ? 'checked' : '' ?>
                            required>
                        <span class="likert-num"><?= $i ?></span>
                    </label>
                    <?php endfor; ?>
                    <span class="scale-label">Strongly Agree</span>
                </div>
            </div>

            <?php endforeach; ?>
            </div><!-- close last domain -->

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="saveProgress()">
                    Save Progress
                </button>
                <button type="submit" class="btn-primary">
                    Submit Assessment
                </button>
            </div>
        </form>
    </div>

<script>
function saveProgress() {
    const form    = document.getElementById('assessment-form');
    const data    = new FormData(form);
    data.append('save_only', '1');

    fetch('../assessment/save_progress.php', {
        method: 'POST',
        body: data
    })
    .then(r => r.json())
    .then(res => {
        alert(res.message || 'Progress saved!');
    })
    .catch(() => alert('Could not save progress. Check your connection.'));
}
</script>
</body>
</html>