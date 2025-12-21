<?php
session_start();
require_once 'config/database.php';
require_once 'ai_analyzer.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$report_id = $data['report_id'];

// Get report details
$stmt = $pdo->prepare(
    "SELECT mwm.*, u.name as student_name, u.email
     FROM mental_wellness_messages mwm
     JOIN users u ON mwm.user_id = u.id
     WHERE mwm.id = ?"
);
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    echo json_encode(['success' => false, 'message' => 'Report not found']);
    exit();
}

// Get anti-ragging committee
$stmt = $pdo->prepare(
    "SELECT u.id, u.email, u.name 
     FROM users u 
     JOIN anti_ragging_committee arc ON u.id = arc.user_id 
     WHERE arc.is_active = 1"
);
$stmt->execute();
$committee = $stmt->fetchAll();

// Send notifications to committee
$notified_count = 0;
foreach ($committee as $member) {
    // Create urgent alert
    $stmt = $pdo->prepare(
        "INSERT INTO alerts (user_id, alert_type, title, message, severity, related_id) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    
    $title = "🚨 URGENT: Anti-Ragging Case Escalated";
    $message = "Case escalated for: " . $report['student_name'] . 
              "\nScore: " . $report['severity_score'] . 
              "\nDepartment: " . $report['department_referred'] . 
              "\n\nImmediate attention required!";
    
    $stmt->execute([
        $member['id'],
        'anti_ragging',
        $title,
        $message,
        'critical',
        $report['user_id']
    ]);
    
    $notified_count++;
}

// Update report status
$stmt = $pdo->prepare(
    "UPDATE mental_wellness_messages 
     SET status = 'escalated' 
     WHERE id = ?"
);
$stmt->execute([$report_id]);

echo json_encode([
    'success' => true,
    'notified_count' => $notified_count,
    'message' => 'Report escalated to anti-ragging committee'
]);
?>