<?php
session_start();

/* If user directly opens this page */
if (!isset($_SESSION['temp_user'])) {
    header("Location: signup.php");
    exit();
}

$error = "";

/* OTP Verify */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $enteredOtp = trim($_POST["otp"]);

    /* OTP Expiry: 5 minutes */
    if (time() - $_SESSION['temp_user']['otp_time'] > 300) {
        $error = "OTP expired. Please sign up again.";
        session_unset();
        session_destroy();
    }
    /* OTP Match */
    elseif ($enteredOtp === (string)$_SESSION['temp_user']['otp']) {
        $_SESSION['otp_verified'] = true;   // Important flag
        header("Location: personal_details.php");
        exit();
    }
    /* Wrong OTP */
    else {
        $error = "Invalid OTP. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email Verification</title>
    <style>
        body {
            background: #2b2b2b;
            color: #fff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .otp-box {
            background: #1f1f1f;
            padding: 30px;
            width: 350px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }
        input {
            width: 100%;
            padding: 12px;
            border-radius: 8px;
            border: none;
            margin-top: 15px;
            font-size: 16px;
        }
        button {
            margin-top: 15px;
            width: 100%;
            padding: 12px;
            background: teal;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #00a5a5;
        }
        .error {
            background: #3a1f1f;
            color: #ffb3b3;
            padding: 10px;
            margin-top: 15px;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="otp-box">
    <h2>Email Verification</h2>
    <p>
        Enter the OTP sent to<br>
        <b><?php echo htmlspecialchars($_SESSION['temp_user']['email']); ?></b>
    </p>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="otp" placeholder="6-digit OTP" maxlength="6" required>
        <button type="submit">Verify OTP</button>
    </form>
</div>

</body>
</html>
*/