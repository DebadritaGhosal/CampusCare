<?php
session_start();
require_once 'config/database.php';

class MentalHealthAnalyzer {
    private $pdo;
    private $keywordPatterns = [
        'depression' => ['depressed', 'sad', 'hopeless', 'worthless', 'suicidal', 'empty', 'numb'],
        'anxiety' => ['anxious', 'panic', 'worried', 'scared', 'fear', 'nervous'],
        'stress' => ['stressed', 'overwhelmed', 'pressure', 'burdened', 'burnout'],
        'academic' => ['grades', 'fail', 'exam', 'study', 'assignment', 'deadline'],
        'social' => ['lonely', 'alone', 'isolated', 'friends', 'social', 'avoid'],
        'sleep' => ['insomnia', 'sleep', 'tired', 'exhausted', 'fatigue'],
        'eating' => ['eating', 'food', 'weight', 'appetite', 'binge'],
        'self_harm' => ['cut', 'hurt', 'pain', 'suicide', 'die', 'kill'],
        'abuse' => ['bully', 'harass', 'abuse', 'threat', 'force', 'pressure'],
        'substance' => ['drink', 'alcohol', 'drug', 'smoke', 'addict']
    ];
    
    private $severityScores = [
        'depression' => 8,
        'anxiety' => 6,
        'stress' => 5,
        'academic' => 4,
        'social' => 5,
        'sleep' => 4,
        'eating' => 6,
        'self_harm' => 10,
        'abuse' => 9,
        'substance' => 7
    ];
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function analyzeMessage($message, $user_id) {
        $message = strtolower($message);
        $foundKeywords = [];
        $scores = [];
        $totalScore = 0;
        
        // Analyze for each pattern
        foreach ($this->keywordPatterns as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $foundKeywords[$category][] = $keyword;
                    if (!isset($scores[$category])) {
                        $scores[$category] = $this->severityScores[$category];
                    }
                }
            }
        }
        
        // Calculate total score (average of category scores)
        if (!empty($scores)) {
            $totalScore = array_sum($scores) / count($scores) * 10;
            $totalScore = min(100, $totalScore);
        }
        
        // Determine department
        $department = $this->determineDepartment($foundKeywords, $totalScore);
        
        // Determine risk level
        $riskLevel = $this->getRiskLevel($totalScore);
        
        // Get suggested actions
        $suggestedActions = $this->getSuggestedActions($foundKeywords, $totalScore);
        
        return [
            'found_keywords' => $foundKeywords,
            'category_scores' => $scores,
            'overall_score' => $totalScore,
            'risk_level' => $riskLevel,
            'department' => $department,
            'suggested_actions' => $suggestedActions
        ];
    }
    
    private function determineDepartment($foundKeywords, $score) {
        if ($score >= 75) {
            return 'Anti-Ragging Committee';
        }
        
        if (isset($foundKeywords['self_harm']) || isset($foundKeywords['abuse'])) {
            return 'Anti-Ragging Committee';
        }
        
        if (isset($foundKeywords['depression']) && count($foundKeywords['depression']) >= 2) {
            return 'Counselling Center';
        }
        
        if (isset($foundKeywords['academic']) && isset($foundKeywords['stress'])) {
            return 'Academic Support';
        }
        
        return 'Counselling Center';
    }
    
    private function getRiskLevel($score) {
        if ($score >= 75) return 'critical';
        if ($score >= 50) return 'high';
        if ($score >= 25) return 'medium';
        return 'low';
    }
    
    private function getSuggestedActions($keywords, $score) {
        $actions = [];
        
        if ($score >= 75) {
            $actions[] = 'Immediate escalation to anti-ragging committee';
            $actions[] = 'Notify college administration';
            $actions[] = 'Schedule urgent counselling session';
        }
        
        if (isset($keywords['self_harm'])) {
            $actions[] = '24/7 crisis helpline contact';
            $actions[] = 'Emergency counselling required';
        }
        
        if (isset($keywords['academic'])) {
            $actions[] = 'Academic advisor consultation';
            $actions[] = 'Study skills workshop';
        }
        
        if ($score >= 50) {
            $actions[] = 'Schedule counselling appointment';
            $actions[] = 'Peer support group recommendation';
        }
        
        return $actions;
    }
    
    public function createAlert($user_id, $analysis, $message_id) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO alerts (user_id, alert_type, title, message, severity, related_id) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $title = "Mental Health Alert - " . ucfirst($analysis['risk_level']) . " Risk";
        $message = "Student has been flagged with risk score: " . $analysis['overall_score'] . 
                  ". Referred to: " . $analysis['department'];
        $severity = $analysis['overall_score'] >= 75 ? 'critical' : 'warning';
        
        return $stmt->execute([
            $user_id,
            'mental_health',
            $title,
            $message,
            $severity,
            $message_id
        ]);
    }
    
    public function notifyAntiRaggingCommittee($user_id, $analysis, $message_text) {
        // Get anti-ragging committee members
        $stmt = $this->pdo->prepare(
            "SELECT u.id, u.email, u.name 
             FROM users u 
             JOIN anti_ragging_committee arc ON u.id = arc.user_id 
             WHERE arc.is_active = 1"
        );
        $stmt->execute();
        $committee = $stmt->fetchAll();
        
        foreach ($committee as $member) {
            $this->sendAlertToMember($member, $user_id, $analysis, $message_text);
        }
        
        return count($committee);
    }
    
    private function sendAlertToMember($member, $student_id, $analysis, $message_text) {
        $stmt = $this->pdo->prepare(
            "INSERT INTO alerts (user_id, alert_type, title, message, severity, related_id) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $title = "⚠️ Anti-Ragging Alert - Critical Case";
        $message = "Student ID: " . $student_id . 
                  "\nRisk Score: " . $analysis['overall_score'] . 
                  "\nKeywords Found: " . implode(', ', array_keys($analysis['found_keywords'])) .
                  "\nMessage Excerpt: " . substr($message_text, 0, 200) . "...";
        
        return $stmt->execute([
            $member['id'],
            'anti_ragging',
            $title,
            $message,
            'critical',
            $student_id
        ]);
    }
}
?>