<?php
session_start();
include('config/db.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $role     = $_POST['role'] ?? '';

    echo "Username received: " . htmlspecialchars($username) . "<br>";

    if (empty($username) || empty($password) || empty($role)) {
        echo "❌ Username, password, or role not provided.";
        exit;
    }

    if ($role === 'admin') {
        // Admin table has column 'username'
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ?");
    } else {
        // Students and Faculty share 'users' table with 'username' and 'role' columns
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // For students and faculty, validate the role from the DB
        if ($role !== 'admin' && $user['role'] !== $role) {
            echo "❌ Role mismatch. You selected '$role' but your account is '{$user['role']}'.";
            exit;
        }

        if (password_verify($password, $user['password'])) {
            echo "✅ Password matched!<br>";
            $_SESSION['user'] = $user;
            $_SESSION['role'] = $role;

            if ($role === 'student') {
                header("Location: student/dashboard.php");
            } elseif ($role === 'faculty') {
                header("Location: faculty/dashboard.php");
            } elseif ($role === 'admin') {
                header("Location: admin/dashboard.php");
            }
            exit;
        } else {
            echo "❌ Incorrect password.";
        }
    } else {
        echo "❌ No user found with that username.";
    }
} else {
    echo "Invalid request.";
}
