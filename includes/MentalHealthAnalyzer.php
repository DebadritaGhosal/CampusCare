<?php

class MentalHealthAnalyzer {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // Analyze wellness quiz answers
    public function analyzeQuiz(array $answers) {
        $score = array_sum(array_map('intval', $answers));

        if ($score <= 2) {
            $status = 'good';
            $message = "You're doing well, keep it up! 😊";
        } elseif ($score <= 5) {
            $status = 'moderate';
            $message = "You're okay, but take care of yourself 💛";
        } else {
            $status = 'high';
            $message = "You may want to reach out to someone ❤️";
        }

        return [
            'score' => $score,
            'status' => $status,
            'message' => $message
        ];
    }

    // Store quiz result
    public function saveQuizResult($userId, $score, $status) {

        // quiz_results table
        $stmt = $this->pdo->prepare("
            INSERT INTO quiz_results (user_id, quiz_type, score, max_score, result_category)
            VALUES (?, 'mental_wellness', ?, 9, ?)
        ");
        $stmt->execute([$userId, $score, $status]);

        // wellness_checks table
        $stmt = $this->pdo->prepare("
            INSERT INTO wellness_checks (user_id, score)
            VALUES (?, ?)
        ");
        $stmt->execute([$userId, $score]);
    }

    // Analyze message text (basic keyword demo)
    public function analyzeMessage($text, $userId)
    {
    $text = strtolower($text);

    $categories = [
        'self_harm' => [
            'words' => ['suicide','kill myself','cut','end my life','die','worthless','self harm'],
            'weight' => 40
        ],
        'bullying' => [
            'words' => ['ragging','threat','harass','abuse','bullied','beaten','forced'],
            'weight' => 30
        ],
        'depression' => [
            'words' => ['sad','empty','hopeless','lost','alone','cry','anxious','panic'],
            'weight' => 25
        ],
        'stress' => [
            'words' => ['pressure','overwhelmed','burnout','workload','tired'],
            'weight' => 15
        ]
    ];

    $found = [];
    $score = 0;

    foreach ($categories as $name => $cat) {
        foreach ($cat['words'] as $w) {
            if (strpos($text, $w) !== false) {
                $found[$name][] = $w;
                $score += $cat['weight'];
            }
        }
    }

    $risk = 'low';
    if ($score >= 90) $risk = 'critical';
    elseif ($score >= 60) $risk = 'high';
    elseif ($score >= 30) $risk = 'medium';

    return [
        'overall_score' => min($score,100),
        'risk_level' => $risk,
        'found_keywords' => $found,
        'department' => ($risk === 'high' || $risk === 'critical') ? 
            'Counselling Cell' : 'General Support',
        'suggested_actions' => [
            "Talk to someone you trust",
            "Schedule counselling support",
            "Avoid isolation",
            "Seek immediate help if unsafe"
        ]
    ];
}
public function notifyMentor($userId, $analysis){
  $stmt = $this->pdo->prepare("
   INSERT INTO mentor_alerts(user_id, mentor_id, score, risk_level)
   VALUES(?, (SELECT mentor_id FROM signup_details WHERE id=?), ?, ?)
  ");
  $stmt->execute([$userId,$userId,$analysis['overall_score'],$analysis['risk_level']]);
}

    // Example alert creator
    public function createAlert($userId, $analysis, $messageId = null) {
        $stmt = $this->pdo->prepare("
            INSERT INTO alerts (user_id, title, message, severity)
            VALUES (?, 'Mental wellness alert', 'High-risk indicators detected', ?)
        ");
        $stmt->execute([$userId, $analysis['risk_level']]);
    }

    // Dummy notifier
    public function notifyAntiRaggingCommittee($userId, $analysis, $message) {
        return 3; // suppose 3 members notified
    }
}
?>