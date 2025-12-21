<?php
session_start();
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
    $stmt = $pdo->prepare('SELECT s.* FROM students s 
                          JOIN users u ON s.user_id = u.id 
                          WHERE u.id = ?');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | CampusCare</title>
    <style>
        .sidebar {
            background: #7209b7; /* Different color for student */
        }
        
        .mentor-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .quick-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .action-btn {
            flex: 1;
            padding: 15px;
            background: white;
            border: none;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
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
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-btn">
                    <h3>Book Session</h3>
                    <p>Schedule with mentor</p>
                </div>
                <div class="action-btn">
                    <h3>Resources</h3>
                    <p>Access materials</p>
                </div>
                <div class="action-btn">
                    <h3>Emergency</h3>
                    <p>24/7 support</p>
                </div>
            </div>
            
            <!-- Mentor Information -->
            <div class="mentor-card">
                <h2>My Mentor</h2>
                <?php if ($mentor): ?>
                    <div style="display: flex; align-items: center; margin-top: 15px;">
                        <div class="student-avatar" style="background: #4361ee;">
                            <?php echo strtoupper(substr($mentor['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <h3><?php echo htmlspecialchars($mentor['name']); ?></h3>
                            <p><?php echo htmlspecialchars($mentor['specialization'] ?? 'Mental Health Mentor'); ?></p>
                            <p><?php echo htmlspecialchars($mentor['qualification'] ?? ''); ?></p>
                            <p>Email: <?php echo htmlspecialchars($mentor['email']); ?></p>
                        </div>
                    </div>
                    <div style="margin-top: 20px;">
                        <button class="btn" onclick="bookSession()">Book Session</button>
                        <button class="btn" onclick="messageMentor()">Send Message</button>
                    </div>
                <?php else: ?>
                    <p>No mentor assigned yet. Please contact administration.</p>
                    <button class="btn" onclick="requestMentor()">Request Mentor</button>
                <?php endif; ?>
            </div>
            
            <!-- Upcoming Sessions -->
            <div class="table-container">
                <h2>Upcoming Sessions</h2>
                <p>No upcoming sessions scheduled.</p>
            </div>
        </div>
    </div>
    
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
        function bookSession() {
            window.location.href = 'book_session.php';
        }
        
        function messageMentor() {
            window.location.href = 'messages.php';
        }
        
        function requestMentor() {
            if (confirm('Request a mentor assignment?')) {
                fetch('request_mentor.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Mentor request submitted successfully');
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }
    </script>
</body>
</html>