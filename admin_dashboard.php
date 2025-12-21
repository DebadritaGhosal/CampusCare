<?php
session_start();
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Only admins can access this page
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Fetch pending signup requests
$signupRequests = [];
try {
    $stmt = $pdo->prepare('SELECT sr.*, u.name as reviewer_name 
                          FROM signup_requests sr 
                          LEFT JOIN users u ON sr.reviewed_by = u.id 
                          WHERE sr.status = "pending" 
                          ORDER BY sr.created_at DESC');
    $stmt->execute();
    $signupRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching requests: " . $e->getMessage();
}

// Fetch all users (for admin view)
$users = [];
try {
    $stmt = $pdo->prepare('SELECT u.*, 
                          (SELECT COUNT(*) FROM students s WHERE s.user_id = u.id) as is_student,
                          (SELECT COUNT(*) FROM mentors m WHERE m.user_id = u.id) as is_mentor,
                          (SELECT COUNT(*) FROM admins a WHERE a.user_id = u.id) as is_admin
                          FROM users u 
                          ORDER BY u.created_at DESC');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching users: " . $e->getMessage();
}

// Fetch critical mental health reports for anti-ragging
$critical_reports = [];
try {
    $stmt = $pdo->prepare(
        "SELECT mwm.*, u.name as student_name, u.email, u.phone, aar.risk_level, aar.suggested_actions
         FROM mental_wellness_messages mwm
         JOIN users u ON mwm.user_id = u.id
         JOIN ai_analysis_reports aar ON mwm.id = aar.message_id
         WHERE mwm.severity_score >= 75
         ORDER BY mwm.created_at DESC"
    );
    $stmt->execute();
    $critical_reports = $stmt->fetchAll();
} catch (PDOException $e) {
    $critical_error = "Error fetching critical reports: " . $e->getMessage();
}

// Get AI analysis statistics
$stats = [];
try {
    $stmt = $pdo->query(
        "SELECT 
            COUNT(*) as total_messages,
            AVG(severity_score) as avg_score,
            SUM(CASE WHEN severity_score >= 75 THEN 1 ELSE 0 END) as critical_count,
            SUM(CASE WHEN severity_score BETWEEN 50 AND 74 THEN 1 ELSE 0 END) as high_count,
            SUM(CASE WHEN severity_score BETWEEN 25 AND 49 THEN 1 ELSE 0 END) as medium_count,
            SUM(CASE WHEN severity_score < 25 THEN 1 ELSE 0 END) as low_count
         FROM mental_wellness_messages
         WHERE DATE(created_at) = CURDATE()"
    );
    $stats = $stmt->fetch();
} catch (PDOException $e) {
    $stats_error = "Error fetching AI statistics: " . $e->getMessage();
}

// Get department referrals
$departments = [];
try {
    $stmt = $pdo->query(
        "SELECT department_referred, COUNT(*) as count 
         FROM mental_wellness_messages 
         WHERE DATE(created_at) = CURDATE()
         GROUP BY department_referred 
         ORDER BY count DESC"
    );
    $stmt->execute();
    $departments = $stmt->fetchAll();
} catch (PDOException $e) {
    $dept_error = "Error fetching department referrals: " . $e->getMessage();
}

// Get anti-ragging committee members
$committee = [];
try {
    $stmt = $pdo->prepare(
        "SELECT u.id, u.name, u.email, u.phone, arc.designation, arc.contact_number, arc.is_active
         FROM users u
         JOIN anti_ragging_committee arc ON u.id = arc.user_id
         ORDER BY arc.is_active DESC"
    );
    $stmt->execute();
    $committee = $stmt->fetchAll();
} catch (PDOException $e) {
    $committee_error = "Error fetching committee: " . $e->getMessage();
}

// Calculate additional stats
$studentCount = array_reduce($users, function($carry, $user) {
    return $carry + ($user['is_student'] > 0 ? 1 : 0);
}, 0);

$mentorCount = array_reduce($users, function($carry, $user) {
    return $carry + ($user['is_mentor'] > 0 ? 1 : 0);
}, 0);

$adminCount = array_reduce($users, function($carry, $user) {
    return $carry + ($user['is_admin'] > 0 ? 1 : 0);
}, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | CampusCare</title>
    <style>
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 250px;
            background: #2E8B57;
            color: white;
            padding: 20px;
        }
        
        .main-content {
            flex: 1;
            padding: 30px;
            background: #f5f5f5;
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .profile-pic {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #4CAF50;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-right: 15px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: bold;
            color: #2E8B57;
            margin: 10px 0;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-approve {
            background: #28a745;
            color: white;
        }
        
        .btn-reject {
            background: #dc3545;
            color: white;
        }
        
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .tab-container {
            margin-bottom: 20px;
        }
        
        .tab {
            display: inline-block;
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
        }
        
        .tab.active {
            border-bottom-color: #2E8B57;
            color: #2E8B57;
            font-weight: bold;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }

        /* Profile picture styles */
        .profile-picture {
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid;
            transition: transform 0.3s ease;
        }

        .profile-picture:hover {
            transform: scale(1.05);
        }

        .profile-picture-small {
            width: 40px;
            height: 40px;
            border-width: 2px;
        }

        .profile-picture-medium {
            width: 60px;
            height: 60px;
            border-width: 3px;
        }

        .profile-picture-large {
            width: 100px;
            height: 100px;
            border-width: 4px;
        }

        /* Role-based border colors */
        .profile-picture.student {
            border-color: #7209b7;
        }

        .profile-picture.mentor {
            border-color: #4361ee;
        }

        .profile-picture.admin {
            border-color: #2E8B57;
        }

        /* Profile picture upload widget */
        .upload-widget {
            position: relative;
            display: inline-block;
        }

        .upload-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
            cursor: pointer;
        }

        .upload-widget:hover .upload-overlay {
            opacity: 1;
        }

        .upload-icon {
            color: white;
            font-size: 24px;
        }
        
        /* Additional styles for new sections */
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .card-title {
            color: #2E8B57;
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #2E8B57;
        }
        
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            font-weight: bold;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #212529;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .proposal-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 12px;
            padding: 25px;
            width: 90%;
            max-width: 600px;
            z-index: 1001;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 1.5em;
            cursor: pointer;
            color: #718096;
            background: none;
            border: none;
            padding: 0;
        }
        
        .action-links {
            margin-top: 10px;
        }
        
        .action-links a {
            color: #2E8B57;
            text-decoration: none;
            font-size: 14px;
            margin-right: 15px;
        }
        
        .action-links a:hover {
            text-decoration: underline;
        }
        
        .ai-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .ai-stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .ai-stat-card .number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .ai-stat-card small {
            opacity: 0.9;
        }
        
        .department-bar {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        
        .department-name {
            width: 150px;
            font-weight: 500;
        }
        
        .department-count {
            width: 50px;
            text-align: right;
            margin-right: 10px;
        }
        
        .department-progress {
            flex: 1;
            height: 20px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .department-fill {
            height: 100%;
            background: #2E8B57;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <h2>CampusCare Admin</h2>
            <nav>
                <ul style="list-style: none; padding: 0;">
                    <li><a href="admin_dashboard.php" style="color: white; text-decoration: none;">Dashboard</a></li>
                    <li><a href="admin_users.php" style="color: white; text-decoration: none;">User Management</a></li>
                    <li><a href="admin_reports.php" style="color: white; text-decoration: none;">Reports</a></li>
                    <li><a href="admin_settings.php" style="color: white; text-decoration: none;">Settings</a></li>
                    <li><a href="logout.php" style="color: white; text-decoration: none;">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="profile-pic">
                    <?php 
                    $firstLetter = strtoupper(substr($_SESSION['name'], 0, 1));
                    echo $firstLetter;
                    ?>
                </div>
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?></h1>
                    <p><?php echo ucfirst($_SESSION['role']); ?> Dashboard</p>
                    <div class="action-links">
                        <a href="upload_profile_pic.php">📷 Change Profile Picture</a>
                        <a href="edit_profile.php">✏️ Edit Profile</a>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Total Users</h3>
                    <div class="number"><?php echo count($users); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Requests</h3>
                    <div class="number"><?php echo count($signupRequests); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Students</h3>
                    <div class="number"><?php echo $studentCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Mentors</h3>
                    <div class="number"><?php echo $mentorCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Admins</h3>
                    <div class="number"><?php echo $adminCount; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Critical Cases Today</h3>
                    <div class="number" style="color: #dc3545;"><?php echo $stats['critical_count'] ?? 0; ?></div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="tab-container">
                <div class="tab active" onclick="switchTab('pending')">Pending Signups</div>
                <div class="tab" onclick="switchTab('users')">All Users</div>
                <div class="tab" onclick="switchTab('antiRagging')">Anti-Ragging Reports</div>
                <div class="tab" onclick="switchTab('aiAnalysis')">AI Analysis</div>
                <div class="tab" onclick="switchTab('committee')">Committee Management</div>
            </div>
            
            <!-- Pending Signups Tab -->
            <div id="pendingTab" class="tab-content active">
                <div class="table-container">
                    <h2>Pending Signup Requests</h2>
                    <?php if (empty($signupRequests)): ?>
                        <p>No pending requests.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Requested At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($signupRequests as $request): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($request['name']); ?></td>
                                    <td><?php echo htmlspecialchars($request['email']); ?></td>
                                    <td><span class="badge badge-info"><?php echo ucfirst($request['role']); ?></span></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($request['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-approve" onclick="approveRequest(<?php echo $request['id']; ?>)">Approve</button>
                                        <button class="btn btn-reject" onclick="rejectRequest(<?php echo $request['id']; ?>)">Reject</button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- All Users Tab -->
            <div id="usersTab" class="tab-content">
                <div class="table-container">
                    <h2>All Users</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>College</th>
                                <th>Joined</th>
                                <th>Last Login</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div style="width: 30px; height: 30px; border-radius: 50%; background: #4CAF50; color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                        </div>
                                        <?php echo htmlspecialchars($user['name']); ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <?php 
                                    $roles = [];
                                    if ($user['is_student'] > 0) $roles[] = '<span class="badge badge-info">Student</span>';
                                    if ($user['is_mentor'] > 0) $roles[] = '<span class="badge badge-warning">Mentor</span>';
                                    if ($user['is_admin'] > 0) $roles[] = '<span class="badge badge-success">Admin</span>';
                                    echo implode(' ', $roles);
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($user['college'] ?? 'N/A'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php 
                                    echo $user['last_login'] 
                                        ? date('M d, Y H:i', strtotime($user['last_login'])) 
                                        : 'Never';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Anti-Ragging Reports Tab -->
            <div id="antiRaggingTab" class="tab-content">
                <div class="card">
                    <h2 class="card-title">🛡️ Anti-Ragging Committee Reports</h2>
                    
                    <?php if (empty($critical_reports)): ?>
                        <p>No critical reports requiring anti-ragging committee attention.</p>
                    <?php else: ?>
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                                        <th style="padding: 10px; text-align: left;">Student</th>
                                        <th style="padding: 10px; text-align: left;">Score</th>
                                        <th style="padding: 10px; text-align: left;">Keywords</th>
                                        <th style="padding: 10px; text-align: left;">Department</th>
                                        <th style="padding: 10px; text-align: left;">Time</th>
                                        <th style="padding: 10px; text-align: left;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($critical_reports as $report): 
                                        $keywords = json_decode($report['keywords'], true);
                                        $keyword_list = '';
                                        if ($keywords) {
                                            foreach ($keywords as $cat => $words) {
                                                $keyword_list .= implode(', ', $words) . ', ';
                                            }
                                            $keyword_list = rtrim($keyword_list, ', ');
                                        }
                                    ?>
                                    <tr style="border-bottom: 1px solid #dee2e6;">
                                        <td style="padding: 10px;">
                                            <strong><?php echo htmlspecialchars($report['student_name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($report['email']); ?></small><br>
                                            <small><?php echo htmlspecialchars($report['phone']); ?></small>
                                        </td>
                                        <td style="padding: 10px;">
                                            <span style="color: #dc3545; font-weight: bold;">
                                                <?php echo $report['severity_score']; ?>/100
                                            </span><br>
                                            <small style="color: #dc3545;">CRITICAL</small>
                                        </td>
                                        <td style="padding: 10px;">
                                            <small><?php echo htmlspecialchars(substr($keyword_list, 0, 50)); ?>...</small>
                                        </td>
                                        <td style="padding: 10px;">
                                            <span style="background: #dc3545; color: white; padding: 3px 8px; border-radius: 3px;">
                                                <?php echo htmlspecialchars($report['department_referred']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 10px;">
                                            <?php echo date('H:i', strtotime($report['created_at'])); ?><br>
                                            <small><?php echo date('M d', strtotime($report['created_at'])); ?></small>
                                        </td>
                                        <td style="padding: 10px;">
                                            <button class="btn" onclick="viewReportDetails(<?php echo $report['id']; ?>)">
                                                View Details
                                            </button>
                                            <button class="btn btn-warning" onclick="escalateToAntiRagging(<?php echo $report['id']; ?>)">
                                                🛡️ Escalate
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- AI Analysis Dashboard Tab -->
            <div id="aiAnalysisTab" class="tab-content">
                <div class="card">
                    <h2 class="card-title">🤖 AI Analysis Dashboard</h2>
                    
                    <div class="ai-stats-grid">
                        <div class="ai-stat-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h3>Total Messages</h3>
                            <div class="number"><?php echo $stats['total_messages'] ?? 0; ?></div>
                            <small>Today</small>
                        </div>
                        
                        <div class="ai-stat-card" style="background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);">
                            <h3>Average Score</h3>
                            <div class="number"><?php echo round($stats['avg_score'] ?? 0, 1); ?>/100</div>
                            <small>Risk Level</small>
                        </div>
                        
                        <div class="ai-stat-card" style="background: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);">
                            <h3>Critical Cases</h3>
                            <div class="number"><?php echo $stats['critical_count'] ?? 0; ?></div>
                            <small>Score ≥ 75</small>
                        </div>
                        
                        <div class="ai-stat-card" style="background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);">
                            <h3>High Risk</h3>
                            <div class="number"><?php echo $stats['high_count'] ?? 0; ?></div>
                            <small>Score 50-74</small>
                        </div>
                    </div>
                    
                    <div style="margin-top: 30px;">
                        <h4>Department Referrals (Today)</h4>
                        <?php if (empty($departments)): ?>
                            <p>No department referrals today.</p>
                        <?php else: ?>
                            <?php foreach ($departments as $dept): 
                                $percentage = $stats['total_messages'] > 0 ? ($dept['count'] / $stats['total_messages']) * 100 : 0;
                            ?>
                            <div class="department-bar">
                                <div class="department-name"><?php echo htmlspecialchars($dept['department_referred']); ?></div>
                                <div class="department-count"><?php echo $dept['count']; ?></div>
                                <div class="department-progress">
                                    <div class="department-fill" style="width: <?php echo $percentage; ?>%;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Committee Management Tab -->
            <div id="committeeTab" class="tab-content">
                <div class="card">
                    <h2 class="card-title">🛡️ Anti-Ragging Committee Management</h2>
                    
                    <button class="btn btn-success" onclick="openAddCommitteeMember()" style="margin-bottom: 20px;">
                        + Add Committee Member
                    </button>
                    
                    <?php if (empty($committee)): ?>
                        <p>No anti-ragging committee members added yet.</p>
                    <?php else: ?>
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 10px; text-align: left;">Name</th>
                                    <th style="padding: 10px; text-align: left;">Designation</th>
                                    <th style="padding: 10px; text-align: left;">Contact</th>
                                    <th style="padding: 10px; text-align: left;">Status</th>
                                    <th style="padding: 10px; text-align: left;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($committee as $member): ?>
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 10px;">
                                        <strong><?php echo htmlspecialchars($member['name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($member['email']); ?></small>
                                    </td>
                                    <td style="padding: 10px;"><?php echo htmlspecialchars($member['designation']); ?></td>
                                    <td style="padding: 10px;"><?php echo htmlspecialchars($member['contact_number']); ?></td>
                                    <td style="padding: 10px;">
                                        <span style="padding: 3px 8px; border-radius: 3px; background: <?php echo $member['is_active'] ? '#28a745' : '#6c757d'; ?>; color: white;">
                                            <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 10px;">
                                        <button class="btn" onclick="toggleCommitteeStatus(<?php echo $member['id']; ?>, <?php echo $member['is_active'] ? 0 : 1; ?>)">
                                            <?php echo $member['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for adding committee member -->
    <div class="modal-overlay" id="addCommitteeModal" style="display: none;">
        <div class="proposal-modal">
            <button class="close-modal" onclick="closeCommitteeModal()">&times;</button>
            <h3>Add Anti-Ragging Committee Member</h3>
            <form id="committeeForm" onsubmit="addCommitteeMember(event)">
                <div style="margin-bottom: 15px;">
                    <label>Faculty Name</label>
                    <input type="text" id="facultyName" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Faculty Email</label>
                    <input type="email" id="facultyEmail" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Designation</label>
                    <input type="text" id="facultyDesignation" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label>Contact Number</label>
                    <input type="tel" id="facultyContact" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                </div>
                <button type="submit" class="btn btn-success">Add Member</button>
            </form>
        </div>
    </div>
    
    <!-- Proposal Modal (for report details) -->
    <div class="modal-overlay" id="proposalModal" onclick="closeModal(event)">
        <div class="proposal-modal" onclick="event.stopPropagation()">
            <button class="close-modal" onclick="closeModal(event)">&times;</button>
            <div id="proposalContent">
                <!-- Dynamic content for report details -->
            </div>
        </div>
    </div>
    
    <script>
        // Tab switching function
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
        
        // Request approval functions
        function approveRequest(requestId) {
            if (confirm('Approve this signup request?')) {
                fetch('admin_approve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Request approved successfully');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function rejectRequest(requestId) {
            const reason = prompt('Please enter reason for rejection:');
            if (reason !== null) {
                fetch('admin_approve.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        request_id: requestId,
                        action: 'reject',
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Request rejected');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        // Committee management functions
        function openAddCommitteeMember() {
            document.getElementById('addCommitteeModal').style.display = 'block';
        }
        
        function closeCommitteeModal() {
            document.getElementById('addCommitteeModal').style.display = 'none';
        }
        
        function addCommitteeMember(e) {
            e.preventDefault();
            
            const data = {
                name: document.getElementById('facultyName').value,
                email: document.getElementById('facultyEmail').value,
                designation: document.getElementById('facultyDesignation').value,
                contact: document.getElementById('facultyContact').value
            };
            
            fetch('add_committee_member.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Committee member added successfully');
                    closeCommitteeModal();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
        
        function toggleCommitteeStatus(memberId, newStatus) {
            fetch('toggle_committee_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    member_id: memberId,
                    status: newStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
        
        function escalateToAntiRagging(reportId) {
            if (confirm('Escalate this case to the Anti-Ragging Committee?')) {
                fetch('escalate_report.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ report_id: reportId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Case escalated to Anti-Ragging Committee');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
        
        function viewReportDetails(reportId) {
            fetch(`view_report_details.php?id=${reportId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('proposalContent').innerHTML = data;
                    document.getElementById('proposalModal').style.display = 'block';
                });
        }
        
        function closeModal(event) {
            if(event) event.stopPropagation();
            document.getElementById('proposalModal').style.display = 'none';
        }
        
        // Profile picture preview function
        function previewProfilePicture(input, previewId, initialsId) {
            const preview = document.getElementById(previewId);
            const initials = document.getElementById(initialsId);
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    if (initials) initials.style.display = 'none';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
                if (initials) initials.style.display = 'flex';
            }
        }
        
        // Close modals on ESC key
        document.addEventListener('keydown', function(e) {
            if(e.key === 'Escape') {
                closeModal();
                closeCommitteeModal();
            }
        });
    </script>
</body>
</html>