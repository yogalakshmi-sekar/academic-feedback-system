<?php
$host = "localhost";
$username = "root";
$password = "YOGA@21092004";
$dbname = "feedback_system"; // Or your DB name

$conn = new mysqli($host, $username, $password, $dbname);

$conn = new mysqli("localhost", "root", "YOGA@21092004", "feedback_system");


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
