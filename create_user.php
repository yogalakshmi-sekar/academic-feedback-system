<?php
include('config/db.php');

$name = "test student";
$username = "student1";
$password_plain = "yoga@123";
$role = "student";
$branch = "CSE";
$year = 3;

// Hash the password
$password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);

// Insert into users table
$sql = "INSERT INTO users (name, username, password, role, branch, year)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssi", $name, $username, $password_hashed, $role, $branch, $year);

if ($stmt->execute()) {
    echo "✅ User created successfully.";
} else {
    echo "❌ Error: " . $stmt->error;
}
