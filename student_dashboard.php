<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Only students can access this page
if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Get student details
$student = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM signup_details WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching student details: " . $e->getMessage();
}

// Get assigned mentor
$mentor = [];
try {
    $stmt = $pdo->prepare('SELECT u.name, u.email, m.specialization, m.qualification 
                          FROM mentorship_assignments ma
                          JOIN mentors m ON ma.mentor_id = m.id
                          JOIN users u ON m.user_id = u.id
                          WHERE ma.student_id = ? AND ma.status = "active"');
    $stmt->execute([$student['id']]);
    $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // No mentor assigned yet
}

$stats = [
    'listings' => 0,
    'wellness_checks' => 0,
    'support_sessions' => 0,
    'latest_wellness' => 'N/A'
];

// Total listings
$stmt = $pdo->prepare("SELECT COUNT(*) FROM marketplace WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['listings'] = (int)$stmt->fetchColumn();

// Wellness checks count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM wellness_checks WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['wellness_checks'] = (int)$stmt->fetchColumn();

// Latest wellness score
$stmt = $pdo->prepare("
    SELECT score FROM wellness_checks
    WHERE user_id = ?
    ORDER BY check_date DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$stats['latest_wellness'] = $stmt->fetchColumn() ?? 'N/A';

// Support sessions
$stmt = $pdo->prepare("SELECT COUNT(*) FROM support_sessions WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats['support_sessions'] = (int)$stmt->fetchColumn();

// Dummy data arrays (replace with actual queries)
$listings = $listings ?? [];
$wellness_history = $wellness_history ?? [];
$support_sessions = $support_sessions ?? [];
$active_tab = $active_tab ?? 'tab1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard | CampusCare</title>
<style>
/* --- GENERAL RESET --- */
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI',sans-serif; }
body { background:#f5f5f5; color:#333; line-height:1.6; }
/* --- DASHBOARD LAYOUT --- */
.dashboard-container { display:flex; min-height:100vh; }
/* --- SIDEBAR --- */
.sidebar { width:220px; background:#11965aff; color:#fff; padding:30px 20px; display:flex; flex-direction:column; justify-content:space-between; }
.sidebar h2 { text-align:center; margin-bottom:30px; font-size:22px; }
.sidebar nav ul { list-style:none; }
.sidebar nav ul li { margin-bottom:15px; }
.sidebar nav ul li a { color:#fff; text-decoration:none; display:block; padding:10px 15px; border-radius:8px; transition: background 0.3s; }
.sidebar nav ul li a:hover { background:rgba(255,255,255,0.15); }
/* --- MAIN CONTENT --- */
.main-content { flex:1; padding:30px; background:#f5f5f5; }
/* --- PROFILE HEADER --- */
.profile-header { display:flex; align-items:center; gap:20px; margin-bottom:30px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 3px 6px rgba(0,0,0,0.1); }
.profile-header h1 { font-size:24px; }
.profile-header p { color:#555; }
.profile-picture { border-radius:50%; width:80px; height:80px; object-fit:cover; border:3px solid #16875fff; transition: transform 0.3s ease; }
.profile-picture:hover { transform:scale(1.1); }
/* --- QUICK ACTIONS --- */
.quick-actions { display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap; }
.action-btn { flex:1 1 200px; background:#fff; padding:20px; border-radius:12px; text-align:center; cursor:pointer; box-shadow:0 3px 6px rgba(0,0,0,0.1); transition:all 0.3s; }
.action-btn:hover { transform:translateY(-3px); box-shadow:0 6px 12px rgba(0,0,0,0.15); }
.action-btn h3 { margin-bottom:10px; font-size:18px; }
.action-btn p { font-size:14px; color:#555; }
/* --- STAT CARDS --- */
.card_container{ display:flex; gap:30px; margin-bottom:30px; }
.card{ background:#fff; display:flex; flex-direction:column; gap:10px; align-items:center; justify-content:center; padding:14px 50px; border-radius:20px; box-shadow:0 3px 6px rgba(0,0,0,0.1); }
.card img{ height:30px; width:30px; }
.card h4{ font-size:25px; }
.card p{ font-size:15px; }
/* --- TABS --- */
.tab-buttons { display:flex; justify-content:center; gap:0; margin:30px 0; border-radius:20px; overflow:hidden; background:#e0e0e0; }
.tab-buttons button { padding:10px 100px; background:transparent; border:none; cursor:pointer; transition:background 0.3s; }
.tab-buttons button:hover { background:#059668c2; color:#fff; }
.tab-buttons button.active { background:#059668; color:#fff; }
.tab-content { display:none; }
.tab-content.active { display:block; }
/* --- CARD ANIMATION --- */
.tab-content .parts > * { opacity:0; transform:translateY(20px); transition:all 0.5s ease; }
.tab-content.active .parts > * { opacity:1; transform:translateY(0); }
.tab-content.active .parts > *:nth-child(1){transition-delay:0.1s;}
.tab-content.active .parts > *:nth-child(2){transition-delay:0.2s;}
.tab-content.active .parts > *:nth-child(3){transition-delay:0.3s;}
.tab-content.active .parts > *:nth-child(4){transition-delay:0.4s;}
.tab-content.active .parts > *:nth-child(5){transition-delay:0.5s;}
/* --- OTHER STYLES --- */
/* --- THEME TOGGLE BUTTON --- */
.theme-toggle {
    cursor: pointer;
    font-size: 20px;
    padding: 6px 10px;
    background: #e0e0e0;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.theme-toggle:hover {
    background: #059668c2;
    color: #fff;
    transform: scale(1.1);
}

/* --- LIGHT THEME --- */
body.light {
    background: #fefefe;
    color: #111;
}

body.light .sidebar {
    background: #06b6d4;
}

body.light .tab-buttons button.active {
    background: #0891b2;
}

body.light .card, 
body.light .profile-header, 
body.light .action-btn {
    background: #fff;
    color: #111;
}

.upper { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.upper h1{font-size:22px;}
.upper button { background:#34d399; color:#064e3b; border:none; border-radius:10px; padding:7px 12px; font-size:13px; cursor:pointer; }
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Student Portal</h2>
        <nav>
            <ul>
                <li><a href="student_dashboard.php">Dashboard</a></li>
                <li><a href="student_profile.php">My Profile</a></li>
                <li><a href="student_mentor.php">My Mentor</a></li>
                <li><a href="student_resources.php">Resources</a></li>
                <li><a href="student_appointments.php">Appointments</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- PROFILE HEADER -->
        <div class="profile-header">
            <?php
            require_once 'components/profile_picture.php';
            displayProfilePicture(
                $_SESSION['user_id'],
                $_SESSION['name'],
                $_SESSION['profile_pic'],
                'medium',
                $_SESSION['role']
            );
            ?>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <p><?php echo ucfirst($_SESSION['role']); ?> Dashboard</p>
                <div class="theme-toggle" id="toggleTheme">☾</div>
                <li><a href="upload_profile_pic.php" style="text-decoration:none;color:#074933ff;">📷 Change Profile Picture</a></li>
                <li><a href="edit_profile.php" style="text-decoration:none;color:#074933ff;">✏️ Edit Profile</a></li>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <div class="action-btn"><h3>Book Session</h3><p>Schedule with mentor</p></div>
            <div class="action-btn"><h3>Resources</h3><p>Access materials</p></div>
            <div class="action-btn"><h3>Emergency</h3><p>24/7 support</p></div>
        </div>

        <!-- STAT CARDS -->
        <div class="card_container">
            <div class="card"><img src="shopping_bag.png"><h4><?php echo $stats['listings']; ?></h4><p>Total Listings</p></div>
            <div class="card"><img src="heart.png"><h4><?php echo $stats['wellness_checks']; ?></h4><p>Wellness Checks</p></div>
            <div class="card"><img src="chat.png"><h4><?php echo $stats['support_sessions']; ?></h4><p>Support Sessions</p></div>
            <div class="card"><img src="progress.png"><h4><?php echo $stats['latest_wellness']; ?></h4><p>Latest Wellness</p></div>
        </div>

        <!-- TABS -->
        <div class="tab-buttons">
            <button onclick="showTab('tab1')" class="<?php echo $active_tab=='tab1'?'active':''; ?>">Listings</button>
            <button onclick="showTab('tab2')" class="<?php echo $active_tab=='tab2'?'active':''; ?>">Wellness</button>
            <button onclick="showTab('tab3')" class="<?php echo $active_tab=='tab3'?'active':''; ?>">Support</button>
        </div>

        <!-- TAB CONTENTS -->
        <div class="tab-content <?php echo $active_tab=='tab1'?'active':''; ?>" id="tab1">
            <div class="upper">
                <h1>My Marketplace Listings</h1>
                <button onclick="window.location.href='marketplace.php?action=add'">+ Add New Listing</button>
            </div>
            <div class="parts">
                <?php if(empty($listings)): ?>
                    <p style="text-align:center; padding:40px;">No listings yet. <a href="marketplace.php?action=add">Add your first item!</a></p>
                <?php else: ?>
                    <?php foreach($listings as $listing): ?>
                    <div class="card">
                        <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                        <p>Price: <?php echo htmlspecialchars($listing['price']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab2'?'active':''; ?>" id="tab2">
            <div class="upper">
                <h1>Wellness Check History</h1>
                <a href="wellness.php"><button>♡ Take New Quiz</button></a>
            </div>
            <div class="parts">
                <?php foreach($wellness_history as $wellness): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($wellness['date']); ?> - <?php echo htmlspecialchars($wellness['score']); ?></h4>
                    <p>Status: <?php echo htmlspecialchars($wellness['status']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab3'?'active':''; ?>" id="tab3">
            <div class="upper">
                <h1>Peer Support Sessions</h1>
                <a href="peer_support.php"><button>Start New Session</button></a>
            </div>
            <div class="parts">
                <?php foreach($support_sessions as $session): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($session['mentor_name']); ?></h4>
                    <p>Last message: <?php echo htmlspecialchars($session['last_message']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
     document.addEventListener("DOMContentLoaded", () => {
// TAB FUNCTION
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

    // Animate cards
    const cards = tab.querySelectorAll('.parts > *');
    cards.forEach(card => {
        card.style.opacity = 0;
        card.style.transform = 'translateY(20px)';
        void card.offsetWidth;
        card.style.opacity = 1;
        card.style.transform = 'translateY(0)';
    });
}});
</script>
</body>
</html>