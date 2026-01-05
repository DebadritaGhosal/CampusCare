<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: Home.php');
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
    * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    min-height: 100vh;
    background: #2b2b2b;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    transition: 0.3s;
}

/* Light Mode */
body.light {
    background: #f4f6f8;
    color: #000;
}

.container {
    width: 100%;
    padding: 20px;
}

.signup-box {
    background: #1f1f1f;
    max-width: 420px;
    margin: auto;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    position: relative;
}

body.light .signup-box {
    background: #fff;
}
.theme-toggle {
    position: absolute;
    top: 15px;
    right: 15px;
    cursor: pointer;
    font-size: 18px;
}

.signup-box h1 {
    text-align: center;
    color: #2ecc71;
}

.signup-box p {
    text-align: center;
    margin-bottom: 25px;
    color: #aaa;
}

label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
}

input {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    border: none;
    background: #eaf1ff;
    margin-bottom: 12px;
}

.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 10;
}

.strength-meter {
    width: 100%;
    height: 6px;
    background: #ccc;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 5px;
}

#strengthBar {
    height: 100%;
    width: 0%;
    background: red;
    transition: 0.3s;
}

small {
    font-size: 12px;
    color: #aaa;
}

.role-options {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.role-option {
    flex: 1;
    background: #2a2a2a;
    border: 2px solid #444;
    border-radius: 8px;
    padding: 12px;
    text-align: center;
    cursor: pointer;
}

.role-option.selected {
    border-color: #2ecc71;
    background: #1f3327;
}
.role-description {
    color: #aaa;
    margin-top: 6px;
}
body.light .role-option {
    background: #f0f0f0;
    border-color: #ccc;
}
body.light .role-option.selected {
    background: #d4edda;
    border-color: #28a745;
    color: #155724;
}
body.light .role-description {
    color: #464545ff;
}
button {
    width: 100%;
    padding: 12px;
    background: #008b8b;
    border: none;
    color: #fff;
    font-size: 16px;
    border-radius: 10px;
    cursor: pointer;
}

button:hover {
    background: #00a5a5;
}

.login-link {
    margin-top: 15px;
    text-align: center;
}
.login-link a {
    color: #2ecc71;
    text-decoration: none;
}

/* ---------- Mobile ---------- */
@media (max-width: 480px) {
    .signup-box {
        padding: 25px;
    }

    .role-options {
        flex-direction: column;
    }
}
</style>
</head>
<body>
    <div class="container">
        <div class="signup-box">
            <h1>Sign Up</h1>
            <div class="theme-toggle" id="toggleTheme">☾</div>
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
<div class="password-wrapper">
    <input type="password" id="password" name="password" placeholder="Must be atleast 6 characters"required>
    <span id="togglePassword" class="toggle-password">👁️</span>
</div>

<div class="strength-meter">
    <div id="strengthBar"></div>
</div>
<small id="strengthText"></small>
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
                
                <div class="admin-notice" id="adminNotice" style="margin-bottom: 15px; color: #cc5656ff;">
                    <strong>Note:</strong> Admin registration is restricted to faculty members for privacy safeguarding. Your request will need approval.
                </div>
                
                <input type="hidden" id="role" name="role" value="student">
                
                <button type="submit">Continue</button>
                
                <p class="login-link">Already have an account? <a href="login.php">Log in</a></p>
            </form>
        </div>
    </div>
    
     <script>
        document.addEventListener("DOMContentLoaded", () => {
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
    const toggleBtn = document.getElementById('toggleTheme');
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('light');
      toggleBtn.textContent = document.body.classList.contains('light') ? '☀︎' : '☾';

    });
    const pwd = document.getElementById("password");
    const toggle = document.getElementById("togglePassword");
    const strengthBar = document.getElementById("strengthBar");
    const strengthText = document.getElementById("strengthText");

    /* 👁️ Show / Hide Password */
    toggle.addEventListener("click", () => {
        pwd.type = pwd.type === "password" ? "text" : "password";
        toggle.textContent = pwd.type === "password" ? "👁️" : "🙈";
    });

    /* 🔒 Password Strength */
    pwd.addEventListener("input", () => {
        const value = pwd.value;
        let strength = 0;

        if (value.length >= 6) strength++;
        if (/[A-Z]/.test(value)) strength++;
        if (/[0-9]/.test(value)) strength++;
        if (/[^A-Za-z0-9]/.test(value)) strength++;

        const levels = ["Weak", "Medium", "Good", "Strong"];
        const colors = ["red", "orange", "yellowgreen", "green"];

        strengthBar.style.width = (strength * 25) + "%";
        strengthBar.style.background = colors[strength - 1] || "red";
        strengthText.textContent = levels[strength - 1] || "";
    });
});
    </script>
</body>
</html>