<?php
require_once 'includes/auth_check.php';
require_once 'includes/db_connect.php';

// Only students allowed
if ($_SESSION['role'] !== 'student') {
    header('Location: login.php');
    exit();
}

// Fetch student data
$stmt = $pdo->prepare("SELECT * FROM signup_details WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    die("Student profile not found.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile | CampusCare</title>

<style>
body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f6f9;
}

.profile-container {
    max-width: 900px;
    margin: 40px auto;
    background: #fff;
    padding: 30px;
    border-radius: 14px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 25px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 20px;
}

.profile-header img {
    width: 130px;
    height: 130px;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid #0d4623ff;
}

.profile-header h1 {
    margin: 0;
}

.profile-header p {
    color: #666;
    margin-top: 5px;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
    margin-top: 30px;
}

.profile-item {
    background: #f9fafc;
    padding: 14px;
    border-radius: 10px;
    font-size: 15px;
}

.profile-item strong {
    color: #333;
}

.profile-actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
}

.profile-actions a {
    padding: 12px 18px;
    text-decoration: none;
    background: #24874dff;
    color: white;
    border-radius: 8px;
    font-weight: bold;
}

.profile-actions a.secondary {
    background: #444;
}

.profile-actions a:hover {
    opacity: 0.9;
}

@media (max-width: 768px) {
    .profile-header {
        flex-direction: column;
        text-align: center;
    }
    .profile-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>

<div class="profile-container">

    <!-- Profile Header -->
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
            <h1><?php echo htmlspecialchars($student['name']); ?></h1>
            <p><?php echo htmlspecialchars($student['email']); ?></p>
            <p>Role: Student</p>
        </div>
    </div>

    <!-- Profile Details -->
    <div class="profile-grid">
        <div class="profile-item"><strong>College:</strong> <?php echo $student['college'] ?? '—'; ?></div>
        <div class="profile-item"><strong>Year:</strong> <?php echo $student['year'] ?? '—'; ?></div>
        <div class="profile-item"><strong>Course:</strong> <?php echo $student['course'] ?? '—'; ?></div>
        <div class="profile-item"><strong>Phone:</strong> <?php echo $student['phone'] ?? '—'; ?></div>
        <div class="profile-item"><strong>Date of Birth:</strong> <?php echo $student['dob'] ?? '—'; ?></div>
        <div class="profile-item"><strong>Gender:</strong> <?php echo $student['gender'] ?? '—'; ?></div>
    </div>

    <!-- Actions -->
    <div class="profile-actions">
        <a href="edit_profile.php">✏️ Edit Profile</a>
        <a href="upload_profile_pic.php" class="secondary">📷 Change Picture</a>
        <a href="student_dashboard.php" class="secondary">⬅ Back to Dashboard</a>
    </div>

</div>

</body>
</html>