<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Only mentors can access this page
if ($_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
    exit();
}

// Get mentor details
$mentor = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching mentor details: " . $e->getMessage();
}

// Fetch stats
$stats = [
    'assigned_students' => 0,
    'wellness_sessions' => 0,
    'support_sessions' => 0,
    'latest_wellness_score' => 'N/A'
];

// Assigned students count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM mentorship_assignments WHERE mentor_id = ? AND status='active'");
$stmt->execute([$_SESSION['user_id']]);
$stats['assigned_students'] = (int)$stmt->fetchColumn();

// Wellness sessions count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM wellness_checks WHERE mentor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['wellness_sessions'] = (int)$stmt->fetchColumn();

// Support sessions count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_sessions WHERE mentor_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['support_sessions'] = (int)$stmt->fetchColumn();

// Latest wellness score
$stmt = $pdo->prepare("SELECT score FROM wellness_checks WHERE mentor_id = ? ORDER BY check_date DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$stats['latest_wellness_score'] = $stmt->fetchColumn() ?? 'N/A';

// Fetch recent assigned students
$recent_students = $pdo->prepare("SELECT u.id, u.name, u.email FROM mentorship_assignments ma JOIN users u ON ma.student_id = u.id WHERE ma.mentor_id = ? AND ma.status='active' ORDER BY u.name ASC LIMIT 10");
$recent_students->execute([$_SESSION['user_id']]);
$recent_students = $recent_students->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent wellness history for assigned students
$wellness_history = $pdo->prepare("SELECT u.name AS student_name, w.score, w.status, w.check_date AS date FROM wellness_checks w JOIN users u ON w.user_id = u.id WHERE u.id IN (SELECT student_id FROM mentorship_assignments WHERE mentor_id = ? AND status='active') ORDER BY w.check_date DESC LIMIT 10");
$wellness_history->execute([$_SESSION['user_id']]);
$wellness_history = $wellness_history->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent support sessions
$support_sessions = $pdo->prepare("SELECT s.id, u.name AS student_name, s.last_message, s.last_date FROM support_sessions s JOIN users u ON s.user_id = u.id WHERE s.mentor_id = ? ORDER BY s.last_date DESC LIMIT 10");
$support_sessions->execute([$_SESSION['user_id']]);
$support_sessions = $support_sessions->fetchAll(PDO::FETCH_ASSOC);

$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tab1','tab2','tab3']) ? $_GET['tab'] : 'tab1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mentor Dashboard | CampusCare</title>
<style>
/* Copy all CSS from student dashboard exactly */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
body { background:#f5f5f5; color:#333; line-height:1.6; }
.dashboard-container { display:flex; min-height:100vh; }
.sidebar { width:220px; background:#11965aff; color:#fff; padding:30px 20px; display:flex; flex-direction:column; justify-content:space-between; }
.sidebar h2 { text-align:center; margin-bottom:30px; font-size:22px; }
.sidebar nav ul { list-style:none; }
.sidebar nav ul li { margin-bottom:15px; }
.sidebar nav ul li a { color:#fff; text-decoration:none; display:block; padding:10px 15px; border-radius:8px; transition: background 0.3s; }
.sidebar nav ul li a:hover { background:rgba(255,255,255,0.15); }
.main-content { flex:1; padding:30px; background:#f5f5f5; }
.profile-header { display:flex; align-items:center; gap:20px; margin-bottom:30px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 3px 6px rgba(0,0,0,0.1); }
.profile-header h1 { font-size:24px; }
.profile-header p { color:#555; }
.profile-picture { border-radius:50%; width:80px; height:80px; object-fit:cover; border:3px solid #16875fff; transition: transform 0.3s ease; }
.profile-picture:hover { transform:scale(1.1); }
.quick-actions { display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap; }
.action-btn { flex:1 1 200px; background:#fff; padding:20px; border-radius:12px; text-align:center; cursor:pointer; box-shadow:0 3px 6px rgba(0,0,0,0.1); transition:all 0.3s; }
.action-btn:hover { transform:translateY(-3px); box-shadow:0 6px 12px rgba(0,0,0,0.15); }
.action-btn h3 { margin-bottom:10px; font-size:18px; }
.action-btn p { font-size:14px; color:#555; }
.card_container{ display:flex; gap:30px; margin-bottom:30px; }
.card{ background:#fff; display:flex; flex-direction:column; gap:10px; align-items:center; justify-content:center; padding:14px 50px; border-radius:20px; box-shadow:0 3px 6px rgba(0,0,0,0.1); }
.card img{ height:30px; width:30px; }
.card h4{ font-size:25px; }
.card p{ font-size:15px; }
.tab-buttons { display:flex; justify-content:center; gap:0; margin:30px 0; border-radius:20px; overflow:hidden; background:#e0e0e0; }
.tab-buttons button { padding:10px 100px; background:transparent; border:none; cursor:pointer; transition:background 0.3s; }
.tab-buttons button:hover { background:#059668c2; color:#fff; }
.tab-buttons button.active { background:#059668; color:#fff; }
.tab-content { display:none; }
.tab-content.active { display:block; }
.tab-content .parts > * { opacity:0; transform:translateY(20px); transition:all 0.5s ease; }
.tab-content.active .parts > * { opacity:1; transform:translateY(0); }
.tab-content.active .parts > *:nth-child(1){transition-delay:0.1s;}
.tab-content.active .parts > *:nth-child(2){transition-delay:0.2s;}
.tab-content.active .parts > *:nth-child(3){transition-delay:0.3s;}
.tab-content.active .parts > *:nth-child(4){transition-delay:0.4s;}
.tab-content.active .parts > *:nth-child(5){transition-delay:0.5s;}
.theme-toggle { cursor: pointer; font-size: 20px; padding: 8px 12px; border-radius: 50%; border: none; transition: all 0.3s ease; }
body.light .theme-toggle { background: #e0e0e0; color: #111; }
body:not(.light) .theme-toggle { background: #182642; color: #e5e7eb; box-shadow: 0 4px 10px rgba(0,0,0,0.4); }
body:not(.light) .theme-toggle:hover { background: #1f3555; transform: scale(1.15); }
body.light .theme-toggle:hover { background: #059668c2; color: #fff; }
/* Additional dark mode fixes copied from student dashboard */
body.light { background: #fefefe; color: #111; }
body.light .sidebar { background: #06b6d4; }
body.light .tab-buttons button.active { background: #0891b2; }
body.light .card, body.light .profile-header, body.light .action-btn { background: #fff; color: #111; }
body:not(.light) .sidebar { background: rgba(0,0,0,0.1) !important; }
body:not(.light) .main-content{ background: rgb(15, 23, 42) !important; }
body:not(.light) .card, body:not(.light) .profile-header, body:not(.light) .action-btn { background: #182642 !important; color: #e5e7eb; }
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Mentor Portal</h2>
        <nav>
            <ul>
                <li><a href="mentor_dashboard.php">Dashboard</a></li>
                <li><a href="mentor_students.php">My Students</a></li>
                <li><a href="mentor_wellness.php">Wellness History</a></li>
                <li><a href="mentor_support.php">Support Sessions</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <div class="profile-header">
            <?php
            require_once 'components/profile_picture.php';
            displayProfilePicture($_SESSION['user_id'], $_SESSION['name'], $_SESSION['profile_pic'] ?? '', 'medium', $_SESSION['role']);
            ?>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <p>Mentor Dashboard</p>
                <div class="theme-toggle" id="toggleTheme">☾</div>
                <li>✏️<a href="edit_profile.php">Edit Profile</a></li>
            </div>
        </div>

        <div class="quick-actions">
            <div class="action-btn"><h3>My Students</h3><p>View / Manage</p></div>
            <div class="action-btn"><h3>Wellness</h3><p>Check Student Scores</p></div>
            <div class="action-btn"><h3>Support</h3><p>Active Sessions</p></div>
        </div>

        <div class="card_container">
            <div class="card"><img src="users.png"><h4><?php echo $stats['assigned_students']; ?></h4><p>Assigned Students</p></div>
            <div class="card"><img src="heart.png"><h4><?php echo $stats['wellness_sessions']; ?></h4><p>Wellness Sessions</p></div>
            <div class="card"><img src="chat.png"><h4><?php echo $stats['support_sessions']; ?></h4><p>Support Sessions</p></div>
            <div class="card"><img src="progress.png"><h4><?php echo $stats['latest_wellness_score']; ?></h4><p>Latest Wellness</p></div>
        </div>

        <div class="tab-buttons">
            <button onclick="showTab('tab1')" class="<?php echo $active_tab=='tab1'?'active':''; ?>">My Students</button>
            <button onclick="showTab('tab2')" class="<?php echo $active_tab=='tab2'?'active':''; ?>">Wellness History</button>
            <button onclick="showTab('tab3')" class="<?php echo $active_tab=='tab3'?'active':''; ?>">Support Sessions</button>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab1'?'active':''; ?>" id="tab1">
            <div class="parts">
                <?php foreach($recent_students as $student): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                    <p><?php echo htmlspecialchars($student['email']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab2'?'active':''; ?>" id="tab2">
            <div class="parts">
                <?php foreach($wellness_history as $w): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($w['student_name']); ?> - <?php echo htmlspecialchars($w['score']); ?></h4>
                    <p>Status: <?php echo htmlspecialchars($w['status']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab3'?'active':''; ?>" id="tab3">
            <div class="parts">
                <?php foreach($support_sessions as $s): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($s['student_name']); ?></h4>
                    <p>Last message: <?php echo htmlspecialchars($s['last_message']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggleBtn = document.getElementById('toggleTheme');
    toggleBtn.addEventListener('click', () => {
        document.body.classList.toggle('light');
        toggleBtn.textContent = document.body.classList.contains('light') ? '☀︎' : '☾';
    });

    function showTab(tabId){
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab-buttons button').forEach(b => b.classList.remove('active'));
        const tab = document.getElementById(tabId);
        tab.classList.add('active');
        document.querySelector(`.tab-buttons button[onclick="showTab('${tabId}')"]`).classList.add('active');

        const cards = tab.querySelectorAll('.parts > *');
        cards.forEach(card => {
            card.style.opacity = 0;
            card.style.transform = 'translateY(20px)';
            void card.offsetWidth;
            card.style.opacity = 1;
            card.style.transform = 'translateY(0)';
        });
    }
    window.showTab = showTab;
});
</script>
</body>
</html>