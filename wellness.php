<?php
session_start();
$score = 0;
$message = '';
$show_result = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $q1 = isset($_POST['q1']) ? (int)$_POST['q1'] : 0;
    $q2 = isset($_POST['q2']) ? (int)$_POST['q2'] : 0;
    $q3 = isset($_POST['q3']) ? (int)$_POST['q3'] : 0;
    $score = $q1 + $q2 + $q3;
    $show_result = true;
    if ($score <= 2) {
        $message = "You're doing well, keep it up! 😊";
    } elseif ($score <= 5) {
        $message = "You're okay, but take care of yourself. 💛";
    } else {
        $message = "You may want to reach out to someone. ❤️";
    }
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}
if (isset($_GET['retake'])) {
    $show_result = false;
    if (isset($_SESSION['quiz_result'])) {
        unset($_SESSION['quiz_result']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Mental Wellness Quiz | CampusCare</title>
  <style>
    :root {
      --primary-color: #2E8B57;
      --background-color: #ffffff;
      --text-color: #333333;
      --card-bg: #f8f9fa;
      --border-color: #ddd;
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

    .quiz-container {
      max-width: 600px;
      margin: 0 auto;
      background: var(--card-bg);
      padding: 2rem;
      border-radius: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    h1 {
      text-align: center;
      color: var(--primary-color);
      margin-bottom: 2rem;
    }

    .progress-container {
      width: 100%;
      height: 8px;
      background-color: #e0e0e0;
      border-radius: 4px;
      margin-bottom: 2rem;
      overflow: hidden;
    }

    .dark .progress-container {
      background-color: #444;
    }

    .progress-bar {
      height: 100%;
      background-color: var(--primary-color);
      width: 0%;
      transition: width 0.3s ease;
    }

    .question {
      margin-bottom: 1.5rem;
    }

    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 500;
    }

    select {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid var(--border-color);
      border-radius: 5px;
      background-color: var(--background-color);
      color: var(--text-color);
      font-size: 1rem;
    }

    button {
      background-color: var(--primary-color);
      color: white;
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 5px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s;
      margin: 0.5rem;
    }

    button:hover {
      background-color: #267349;
    }

    #result {
      margin-top: 2rem;
      padding: 1rem;
      border-radius: 5px;
      text-align: center;
      font-size: 1.1rem;
      font-weight: 500;
      display: <?php echo $show_result ? 'block' : 'none'; ?>;
    }

    .dark #result {
      background-color: #333;
    }

    #themeToggle {
      background-color: #666;
    }

    #themeToggle:hover {
      background-color: #555;
    }

    .retake-btn {
      background-color: #6c757d;
    }

    .retake-btn:hover {
      background-color: #5a6268;
    }
  </style>
</head>
<body class="<?php echo isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : ''; ?>">
  <div class="quiz-container">
    <h1>Mental Wellness Self Check</h1>
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
      <button type="submit">Submit</button>
    </form>
    <?php else: ?>
    <div id="result">
      <h2>Your Wellness Score: <?php echo $score; ?>/9</h2>
      <p><?php echo htmlspecialchars($message); ?></p>
      <p><small>Quiz taken on: <?php echo date('F j, Y \a\t g:i A'); ?></small></p>
    </div>
    <div style="text-align: center; margin-top: 1rem;">
      <a href="mental_quiz.php?retake=true" class="retake-btn" style="text-decoration: none;">
        <button class="retake-btn">Retake Quiz</button>
      </a>
      <a href="wellness.php" style="text-decoration: none;">
        <button>Back to Wellness</button>
      </a>
    </div>
    <?php endif; ?>

    <div style="text-align: center; margin-top: 2rem;">
      <button id="themeToggle">Toggle Dark Mode</button>
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
    const themeToggle = document.getElementById('themeToggle');
    themeToggle.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      const isDark = document.body.classList.contains('dark');
      document.cookie = `theme=${isDark ? 'dark' : 'light'}; path=/; max-age=31536000`;
    });
    document.addEventListener('DOMContentLoaded', function() {
      updateProgress();
    });
  </script>
</body>
</html>