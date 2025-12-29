<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';
session_start();
var_dump($_SESSION['role']);

// Only mentors can access this page
if ($_SESSION['role'] !== 'mentor') {
    header('Location: login.php');
    exit();
}

// Get mentor details
$mentor = [];
try {
    $stmt = $pdo->prepare('SELECT m.* FROM mentors m 
                          JOIN users u ON m.user_id = u.id 
                          WHERE u.id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $mentor = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching mentor details: " . $e->getMessage();
}

// Get assigned students
$assignedStudents = [];
try {
    $stmt = $pdo->prepare('SELECT s.*, u.name, u.email, u.phone 
                          FROM mentorship_assignments ma
                          JOIN students s ON ma.student_id = s.id
                          JOIN users u ON s.user_id = u.id
                          WHERE ma.mentor_id = ? AND ma.status = "active"
                          ORDER BY ma.start_date DESC');
    $stmt->execute([$mentor['id']]);
    $assignedStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching students: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Dashboard | CampusCare</title>
    <style>
        /* Similar styles as admin dashboard with mentor theme */
        .sidebar {
            background: #4361ee; /* Different color for mentor */
        }
        
        .session-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="sidebar">
            <h2>Mentor Portal</h2>
            <nav>
                <ul>
                    <li><a href="mentor_dashboard.php">Dashboard</a></li>
                    <li><a href="mentor_students.php">My Students</a></li>
                    <li><a href="mentor_sessions.php">Sessions</a></li>
                    <li><a href="mentor_resources.php">Resources</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
        
        <div class="main-content">
            <!-- Add this to the dashboard header section -->
<div class="profile-header">
    <?php
    // Include the profile picture component
    require_once 'components/profile_picture.php';
    
    // Display the profile picture
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
        <li>
    <a href="upload_profile_pic.php">
        <span>📷</span> Change Profile Picture
    </a>
</li>
<li>
    <a href="edit_profile.php">
        <span>✏️</span> Edit Profile
    </a>
</li>
    </div>
</div>
            
            <!-- Quick Stats -->
            <div class="stats-cards">
                <div class="stat-card">
                    <h3>Assigned Students</h3>
                    <div class="number"><?php echo count($assignedStudents); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Upcoming Sessions</h3>
                    <div class="number">0</div>
                </div>
                <div class="stat-card">
                    <h3>Total Sessions</h3>
                    <div class="number">0</div>
                </div>
            </div>
            
            <!-- Assigned Students -->
            <div class="table-container">
                <h2>Your Students</h2>
                <?php if (empty($assignedStudents)): ?>
                    <p>No students assigned yet.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Roll Number</th>
                                <th>Department</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignedStudents as $student): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="student-avatar">
                                            <?php echo strtoupper(substr($student['name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong><br>
                                            <small><?php echo htmlspecialchars($student['email']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['department'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                                <td><span class="badge">Active</span></td>
                                <td>
                                    <button class="btn">Schedule Session</button>
                                    <button class="btn">View Notes</button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
<script>
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

// Use with file input:
// <input type="file" onchange="previewProfilePicture(this, 'previewId', 'initialsId')">
</script>
</html>