<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Additional security checks can be added here
function checkRole($allowedRoles) {
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        header('Location: unauthorized.php');
        exit();
    }
}
?>