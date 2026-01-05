<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';
require_once 'includes/MentalHealthAnalyzer.php';

<<<<<<< HEAD
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
=======
>>>>>>> 858f12862a83dbb1242a2e7cae30e34c263f6b61
$analyzer = new MentalHealthAnalyzer($pdo);

if ($_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$message = $_POST['message'];

<<<<<<< HEAD
// get latest quiz score (optional but recommended)
$q = $pdo->prepare("SELECT score FROM quiz_results WHERE user_id=? ORDER BY id DESC LIMIT 1");
$q->execute([$user_id]);
$quiz_score = $q->fetchColumn();

$analysis = $analyzer->analyzeMessage($message, $user_id, $quiz_score);

// CLEAR HISTORY
if (isset($_POST['clear_history'])) {
    $stmt = $pdo->prepare(
        "DELETE FROM mental_wellness_messages WHERE user_id = ?"
    );
    $stmt->execute([$_SESSION['user_id']]);
    header("Location: wellness.php?cleared=1");
    exit;
}

=======
>>>>>>> 858f12862a83dbb1242a2e7cae30e34c263f6b61
$score = 0;
$message = '';
$show_result = false;
$analysis = null;
$alerts = [];

/* =========================
   POST HANDLER
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* =========================
       MESSAGE SUBMISSION
    ========================= */
    if (isset($_POST['mental_message'])) {

        $user_id = $_SESSION['user_id'];
        $mental_message = $_POST['mental_message'];
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;

        // AI ANALYSIS
        $analysis = $analyzer->analyzeMessage($mental_message, $user_id);

        // APPLY QUIZ SCORE IF PRESENT
        $quiz_score = $_SESSION['mental_quiz_score'] ?? null;

        if ($quiz_score !== null) {

            // Convert /9 score → /100
            $quiz_weighted = ($quiz_score / 9) * 100;

            // Blend score: 70% AI + 30% quiz
            $analysis['overall_score'] =
                round(($analysis['overall_score'] * 0.7) + ($quiz_weighted * 0.3));

            // Increase risk level if quiz indicates high distress
            if ($quiz_score >= 7 && $analysis['risk_level'] !== 'critical') {
                $analysis['risk_level'] = 'high';
            }
        }

        // STORE MESSAGE
        $stmt = $pdo->prepare(
<<<<<<< HEAD
            "INSERT INTO mental_wellness_messages 
            (user_id, message, keywords, severity_score, ai_analysis, department_referred, anonymous) 
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
=======
    "INSERT INTO mental_wellness_messages 
    (user_id, message, keywords, severity_score, ai_analysis, department_referred, anonymous) 
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);
>>>>>>> 858f12862a83dbb1242a2e7cae30e34c263f6b61

        $keywords_json = json_encode($analysis['found_keywords'] ?? []);
        $analysis_json = json_encode($analysis);
<<<<<<< HEAD

        $stmt->execute([
            $anonymous ? null : $user_id,
            $mental_message,
            $keywords_json,
            $analysis['overall_score'],
            $analysis_json,
            $analysis['department'],
            $anonymous
        ]);
=======
$stmt->execute([
    $anonymous ? null : $user_id,   // hide identity
    $mental_message,
    $keywords_json,
    $analysis['overall_score'],
    $analysis_json,
    $analysis['department'],
    $anonymous
]);
       
>>>>>>> 858f12862a83dbb1242a2e7cae30e34c263f6b61

        $message_id = $pdo->lastInsertId();

        // STORE AI REPORT
        $stmt = $pdo->prepare(
            "INSERT INTO ai_analysis_reports 
            (message_id, keyword_scores, overall_score, risk_level, suggested_actions) 
            VALUES (?, ?, ?, ?, ?)"
        );

        $keyword_scores_json = json_encode($analysis['keyword_scores'] ?? []);
        $suggested_actions = implode('; ', $analysis['suggested_actions']);

        $stmt->execute([
            $message_id,
            $keyword_scores_json,
            $analysis['overall_score'],
            $analysis['risk_level'],
            $suggested_actions
        ]);

        // ALERTS
        if ($analysis['overall_score'] >= 50) {
            $analyzer->createAlert($user_id, $analysis, $message_id);
        }
        // Notify assigned mentor if HIGH or CRITICAL risk
if ($analysis['overall_score'] >= 60) {
    $analyzer->notifyMentor($user_id, $analysis);
}

        if ($analysis['overall_score'] >= 60) {
            $analyzer->notifyMentor($user_id, $analysis);
        }

        if ($analysis['overall_score'] >= 75) {
            $notified_count = $analyzer->notifyAntiRaggingCommittee(
                $user_id,
                $analysis,
                $mental_message
            );
            $alerts[] = "⚠️ High-risk alert sent to $notified_count anti-ragging committee members";
        }

        // USER MESSAGE
        $message = "Your message has been analyzed. Score: " .
            $analysis['overall_score'] . ". Referred to: " . $analysis['department'];

        // CLEAR QUIZ SCORE AFTER USE
        unset($_SESSION['mental_quiz_score']);
    }

    /* =========================
       QUIZ SUBMISSION
    ========================= */
    elseif (isset($_POST['answers']) && is_array($_POST['answers'])) {

        $answers = array_map('intval', $_POST['answers']);
        $score = array_sum($answers);
        $show_result = true;

        if ($score <= 2) {
            $message = "You're doing well, keep it up! 😊";
            $result_category = 'good';
        } elseif ($score <= 5) {
            $message = "You're okay, but take care of yourself. 💛";
            $result_category = 'moderate';
        } else {
            $message = "You may want to reach out to someone. ❤️";
            $result_category = 'high';
        }

        // SAVE QUIZ RESULT
        $stmt = $pdo->prepare(
            "INSERT INTO quiz_results (user_id, quiz_type, score, max_score, result_category) 
            VALUES (?, 'mental_wellness', ?, 9, ?)"
        );
        $stmt->execute([$_SESSION['user_id'], $score, $result_category]);

        $stmt = $pdo->prepare(
            "INSERT INTO wellness_checks (user_id, score) VALUES (?, ?)"
        );
        $stmt->execute([$_SESSION['user_id'], $score]);

        // KEEP SCORE FOR AI BLENDING
        $_SESSION['mental_quiz_score'] = $score;

        $_SESSION['quiz_result'] = [
            'score' => $score,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

// RETAKE QUIZ
if (isset($_GET['retake'])) {
    $show_result = false;
    unset($_SESSION['quiz_result'], $_SESSION['mental_quiz_score']);
}

/* =========================
   FETCH HISTORY + ALERTS
========================= */

$user_messages = [];
$user_alerts = [];

$stmt = $pdo->prepare(
    "SELECT * FROM mental_wellness_messages 
     WHERE user_id = ? 
     ORDER BY created_at DESC LIMIT 5"
);
$stmt->execute([$_SESSION['user_id']]);
$user_messages = $stmt->fetchAll();

$stmt = $pdo->prepare(
    "SELECT * FROM alerts 
     WHERE user_id = ? AND is_read = 0 
     ORDER BY created_at DESC LIMIT 10"
);
$stmt->execute([$_SESSION['user_id']]);
$user_alerts = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mental Wellness & AI Monitoring | CampusCare</title>
<style>
    /* -------- Basic CSS -------- */
    :root {
        --primary-color: #2E8B57;
        --background-color: #fff;
        --text-color: #333;
        --card-bg: #f8f9fa;
        --border-color: #ddd;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --success-color: #28a745;
    }
    .dark {
        --primary-color: #70c4a8ff;
        --background-color: #1a1a1a;
        --text-color: #fff;
        --card-bg: #2d2d2d;
        --border-color: #444;
    }
    body { font-family: sans-serif; background: #52957eff; color: var(--text-color); margin:0; padding:20px; transition:0.3s;}
    .container { max-width:1200px; margin:0 auto; }
    .card { background: var(--card-bg); padding:20px; border-radius:10px; margin-bottom:20px; box-shadow:0 2px 6px rgba(0,0,0,0.1);}
    .card-title { color: var(--primary-color); margin-top:0; border-bottom:2px solid var(--primary-color); padding-bottom:5px;}
    .tab-container { display:flex; margin-bottom:20px; border-bottom:2px solid var(--border-color);}
    .tab { padding:10px 20px; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; }
    .tab.active { border-bottom-color: var(--primary-color); color:var(--primary-color); font-weight:bold;}
    .tab-content { display:none; }
    .tab-content.active { display:block; }
    .btn { background: var(--primary-color); color:#fff; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; margin:5px; }
    .btn:hover { background:#267349; }
    .btn-danger { background: var(--danger-color); }
    .btn-warning { background: var(--warning-color); color:#333; }
    textarea { width:100%; padding:10px; border:1px solid var(--border-color); border-radius:5px; resize:vertical; min-height:120px; margin-bottom:10px; background:var(--background-color); color:var(--text-color);}
    .ai-analysis-result { margin-top:20px; padding:15px; border-radius:8px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color:#fff;}
    .risk-badge { display:inline-block; padding:5px 15px; border-radius:20px; font-weight:bold; margin:5px;}
    .risk-low { background: var(--success-color); }
    .risk-medium { background: var(--warning-color); color:#333;}
    .risk-high { background: #fd7e14; }
    .risk-critical { background: var(--danger-color);}
    .alert-item { padding:10px; margin:10px 0; border-left:4px solid; border-radius:4px; background:var(--card-bg);}
    .alert-danger { border-color: var(--danger-color); }
    .alert-warning { border-color: var(--warning-color); }
    .alert-info { border-color: var(--primary-color); }
    .theme-toggle{
   position:fixed;
   right:20px;
   top:20px;
   padding:8px 12px;
   border-radius:50%;
   border:none;
   cursor:pointer;
   font-size:18px;
}

</style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme']==='dark'?'dark':'';?>">
<div class="container" >
            <button id="toggleTheme" class="theme-toggle" style="background: #52957eff;">☾</button>
    <div class="tab-container">
        <div class="tab active" onclick="switchTab('message', this)">Share Message</div>
<div class="tab" onclick="switchTab('quiz', this)">Wellness Quiz</div>
<div class="tab" onclick="switchTab('history', this)">My History</div>
<div class="tab" onclick="switchTab('alerts', this)">
   Alerts (<?php echo count($user_alerts); ?>)
</div>


    <!-- Message Tab -->
    <div id="messageTab" class="tab-content active">
        <div class="card">
            <h2 class="card-title">Share Your Thoughts</h2>
            <?php if(!empty($alerts)) foreach($alerts as $alert): ?>
                <div class="alert-item alert-warning"><?php echo htmlspecialchars($alert); ?></div>
            <?php endforeach; ?>
            <form method="POST">
                <textarea name="mental_message" placeholder="How are you feeling today?" required></textarea>
                <div>
                    <input type="checkbox" id="anonymous" name="anonymous">
                    <label for="anonymous">Post anonymously</label>
                </div>
                <button type="submit" class="btn">Analyze & Submit</button>
            </form>
           <?php if($analysis): ?>
    <div class="ai-analysis-result">
        <h3>AI Analysis Result</h3>
        <p><strong>Score:</strong> <?php echo $analysis['overall_score']; ?>/100</p>
        <p><strong>Risk Level:</strong> 
            <span class="risk-badge risk-<?php echo $analysis['risk_level'];?>"><?php echo ucfirst($analysis['risk_level']);?></span>
        </p>
        <p><strong>Department:</strong> <?php echo $analysis['department'];?></p>
    </div>

    <?php
    if ($analysis['risk_level'] == 'critical') 
        echo "<div class='alert alert-danger'>🚨 Critical Mental Health Risk — Please seek support immediately</div>";

    elseif ($analysis['risk_level'] == 'high') 
        echo "<div class='alert alert-warning'>⚠ High Mental Health Risk Detected</div>";

    elseif ($analysis['risk_level'] == 'medium') 
        echo "<div class='alert alert-info'>🧠 Moderate Emotional Stress</div>";

    else 
        echo "<div class='alert alert-success'>😊 You seem emotionally stable</div>";
    ?>
<?php endif; ?>

        </div>
    </div>

    <!-- Quiz Tab -->
    <div id="quizTab" class="tab-content">
        <div class="card">
            <h2 class="card-title">Mental Wellness Quiz</h2>
            <?php if(!$show_result): ?>
                <form method="POST">
                    <p>1. I feel relaxed</p>
                    <input type="radio" name="answers[0]" value="0" required>Never
                    <input type="radio" name="answers[0]" value="1">Sometimes
                    <input type="radio" name="answers[0]" value="2">Often
                    <input type="radio" name="answers[0]" value="3">Always
                    <p>2. I feel anxious</p>
                    <input type="radio" name="answers[1]" value="0" required>Never
                    <input type="radio" name="answers[1]" value="1">Sometimes
                    <input type="radio" name="answers[1]" value="2">Often
                    <input type="radio" name="answers[1]" value="3">Always
                    <p>3. I feel stressed</p>
                    <input type="radio" name="answers[2]" value="0" required>Never
                    <input type="radio" name="answers[2]" value="1">Sometimes
                    <input type="radio" name="answers[2]" value="2">Often
                    <input type="radio" name="answers[2]" value="3">Always
                    <br><br><button type="submit" class="btn">Submit Quiz</button>
                </form>
            <?php else: ?>
                <h3>Your Score: <?php echo $score;?>/9</h3>
                <p><?php echo htmlspecialchars($message); ?></p>
                <a href="wellness.php?retake=true" class="btn">Retake Quiz</a>
            <?php endif;?>
            <?php if(isset($analysis)) : ?>

        </div>
    </div>

    <!-- History Tab -->
    <div id="historyTab" class="tab-content">
        <div class="card">
            <h2 class="card-title">
   Message History
</h2>

<?php if(!empty($user_messages)): ?>
<form method="POST" onsubmit="return confirm('Delete all your past messages?')">
    <button name="clear_history" class="btn btn-danger">
        Clear My History 🗑
    </button>
</form>
<?php endif; ?>

            <?php if(empty($user_messages)) echo "<p>No messages yet.</p>"; ?>
            <?php foreach($user_messages as $msg): $ai = json_decode($msg['ai_analysis'],true); ?>
                <div class="alert-item alert-info">
                    <p><strong>Date:</strong> <?php echo date('M d, Y',strtotime($msg['created_at']));?></p>
                    <p><strong>Message:</strong> <?php echo substr(htmlspecialchars($msg['message']),0,100);?>...</p>
                    <p><strong>Score:</strong> <?php echo $msg['severity_score'];?></p>
                    <p><strong>Department:</strong> <?php echo htmlspecialchars($msg['department_referred']);?></p>
                </div>
            <?php endforeach;?>
        </div>
    </div>

    <!-- Alerts Tab -->
    <div id="alertsTab" class="tab-content">
        <div class="card">
            <h2 class="card-title">Your Alerts</h2>
            <?php if(empty($user_alerts)) echo "<p>No new alerts.</p>"; ?>
            <?php foreach($user_alerts as $alert): ?>
                <div class="alert-item alert-<?php echo $alert['severity'];?>">
                    <strong><?php echo htmlspecialchars($alert['title']);?></strong>
                    <p><?php echo htmlspecialchars($alert['message']);?></p>
                    <small><?php echo date('M d, Y',strtotime($alert['created_at']));?></small>
                </div>
            <?php endforeach;?>
        </div>
    </div>

</div>

<script>
    function switchTab(tab, el){
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
  document.getElementById(tab+'Tab').classList.add('active');
}

  document.addEventListener("DOMContentLoaded", () => {
  const toggleBtn = document.getElementById('toggleTheme');
toggleBtn.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  toggleBtn.textContent = document.body.classList.contains('dark') ? '☀︎' : '☾';
});
  });
</script>
</body>
<?php if(isset($_GET['cleared'])): ?>
<div style="background:#d1ffd6;padding:10px;border-radius:8px;margin-bottom:10px;">
   ✔ Your history has been cleared
</div>
<?php endif;?>
</html>