<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$name = $email = $password = $role = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    
    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/@rcciit\.org\.in$/i', $email)) {
        $error = "Please sign up using your college email ending with @rcciit.org.in.";
    } else {
        // For admin registration, restrict to faculty
        if ($role === 'admin') {
            // Check if faculty email (you might have a faculty email list)
            $facultyDomains = ['faculty.rcciit.org.in', 'rcciit.org.in']; // Add actual faculty domains
            $emailDomain = strtolower(substr(strrchr($email, "@"), 1));
            
            if (!in_array($emailDomain, $facultyDomains)) {
                $error = "Admin registration is restricted to faculty members only.";
            } else {
                // Create signup request for admin approval
                try {
                    $pdo = new PDO('mysql:host=127.0.0.1;dbname=campuscare;charset=utf8mb4', 'root', '');
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->prepare('INSERT INTO signup_requests (name, email, role, request_data) VALUES (?, ?, ?, ?)');
                    $stmt->execute([
                        htmlspecialchars($name),
                        $email,
                        $role,
                        json_encode(['timestamp' => date('Y-m-d H:i:s')])
                    ]);
                    
                    $_SESSION['signup_message'] = "Your admin registration request has been submitted for approval.";
                    header('Location: login.php');
                    exit();
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            }
        } else {
            // For student/mentor, proceed normally
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $_SESSION['temp_user'] = [
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'email' => $email,
                'password' => $hashed_password,
                'role' => $role
            ];
            
            header('Location: personal_details.php');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>CampusCare | Sign Up</title>
    <style>
        .role-options {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .role-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option:hover {
            border-color: #2E8B57;
        }
        
        .role-option.selected {
            border-color: #2E8B57;
            background-color: #f0fff4;
        }
        
        .role-description {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
        .admin-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="signup-box">
            <h1>Sign Up</h1>
            <p>Create your CampusCare account</p>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required value="<?php echo htmlspecialchars($name); ?>">
                
                <label for="email">College Email</label>
                <input type="email" id="email" name="email" placeholder="you@rcciit.org.in" required value="<?php echo htmlspecialchars($email); ?>">
                <small>Must end with @rcciit.org.in</small>
                
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
                
                <label>Select Your Role</label>
                <div class="role-options">
                    <div class="role-option" data-role="student">
                        <strong>Student</strong>
                        <div class="role-description">Access student dashboard</div>
                    </div>
                    <div class="role-option" data-role="mentor">
                        <strong>Mentor</strong>
                        <div class="role-description">Mental health support</div>
                    </div>
                    <div class="role-option" data-role="admin">
                        <strong>Administrator</strong>
                        <div class="role-description">Faculty only</div>
                    </div>
                </div>
                
                <div class="admin-notice" id="adminNotice">
                    <strong>Note:</strong> Admin registration is restricted to faculty members for privacy safeguarding. Your request will need approval.
                </div>
                
                <input type="hidden" id="role" name="role" value="student">
                
                <button type="submit">Continue</button>
                
                <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>
    
    <script>
        // Role selection
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.role-option').forEach(el => {
                    el.classList.remove('selected');
                });
                this.classList.add('selected');
                const role = this.dataset.role;
                document.getElementById('role').value = role;
                
                // Show/hide admin notice
                const adminNotice = document.getElementById('adminNotice');
                adminNotice.style.display = role === 'admin' ? 'block' : 'none';
            });
        });
        
        // Set default selected
        document.querySelector('[data-role="student"]').classList.add('selected');
        
        // Email validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            if (!email.toLowerCase().endsWith('@rcciit.org.in')) {
                e.preventDefault();
                alert('Please use your college email ending with @rcciit.org.in');
                return false;
            }
            return true;
        });
    </script>
</body>
</html>