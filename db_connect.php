<?php
$host = 'localhost';
$user = 'root'; // or your database username
$pass = ''; // or your database password
$db = 'campuscare';
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>