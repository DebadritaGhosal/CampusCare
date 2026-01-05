<?php

class MentalHealthAnalyzer {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // ==============================
    // QUIZ ANALYSIS
    // ==============================
    public function analyzeQuiz(array $answers) {

        $score = array_sum(array_map('intval', $answers));

        if ($score <= 2) $status = 'good';
        elseif ($score <= 5) $status = 'moderate';
        else $status = 'high';

        return [
            'score' => $score,
            'status' => $status
        ];
    }


    // ==============================
    // SAVE QUIZ RESULT
    // ==============================
    public function saveQuizResult($userId, $score, $status) {

        $stmt = $this->pdo->prepare("
            INSERT INTO quiz_results (user_id, quiz_type, score, max_score, result_category)
            VALUES (?, 'mental_wellness', ?, 9, ?)
        ");

        $stmt->execute([$userId, $score, $status]);
    }


    // ==============================
    // MESSAGE + QUIZ AI ANALYSIS
    // ==============================
    public function analyzeMessage($message, $user_id, $quiz_score = null)
    {
        $text = strtolower($message);

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
                    if (!isset($found[$name])) $found[$name] = [];
                    $found[$name][] = $w;
                    $score += $cat['weight'];
                }
            }
        }

        // QUIZ adds up to 30 score
        if ($quiz_score !== null) {
            $score += ($quiz_score / 9) * 30;
        }

        // Cap score 0-100
        $score = max(0, min($score, 100));

        // RISK LEVELS
        if ($score >= 80) $risk = 'critical';
        elseif ($score >= 60) $risk = 'high';
        elseif ($score >= 30) $risk = 'medium';
        else $risk = 'low';

        // Save AI result
        $this->saveAnalysis($user_id, $message, $score, $risk);

        // Notify mentor if needed
        if (in_array($risk, ['high', 'critical'])) {
            $this->notifyMentor($user_id, $score, $risk);
        }

        return [
            'overall_score' => $score,
            'risk_level' => $risk,
            'found_keywords' => $found
        ];
    }

if ($analysis['risk_level'] == 'critical') 
    echo "<div class='alert alert-danger mt-3'>
            🚨 <strong>Critical Mental Health Risk</strong><br>
            Please seek professional help immediately.
          </div>";

elseif ($analysis['risk_level'] == 'high') 
    echo "<div class='alert alert-warning mt-3'>
            ⚠ <strong>High Risk Detected</strong><br>
            Consider counselling support soon.
          </div>";

elseif ($analysis['risk_level'] == 'medium') 
    echo "<div class='alert alert-info mt-3'>
            🧠 <strong>Moderate Emotional Stress</strong><br>
            Try self-care & reach out to someone you trust.
          </div>";

else 
    echo "<div class='alert alert-success mt-3'>
            😊 <strong>You seem emotionally stable.</strong>
          </div>";

    // ==============================
    // SAVE ANALYSIS HISTORY
    // ==============================
    private function saveAnalysis($userId, $message, $score, $risk)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO mental_health_analysis (user_id, message, overall_score, risk_level)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $message, $score, $risk]);
    }


    // ==============================
    // ALERT MENTOR – NO DUPLICATES 24HRS
    // ==============================
    public function notifyMentor($userId, $score, $risk)
    {
        // Prevent duplicate alerts in last 24hrs
        $stmt = $this->pdo->prepare("
            SELECT id FROM mentor_alerts
            WHERE user_id = ? 
            AND created_at >= NOW() - INTERVAL 1 DAY
        ");
        $stmt->execute([$userId]);

        if ($stmt->rowCount() > 0) return;

        // Get assigned mentor (if any)
        $mentorQuery = $this->pdo->prepare("
            SELECT mentor_id FROM signup_details WHERE id = ?
        ");
        $mentorQuery->execute([$userId]);
        $mentorId = $mentorQuery->fetchColumn();

        // Insert alert
        $stmt = $this->pdo->prepare("
            INSERT INTO mentor_alerts (user_id, mentor_id, score, risk_level)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$userId, $mentorId, $score, $risk]);
    }
}

?>