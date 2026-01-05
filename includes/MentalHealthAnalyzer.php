<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Block direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: mental_quiz.php");
    exit;
}

// Validate answers
if (!isset($_POST['answers']) || !is_array($_POST['answers'])) {
    die("Invalid quiz submission.");
}

$user_id = $_SESSION['user_id'];
$answers = $_POST['answers'];

// Calculate score
$score = array_sum(array_map('intval', $answers));

// Determine wellness status
if ($score <= 5) {
    $status = 'Poor';
} elseif ($score <= 8) {
    $status = 'Moderate';
} else {
    $status = 'Good';
}

// Insert into database
$stmt = $pdo->prepare("
    INSERT INTO wellness_checks (user_id, score, status, check_date)
    VALUES (?, ?, ?, NOW())
");
$stmt->execute([$user_id, $score, $status]);

// Redirect back to dashboard
header("Location: student_dashboard.php?quiz=success");
exit;
?>