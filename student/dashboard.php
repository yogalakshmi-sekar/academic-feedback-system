<?php
session_start();
include('../config/db.php');

// Ensure user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// ✅ Load language
$lang = $_SESSION['lang'] ?? 'en';
$lang_path = "../lang/{$lang}.php";
if (file_exists($lang_path)) {
    $translations = include $lang_path;
} else {
    $translations = include "../lang/en.php"; // fallback to English
}

// ✅ Load student data
$student = $_SESSION['user'];
?>

<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= $translations['student_dashboard'] ?? 'Student Dashboard' ?></title>
    <style>
        body {
            margin: 0;
            background: #f0f2f5;
            font-family: 'Segoe UI', sans-serif;
        }

        .card,
        label,
        select,
        input,
        textarea,
        button {
            transition: all 0.3s ease;
        }

        .sidebar {
            width: 200px;
            background-color: #111827;
            color: #facc15;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px;
        }

        .sidebar h3 {
            margin: 0 0 30px;
            font-size: 1.5rem;
        }

        .sidebar a {
            display: block;
            color: white;
            margin: 12px 0;
            text-decoration: none;
            padding: 8px 10px;
            border-radius: 6px;
        }

        .sidebar a:hover {
            background-color: #374151;
        }

        main {
            margin-left: 220px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .card {
            background: #ffffff;
            padding: 50px 60px;
            border-radius: 20px;
            box-shadow: 10px 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 650px;
        }

        h2 {
            font-size: 2.4rem;
        }

        p {
            font-size: 1.1rem;
            margin: 10px 0;
            color: #444;
        }

        .btn {
            display: inline-block;
            padding: 14px 24px;
            margin: 10px 5px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .btn-feedback {
            background-color: #4CAF50;
            color: white;
        }

        .btn-logout {
            background-color: #f44336;
            color: white;
        }

        .btn:hover {
            transform: scale(1.05);
        }

        select[name="lang"] {
            margin-top: 30px;
            padding: 5px;
            border-radius: 6px;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h3>AFS ▼</h3>
        <a href="../index.php"><?= $translations['login_page'] ?? 'Login Page' ?></a>
        <a href="submit_feedback.php"><?= $translations['submit_feedback'] ?? 'Submit Feedback' ?></a>
        <a href="../logout.php"><?= $translations['logout'] ?? 'Logout' ?></a>

        <form method="POST" action="../switch_lang.php">
            <label for="lang" style="margin-top: 20px; display:block; color: white;">🌐 <?= $translations['language'] ?? 'Language' ?></label>
            <select name="lang" onchange="this.form.submit()">
                <option value="en" <?= $lang == 'en' ? 'selected' : '' ?>>English</option>
                <option value="ta" <?= $lang == 'ta' ? 'selected' : '' ?>>தமிழ்</option>
            </select>
        </form>
    </div>

    <main>
        <div class="card">
            <h2>🎓 <?= $translations['welcome'] ?? 'Welcome' ?>, <?= htmlspecialchars($student['name']) ?></h2>
            <p>📘 <?= $translations['branch'] ?? 'Branch' ?>: <strong><?= htmlspecialchars($student['branch']) ?></strong></p>
            <p>📅 <?= $translations['year'] ?? 'Year' ?>: <strong><?= htmlspecialchars($student['year']) ?></strong></p>

            <a class="btn btn-feedback" href="submit_feedback.php">📝 <?= $translations['submit_feedback'] ?? 'Submit Feedback' ?></a>
            <a href="../logout.php" class="btn btn-logout">🔴 <?= $translations['logout'] ?? 'Logout' ?></a>
        </div>
    </main>

</body>

</html>