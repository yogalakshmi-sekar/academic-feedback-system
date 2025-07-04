<?php
session_start();
include('../config/db.php');

// ✅ Ensure student is logged in
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

// ✅ Language translation
$lang = $_SESSION['lang'] ?? 'en';
$lang_path = "../lang/{$lang}.php";
$translations = file_exists($lang_path) ? include $lang_path : include "../lang/en.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id = $_SESSION['user']['id'];
    $subject_id = $_POST['subject_id'] ?? '';
    $faculty_id = $_POST['faculty_id'] ?? '';
    $rating     = $_POST['rating'] ?? '';
    $comments   = $_POST['comments'] ?? '';

    // ✅ Validate subject ID
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        // ✅ Check for duplicate feedback
        $checkStmt = $conn->prepare("SELECT id FROM feedback WHERE student_id = ? AND faculty_id = ? AND subject_id = ?");
        $checkStmt->bind_param("iii", $student_id, $faculty_id, $subject_id);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            $message = $translations['feedback_duplicate'];
        } else {
            // ✅ Insert feedback
            $insertStmt = $conn->prepare("INSERT INTO feedback (student_id, faculty_id, subject_id, rating, comment) VALUES (?, ?, ?, ?, ?)");
            $insertStmt->bind_param("iiiis", $student_id, $faculty_id, $subject_id, $rating, $comments);

            if ($insertStmt->execute()) {
                echo "<script>
                    alert('" . $translations['feedback_success'] . "');
                    localStorage.removeItem('saved_comment');
                    localStorage.removeItem('saved_rating');
                    window.location.href = 'dashboard.php';
                </script>";
                exit();
            } else {
                $message = $translations['feedback_error'] . " " . $insertStmt->error;
            }

            $insertStmt->close();
        }
        $checkStmt->close();
    } else {
        $message = $translations['invalid_subject'];
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>">

<head>
    <meta charset="UTF-8">
    <title><?= $translations['submit_feedback'] ?></title>
    <style>
        :root {
            --base-font-size: 16px;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            font-size: var(--base-font-size);
            background-color: #eef2f7;
            display: flex;
        }

        .sidebar {
            width: 220px;
            background: #0f172a;
            color: white;
            padding-top: 30px;
            height: 100vh;
            position: fixed;
            left: 0;
        }

        .sidebar h2 {
            text-align: center;
            color: orange;
            margin-bottom: 30px;
        }

        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: white;
            text-decoration: none;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background: #1e293b;
        }

        .main {
            margin-left: 240px;
            padding: 40px;
            width: 100%;
        }

        .form-card {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: auto;
        }

        .form-card h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 12px;
        }

        select,
        input[type=number],
        textarea {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        input[type=submit] {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
        }

        input[type=submit]:hover {
            background-color: #43a047;
        }

        .settings-panel {
            position: fixed;
            top: 100px;
            right: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 20px;
            width: 200px;
        }

        .tips {
            position: fixed;
            top: 100px;
            left: 260px;
            background: #1e293b;
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
    <script>
        function loadFaculty(subjectId) {
            if (subjectId === "") {
                document.getElementById("faculty_id").innerHTML = "<option value=''>-- <?= $translations['faculty'] ?> --</option>";
                return;
            }
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "get_faculty.php?subject_id=" + subjectId, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById("faculty_id").innerHTML = this.responseText;
                }
            };
            xhr.send();
        }

        function changeTextSize(value) {
            document.documentElement.style.setProperty('--base-font-size', value + 'px');
        }

        window.addEventListener('load', () => {
            if (!navigator.onLine) alert("<?= $translations['offline_alert'] ?>");
            window.addEventListener('offline', () => alert("<?= $translations['offline_alert'] ?>"));
            window.addEventListener('online', () => alert("<?= $translations['back_online'] ?>"));
        });

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const commentBox = form.querySelector("textarea[name='comments']");
            const ratingBox = form.querySelector("input[name='rating']");

            commentBox.value = localStorage.getItem("saved_comment") || "";
            ratingBox.value = localStorage.getItem("saved_rating") || "";

            commentBox.addEventListener("input", () => localStorage.setItem("saved_comment", commentBox.value));
            ratingBox.addEventListener("input", () => localStorage.setItem("saved_rating", ratingBox.value));
        });
    </script>
</head>

<body>
    <div class="sidebar">
        <h2>AFS ▾</h2>
        <a href="dashboard.php"><?= $translations['student_dashboard'] ?? 'Dashboard' ?></a>
        <a href="../index.php"><?= $translations['login_page'] ?></a>
        <a href="../logout.php"><?= $translations['logout'] ?></a>
        <form method="POST" action="../switch_lang.php" style="padding: 12px;">
            <label for="lang">🌐 <?= $translations['language'] ?>:</label>
            <select name="lang" onchange="this.form.submit()">
                <option value="en" <?= ($lang === 'en') ? 'selected' : '' ?>>English</option>
                <option value="ta" <?= ($lang === 'ta') ? 'selected' : '' ?>>தமிழ்</option>
            </select>
        </form>
    </div>

    <div class="main">
        <div class="form-card">
            <h2>📝 <?= $translations['submit_feedback'] ?></h2>

            <?php if ($message): ?>
                <p style="color: red; text-align: center;"> <?= htmlspecialchars($message) ?> </p>
            <?php endif; ?>

            <form method="POST">
                <label for="subject_id"><?= $translations['subject'] ?>:</label>
                <select name="subject_id" id="subject_id" onchange="loadFaculty(this.value)" required>
                    <option value="">-- <?= $translations['subject'] ?> --</option>
                    <?php
                    $query = "SELECT id, subject_name FROM subjects";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['id']}'>{$row['subject_name']}</option>";
                    }
                    ?>
                </select>

                <label for="faculty_id"><?= $translations['faculty'] ?>:</label>
                <select name="faculty_id" id="faculty_id" required>
                    <option value="">-- <?= $translations['faculty'] ?> --</option>
                </select>

                <label for="rating"><?= $translations['rating'] ?>:</label>
                <input type="number" name="rating" min="1" max="5" required>

                <label for="comments"><?= $translations['comments'] ?>:</label>
                <textarea name="comments" rows="4" required></textarea>

                <input type="submit" value="<?= $translations['submit'] ?>">
            </form>
        </div>
    </div>

    <div class="settings-panel">
        <strong>Settings</strong><br>
        <label for="text-size"><?= $translations['language'] ?> Size:</label>
        <input type="range" min="12" max="24" value="16" oninput="changeTextSize(this.value)">
    </div>

    <div class="tips" style="display:none;">
        <strong>📖 Tips:</strong>
        <ul>
            <li><?= $translations['submit_feedback'] ?></li>
            <li>All data is stored securely.</li>
        </ul>
    </div>
</body>

</html>