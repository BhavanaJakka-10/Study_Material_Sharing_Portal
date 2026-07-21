<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "study_portal"; // <--- MUST MATCH YOUR phpMyAdmin DB NAME

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>