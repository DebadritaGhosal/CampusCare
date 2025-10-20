<?php
session_start();
if (!isset($_SESSION['temp_user'])) {
    header('Location: signup.php');
    exit();
}
$message = '';
$message_type = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dob = $_POST['dob'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $college = $_POST['college'] ?? '';
    if (empty($dob) || empty($gender) || empty($phone) || empty($college)) {
        $message = 'Please fill out all fields.';
        $message_type = 'error';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Phone number must be a 10-digit number.';
        $message_type = 'error';
    } else {
        $_SESSION['personal_details'] = [
            'dob' => $dob,
            'gender' => $gender,
            'phone' => $phone,
            'college' => $college
        ];
        $user_data = array_merge($_SESSION['temp_user'], $_SESSION['personal_details']);
        unset($_SESSION['temp_user']);
        unset($_SESSION['personal_details']);
        $message = 'Personal details submitted successfully!';
        $message_type = 'success';
        header('Refresh: 2; URL=home.php');
        echo '<div style="text-align:center;padding:20px;">Personal details submitted successfully! Redirecting to home page...</div>';
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Details</title>
  <link rel="stylesheet" href="s.css">
  <style>
    #message { margin-top: 12px; font-size: 14px; }
    #message.success { color: green; }
    #message.error { color: red; }
  </style>
</head>
<body>
  <div class="theme-toggle">
    <button id="toggleTheme">☾</button>
  </div>
  <div class="container">
    <div class="login-box">
      <h1>Personal Details</h1>
      <p>Please fill out your details</p>
      <form id="detailsForm" method="post" action="">
        <label for="dob">Date of Birth</label>
        <input type="date" id="dob" name="dob" required value="<?php echo isset($_POST['dob']) ? htmlspecialchars($_POST['dob']) : ''; ?>">
        <label for="gender">Gender</label>
        <select id="gender" name="gender" required>
          <option value="">--Select--</option>
          <option value="Male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
          <option value="Female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
          <option value="Other" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
        </select>

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" pattern="[0-9]{10}" placeholder="10-digit number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">

        <label for="college">College Name</label>
        <input type="text" id="college" name="college" required value="<?php echo isset($_POST['college']) ? htmlspecialchars($_POST['college']) : ''; ?>">

        <button type="submit">Submit</button>
      </form>
      <?php if ($message): ?>
        <div id="message" class="<?php echo htmlspecialchars($message_type); ?>">
          <?php echo htmlspecialchars($message); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <script>
    const toggleBtn = document.getElementById('toggleTheme');
    toggleBtn.addEventListener('click', () => {
      document.body.classList.toggle('dark');
      toggleBtn.textContent = document.body.classList.contains('dark') ? '☀︎' : '☾';
    });
    document.getElementById('detailsForm').addEventListener('submit', function(e) {
    });
  </script>
</body>
</html>