<?php
require_once dirname(__DIR__) . '/includes/initiate.php';
function get_ai_feedback($profile_type, $weaknesses, $priority_facets, $overall_score) {
    
    $API_KEY = 'YOUR_API_KEY_HERE'; // Store in config, never hardcode in prod
    $API_URL = 'https://api.anthropic.com/v1/messages'; // or OpenAI endpoint

    // Build a structured prompt
    $prompt = "You are an emotional intelligence coach. 
A user completed an EQ assessment with the following results:
- Overall EQ Score: {$overall_score}/100
- EQ Profile Type: {$profile_type}
- Weak Areas: " . implode(', ', $weaknesses) . "
- Priority Development Facets: " . implode(', ', $priority_facets) . "

Provide:
1. A personalized 2-sentence summary of their emotional profile
2. Three specific, actionable improvement tips for their weakest areas
3. One motivational closing statement

Keep the tone supportive and professional. Total response under 200 words.";

    $data = [
        'model'      => 'claude-sonnet-4-20250514',
        'max_tokens' => 400,
        'messages'   => [
            ['role' => 'user', 'content' => $prompt]
        ]
    ];

    $ch = curl_init($API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'x-api-key: ' . $API_KEY,
            'anthropic-version: 2023-06-01'
        ]
    ]);

    $response = curl_exec($ch);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return 'AI feedback temporarily unavailable. Please try again later.';
    }

    $decoded = json_decode($response, true);
    return $decoded['content'][0]['text'] ?? 'Unable to generate feedback.';
}

// Save AI feedback back to eq_profiles
function save_ai_feedback($conn, $session_id, $feedback_text) {
    $stmt = $conn->prepare(
        "UPDATE eq_profiles SET ai_feedback = ? WHERE session_id = ?"
    );
    $stmt->bind_param("si", $feedback_text, $session_id);
    $stmt->execute();
}
?>