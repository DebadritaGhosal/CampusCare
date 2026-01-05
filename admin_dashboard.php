<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get admin details
$admin = [];
try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching admin details: " . $e->getMessage();
}

// Fetch stats
$stats = [
    'total_students' => 0,
    'total_mentors' => 0,
    'total_listings' => 0,
    'active_support_sessions' => 0
];

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'");
$stats['total_students'] = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role='mentor'");
$stats['total_mentors'] = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM marketplace");
$stats['total_listings'] = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM support_sessions WHERE status='active'");
$stats['active_support_sessions'] = (int)$stmt->fetchColumn();

// Fetch recent activity
$recent_users = $pdo->query("SELECT id, name, email, role FROM users ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
$recent_listings = $pdo->query("SELECT id, title, price, user_id FROM marketplace ORDER BY posted_date DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

$active_tab = isset($_GET['tab']) && in_array($_GET['tab'], ['tab1','tab2']) ? $_GET['tab'] : 'tab1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard | CampusCare</title>
<link rel="stylesheet" href="dashboard.css">
<style>
/* You can copy all student dashboard CSS here, no changes needed */
</style>
</head>
<body>
<div class="dashboard-container">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>Admin Portal</h2>
        <nav>
            <ul>
                <li><a href="admin_dashboard.php">Dashboard</a></li>
                <li><a href="admin_users.php">Manage Users</a></li>
                <li><a href="admin_marketplace.php">Marketplace</a></li>
                <li><a href="admin_support.php">Support Requests</a></li>
                <li><a href="logout.php">Logout</a></li>
                <li><a href="delete_account.php" style="color: #ff6b6b;">Delete Account</a></li>
            </ul>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- PROFILE HEADER -->
        <div class="profile-header">
            <?php
            require_once 'components/profile_picture.php';
            displayProfilePicture($_SESSION['user_id'], $_SESSION['name'], $_SESSION['profile_pic'] ?? '', 'medium', $_SESSION['role']);
            ?>
            <div>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                <p>Admin Dashboard</p>
                <div class="theme-toggle" id="toggleTheme">☾</div>
                <li>✏️<a href="edit_profile.php">Edit Profile</a></li>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <div class="action-btn"><h3>Manage Users</h3><p>Add / Edit / Remove Users</p></div>
            <div class="action-btn"><h3>Marketplace</h3><p>Monitor Listings</p></div>
            <div class="action-btn"><h3>Support</h3><p>Active Requests</p></div>
        </div>

        <!-- STAT CARDS -->
        <div class="card_container">
            <div class="card"><img src="users.png"><h4><?php echo $stats['total_students']; ?></h4><p>Total Students</p></div>
            <div class="card"><img src="mentor.png"><h4><?php echo $stats['total_mentors']; ?></h4><p>Total Mentors</p></div>
            <div class="card"><img src="shopping_bag.png"><h4><?php echo $stats['total_listings']; ?></h4><p>Total Listings</p></div>
            <div class="card"><img src="chat.png"><h4><?php echo $stats['active_support_sessions']; ?></h4><p>Active Support</p></div>
        </div>

        <!-- TABS -->
        <div class="tab-buttons">
            <button onclick="showTab('tab1')" class="<?php echo $active_tab=='tab1'?'active':''; ?>">Recent Users</button>
            <button onclick="showTab('tab2')" class="<?php echo $active_tab=='tab2'?'active':''; ?>">Recent Listings</button>
        </div>

        <!-- TAB CONTENT -->
        <div class="tab-content <?php echo $active_tab=='tab1'?'active':''; ?>" id="tab1">
            <div class="parts">
                <?php foreach($recent_users as $user): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($user['name']); ?> (<?php echo ucfirst($user['role']); ?>)</h4>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-content <?php echo $active_tab=='tab2'?'active':''; ?>" id="tab2">
            <div class="parts">
                <?php foreach($recent_listings as $listing): ?>
                <div class="card">
                    <h4><?php echo htmlspecialchars($listing['title']); ?></h4>
                    <p>Price: Rs.<?php echo number_format((float)$listing['price'],2); ?>/-</p>
                    <p>Seller ID: <?php echo htmlspecialchars($listing['user_id']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Copy student dashboard JS
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
