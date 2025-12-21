<?php
session_start();
require_once 'config/database.php';
require_once 'ai_analyzer.php';

// Initialize AI Analyzer
$analyzer = new MentalHealthAnalyzer($pdo);

$score = 0;
$message = '';
$show_result = false;
$analysis = null;
$alerts = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['mental_message'])) {
        // Process mental wellness message
        $user_id = $_SESSION['user_id'];
        $mental_message = $_POST['mental_message'];
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;
        
        // AI Analysis
        $analysis = $analyzer->analyzeMessage($mental_message, $user_id);
        
        // Store message in database
        $stmt = $pdo->prepare(
            "INSERT INTO mental_wellness_messages 
            (user_id, message, keywords, severity_score, ai_analysis, department_referred) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $keywords_json = json_encode($analysis['found_keywords']);
        $analysis_json = json_encode($analysis);
        
        $stmt->execute([
            $user_id,
            $mental_message,
            $keywords_json,
            $analysis['overall_score'],
            $analysis_json,
            $analysis['department']
        ]);
        
        $message_id = $pdo->lastInsertId();
        
        // Store AI analysis report
        $stmt = $pdo->prepare(
            "INSERT INTO ai_analysis_reports 
            (message_id, keyword_scores, overall_score, risk_level, suggested_actions) 
            VALUES (?, ?, ?, ?, ?)"
        );
        
        $keyword_scores_json = json_encode($analysis['category_scores']);
        $suggested_actions = implode('; ', $analysis['suggested_actions']);
        
        $stmt->execute([
            $message_id,
            $keyword_scores_json,
            $analysis['overall_score'],
            $analysis['risk_level'],
            $suggested_actions
        ]);
        
        // Create alert if score is high
        if ($analysis['overall_score'] >= 50) {
            $analyzer->createAlert($user_id, $analysis, $message_id);
        }
        
        // Notify anti-ragging committee if score >= 75
        if ($analysis['overall_score'] >= 75) {
            $notified_count = $analyzer->notifyAntiRaggingCommittee($user_id, $analysis, $mental_message);
            $alerts[] = "⚠️ High-risk alert sent to $notified_count anti-ragging committee members";
        }
        
        $message = "Your message has been analyzed. Score: " . $analysis['overall_score'] . 
                  ". Referred to: " . $analysis['department'];
        
    } else {
        // Process quiz
        $q1 = isset($_POST['q1']) ? (int)$_POST['q1'] : 0;
        $q2 = isset($_POST['q2']) ? (int)$_POST['q2'] : 0;
        $q3 = isset($_POST['q3']) ? (int)$_POST['q3'] : 0;
        $score = $q1 + $q2 + $q3;
        $show_result = true;
        
        // Store quiz results
        $user_id = $_SESSION['user_id'];
        $result_category = '';
        
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
        
        $stmt = $pdo->prepare(
            "INSERT INTO quiz_results (user_id, quiz_type, score, max_score, result_category) 
             VALUES (?, 'mental_wellness', ?, 9, ?)"
        );
        $stmt->execute([$user_id, $score, $result_category]);
        
        $_SESSION['quiz_result'] = [
            'score' => $score,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}

if (isset($_GET['retake'])) {
    $show_result = false;
    if (isset($_SESSION['quiz_result'])) {
        unset($_SESSION['quiz_result']);
    }
}

// Get user's recent messages and alerts
$user_messages = [];
$user_alerts = [];

if (isset($_SESSION['user_id'])) {
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
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mental Wellness & AI Monitoring | CampusCare</title>
  <style>
    :root {
      --primary-color: #2E8B57;
      --background-color: #ffffff;
      --text-color: #333333;
      --card-bg: #f8f9fa;
      --border-color: #ddd;
      --danger-color: #dc3545;
      --warning-color: #ffc107;
      --success-color: #28a745;
    }

    .dark {
      --background-color: #1a1a1a;
      --text-color: #ffffff;
      --card-bg: #2d2d2d;
      --border-color: #444;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--background-color);
      color: var(--text-color);
      margin: 0;
      padding: 20px;
      transition: all 0.3s ease;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    .dashboard-grid {
      display: grid;
      grid-template-columns: 2fr 1fr;
      gap: 30px;
      margin-top: 30px;
    }

    .card {
      background: var(--card-bg);
      padding: 1.5rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .card-title {
      color: var(--primary-color);
      margin-top: 0;
      border-bottom: 2px solid var(--primary-color);
      padding-bottom: 10px;
    }

    .message-form textarea {
      width: 100%;
      padding: 15px;
      border: 2px solid var(--border-color);
      border-radius: 8px;
      font-size: 16px;
      resize: vertical;
      min-height: 150px;
      margin-bottom: 15px;
      background: var(--background-color);
      color: var(--text-color);
    }

    .ai-analysis-result {
      margin-top: 20px;
      padding: 15px;
      border-radius: 8px;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }

    .risk-badge {
      display: inline-block;
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: bold;
      margin: 5px;
    }

    .risk-low { background: var(--success-color); }
    .risk-medium { background: var(--warning-color); color: #333; }
    .risk-high { background: #fd7e14; }
    .risk-critical { background: var(--danger-color); }

    .keyword-tag {
      display: inline-block;
      background: rgba(255,255,255,0.2);
      padding: 3px 10px;
      border-radius: 15px;
      margin: 3px;
      font-size: 0.9em;
    }

    .alert-item {
      padding: 12px;
      margin: 10px 0;
      border-left: 4px solid;
      border-radius: 4px;
      background: var(--card-bg);
    }

    .alert-danger { border-color: var(--danger-color); }
    .alert-warning { border-color: var(--warning-color); }
    .alert-info { border-color: var(--primary-color); }

    .message-history {
      max-height: 400px;
      overflow-y: auto;
    }

    .message-item {
      border-left: 3px solid var(--primary-color);
      padding: 10px 15px;
      margin: 10px 0;
      background: var(--card-bg);
    }

    .score-meter {
      height: 10px;
      background: #e0e0e0;
      border-radius: 5px;
      margin: 10px 0;
      overflow: hidden;
    }

    .score-fill {
      height: 100%;
      background: linear-gradient(90deg, var(--success-color), var(--danger-color));
    }

    .tab-container {
      display: flex;
      margin-bottom: 20px;
      border-bottom: 2px solid var(--border-color);
    }

    .tab {
      padding: 10px 20px;
      cursor: pointer;
      border-bottom: 2px solid transparent;
      margin-bottom: -2px;
    }

    .tab.active {
      border-bottom-color: var(--primary-color);
      color: var(--primary-color);
      font-weight: bold;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
    }

    .btn {
      background-color: var(--primary-color);
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.3s;
      margin: 5px;
    }

    .btn:hover {
      background-color: #267349;
      transform: translateY(-2px);
    }

    .btn-danger { background-color: var(--danger-color); }
    .btn-danger:hover { background-color: #c82333; }

    .btn-warning { background-color: var(--warning-color); color: #333; }
    .btn-warning:hover { background-color: #e0a800; }

    .anonymous-check {
      display: flex;
      align-items: center;
      margin: 15px 0;
    }

    .anonymous-check input {
      margin-right: 10px;
    }
  </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : ''; ?>">
  <div class="container">
    <div class="tab-container">
      <div class="tab active" onclick="switchTab('message')">Share Message</div>
      <div class="tab" onclick="switchTab('quiz')">Wellness Quiz</div>
      <div class="tab" onclick="switchTab('history')">My History</div>
      <div class="tab" onclick="switchTab('alerts')">Alerts (<?php echo count($user_alerts); ?>)</div>
    </div>

    <!-- Share Message Tab -->
    <div id="messageTab" class="tab-content active">
      <div class="card">
        <h2 class="card-title">Share Your Thoughts (AI-Powered Analysis)</h2>
        <p>Share how you're feeling. Our AI will analyze your message and direct you to the appropriate support department.</p>
        
        <?php if (!empty($alerts)): ?>
          <?php foreach ($alerts as $alert): ?>
            <div class="alert-item alert-warning">
              <?php echo htmlspecialchars($alert); ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
        
        <form method="POST" action="" class="message-form">
          <textarea name="mental_message" placeholder="How are you feeling today? What's on your mind? (Your message will be analyzed for appropriate support)" required></textarea>
          
          <div class="anonymous-check">
            <input type="checkbox" id="anonymous" name="anonymous">
            <label for="anonymous">Post anonymously (your identity won't be shared)</label>
          </div>
          
          <button type="submit" class="btn">Analyze & Submit</button>
        </form>
        
        <?php if ($analysis): ?>
        <div class="ai-analysis-result">
          <h3>AI Analysis Result</h3>
          <div class="score-meter">
            <div class="score-fill" style="width: <?php echo $analysis['overall_score']; ?>%"></div>
          </div>
          <p><strong>Risk Score:</strong> <?php echo $analysis['overall_score']; ?>/100</p>
          <p><strong>Risk Level:</strong> 
            <span class="risk-badge risk-<?php echo $analysis['risk_level']; ?>">
              <?php echo ucfirst($analysis['risk_level']); ?>
            </span>
          </p>
          <p><strong>Department Referred:</strong> <?php echo $analysis['department']; ?></p>
          
          <?php if (!empty($analysis['found_keywords'])): ?>
          <p><strong>Keywords Detected:</strong>
            <?php foreach ($analysis['found_keywords'] as $category => $keywords): ?>
              <?php foreach ($keywords as $keyword): ?>
                <span class="keyword-tag"><?php echo htmlspecialchars($keyword); ?></span>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </p>
          <?php endif; ?>
          
          <h4>Suggested Actions:</h4>
          <ul>
            <?php foreach ($analysis['suggested_actions'] as $action): ?>
              <li><?php echo htmlspecialchars($action); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quiz Tab -->
    <div id="quizTab" class="tab-content">
      <div class="card">
        <h2 class="card-title">Mental Wellness Self Check</h2>
        <div class="progress-container">
          <div class="progress-bar" id="progressBar"></div>
        </div>
        
        <?php if (!$show_result): ?>
        <form id="quizForm" method="POST" action="">
          <div class="question">
            <label for="q1">1. How often have you been feeling down lately?</label>
            <select id="q1" name="q1" required>
              <option value="">Select</option>
              <option value="0">Not at all</option>
              <option value="1">Sometimes</option>
              <option value="2">Often</option>
              <option value="3">Almost always</option>
            </select>
          </div>
          <div class="question">
            <label for="q2">2. How well are you sleeping?</label>
            <select id="q2" name="q2" required>
              <option value="">Select</option>
              <option value="0">Very well</option>
              <option value="1">Okay</option>
              <option value="2">Poorly</option>
              <option value="3">Terribly</option>
            </select>
          </div>
          <div class="question">
            <label for="q3">3. How often do you feel anxious?</label>
            <select id="q3" name="q3" required>
              <option value="">Select</option>
              <option value="0">Rarely</option>
              <option value="1">Sometimes</option>
              <option value="2">Frequently</option>
              <option value="3">All the time</option>
            </select>
          </div>
          <button type="submit" class="btn">Submit</button>
        </form>
        <?php else: ?>
        <div id="result">
          <h2>Your Wellness Score: <?php echo $score; ?>/9</h2>
          <p><?php echo htmlspecialchars($message); ?></p>
          <p><small>Quiz taken on: <?php echo date('F j, Y \a\t g:i A'); ?></small></p>
          
          <?php if ($score >= 6): ?>
            <div class="alert-item alert-warning">
              <strong>Recommendation:</strong> Consider scheduling a counselling session. 
              Your score indicates you might benefit from professional support.
            </div>
          <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 1rem;">
          <a href="wellness.php?retake=true" class="btn">Retake Quiz</a>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- History Tab -->
    <div id="historyTab" class="tab-content">
      <div class="card">
        <h2 class="card-title">Message History</h2>
        <div class="message-history">
          <?php if (empty($user_messages)): ?>
            <p>No messages yet. Share how you're feeling!</p>
          <?php else: ?>
            <?php foreach ($user_messages as $msg): 
              $ai_data = json_decode($msg['ai_analysis'], true);
            ?>
              <div class="message-item">
                <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></p>
                <p><strong>Message:</strong> <?php echo substr(htmlspecialchars($msg['message']), 0, 150); ?>...</p>
                <p><strong>Score:</strong> <?php echo $msg['severity_score']; ?>/100</p>
                <p><strong>Referred to:</strong> <?php echo htmlspecialchars($msg['department_referred']); ?></p>
                <p><strong>Status:</strong> 
                  <span class="risk-badge risk-<?php echo strtolower($msg['status']); ?>">
                    <?php echo ucfirst($msg['status']); ?>
                  </span>
                </p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Alerts Tab -->
    <div id="alertsTab" class="tab-content">
      <div class="card">
        <h2 class="card-title">Your Alerts & Notifications</h2>
        <?php if (empty($user_alerts)): ?>
          <p>No new alerts.</p>
        <?php else: ?>
          <?php foreach ($user_alerts as $alert): ?>
            <div class="alert-item alert-<?php echo $alert['severity']; ?>">
              <strong><?php echo htmlspecialchars($alert['title']); ?></strong><br>
              <p><?php echo htmlspecialchars($alert['message']); ?></p>
              <small><?php echo date('M d, Y H:i', strtotime($alert['created_at'])); ?></small>
              <button class="btn" onclick="markAlertRead(<?php echo $alert['id']; ?>)">Mark as Read</button>
            </div>
          <?php endforeach; ?>
          <button class="btn" onclick="markAllAlertsRead()">Mark All as Read</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    const selects = document.querySelectorAll('select');
    const progressBar = document.getElementById('progressBar');
    
    function updateProgress() {
      const total = selects.length;
      let filled = 0;
      selects.forEach(select => {
        if (select.value) filled++;
      });
      const percent = Math.round((filled / total) * 100);
      progressBar.style.width = percent + "%";
    }
    
    selects.forEach(select => {
      select.addEventListener('change', updateProgress);
    });
    
    function switchTab(tabName) {
      // Update tabs
      document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
      });
      event.target.classList.add('active');
      
      // Update content
      document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
      });
      document.getElementById(tabName + 'Tab').classList.add('active');
    }
    
    function markAlertRead(alertId) {
      fetch('mark_alert_read.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ alert_id: alertId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          event.target.closest('.alert-item').style.display = 'none';
          // Update alert count in tab
          const alertTab = document.querySelector('[onclick="switchTab(\'alerts\')"]');
          const currentCount = parseInt(alertTab.textContent.match(/\((\d+)\)/)[1] || 0);
          alertTab.textContent = alertTab.textContent.replace(/\(\d+\)/, `(${currentCount - 1})`);
        }
      });
    }
    
    function markAllAlertsRead() {
      fetch('mark_all_alerts_read.php', {
        method: 'POST'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.querySelectorAll('.alert-item').forEach(alert => {
            alert.style.display = 'none';
          });
          const alertTab = document.querySelector('[onclick="switchTab(\'alerts\')"]');
          alertTab.textContent = alertTab.textContent.replace(/\(\d+\)/, '(0)');
        }
      });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
      updateProgress();
    });
  </script>
</body>
</html>