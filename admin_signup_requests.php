<?php
session_start();
require_once 'config/database.php';

// Only admin can access
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Get all signup requests
$stmt = $pdo->query(
    "SELECT u.*, 
     (SELECT COUNT(*) FROM mental_wellness_messages WHERE user_id = u.id) as wellness_messages,
     (SELECT MAX(severity_score) FROM mental_wellness_messages WHERE user_id = u.id) as max_score,
     (SELECT COUNT(*) FROM quiz_results WHERE user_id = u.id) as quiz_count
     FROM users u 
     WHERE u.role IN ('student', 'mentor', 'teacher')
     ORDER BY u.created_at DESC"
);
$users = $stmt->fetchAll();
?>

<div class="card">
    <h2 class="card-title">👥 Student & Faculty Database</h2>
    
    <div style="margin-bottom: 20px;">
        <input type="text" 
               id="userSearch" 
               placeholder="Search users..." 
               style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"
               onkeyup="searchUsers()">
    </div>
    
    <table style="width: 100%; border-collapse: collapse;" id="usersTable">
        <thead>
            <tr style="background: #f8f9fa;">
                <th style="padding: 10px; text-align: left;">Name</th>
                <th style="padding: 10px; text-align: left;">Role</th>
                <th style="padding: 10px; text-align: left;">Wellness Score</th>
                <th style="padding: 10px; text-align: left;">Messages</th>
                <th style="padding: 10px; text-align: left;">Joined</th>
                <th style="padding: 10px; text-align: left;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): 
                $risk_color = $user['max_score'] >= 75 ? '#dc3545' : 
                             ($user['max_score'] >= 50 ? '#fd7e14' : '#28a745');
            ?>
            <tr style="border-bottom: 1px solid #dee2e6;">
                <td style="padding: 10px;">
                    <div style="display: flex; align-items: center;">
                        <div style="width: 30px; height: 30px; border-radius: 50%; background: #2E8B57; color: white; 
                                   display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold;">
                            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($user['name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($user['email']); ?></small>
                        </div>
                    </div>
                </td>
                <td style="padding: 10px;">
                    <span style="padding: 3px 8px; border-radius: 3px; background: #6c757d; color: white;">
                        <?php echo ucfirst($user['role']); ?>
                    </span>
                    <?php if ($user['is_anti_ragging']): ?>
                    <br><small style="color: #dc3545;">🛡️ Anti-Ragging</small>
                    <?php endif; ?>
                </td>
                <td style="padding: 10px;">
                    <div style="color: <?php echo $risk_color; ?>; font-weight: bold;">
                        <?php echo $user['max_score'] ?: 0; ?>/100
                    </div>
                    <?php if ($user['max_score'] >= 75): ?>
                    <small style="color: #dc3545;">⚠️ Critical</small>
                    <?php endif; ?>
                </td>
                <td style="padding: 10px;">
                    <?php echo $user['wellness_messages']; ?> messages<br>
                    <?php echo $user['quiz_count']; ?> quizzes
                </td>
                <td style="padding: 10px;">
                    <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                </td>
                <td style="padding: 10px;">
                    <button class="btn" onclick="viewUserDetails(<?php echo $user['id']; ?>)">
                        View Details
                    </button>
                    <?php if ($user['max_score'] >= 75): ?>
                    <button class="btn btn-danger" onclick="flagUser(<?php echo $user['id']; ?>)">
                        🚨 Flag
                    </button>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function searchUsers() {
    const input = document.getElementById('userSearch');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const text = row.textContent.toLowerCase();
        row.style.display = text.indexOf(filter) > -1 ? '' : 'none';
    }
}

function viewUserDetails(userId) {
    fetch(`get_user_details.php?id=${userId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('proposalContent').innerHTML = data;
            document.getElementById('proposalModal').style.display = 'block';
        });
}

function flagUser(userId) {
    if (confirm('Flag this user for follow-up?')) {
        fetch('flag_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ user_id: userId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User flagged for follow-up');
            }
        });
    }
}
</script>