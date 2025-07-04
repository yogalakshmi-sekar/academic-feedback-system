<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

$subjects = $conn->query("SELECT id, subject_name FROM subjects");

$results = [];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $subject_id = $_POST['subject_id'] ?? '';
    $rating = $_POST['rating'] ?? '';
    $from = $_POST['from'] ?? '';
    $to = $_POST['to'] ?? '';

    $conditions = [];
    if ($subject_id) $conditions[] = "subject_id = " . intval($subject_id);
    if ($rating) $conditions[] = "rating = " . intval($rating);
    if ($from && $to) $conditions[] = "DATE(submitted_at) BETWEEN '$from' AND '$to'";

    $where = count($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $query = "SELECT * FROM feedback $where ORDER BY submitted_at DESC";
    $results = $conn->query($query);
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Search Feedback</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            background: #f3f3f3;
        }

        form {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            max-width: 700px;
            margin: auto;
        }

        input,
        select {
            padding: 10px;
            width: 100%;
            margin-top: 8px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 30px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #eee;
        }

        h2 {
            text-align: center;
        }

        .btn {
            background-color: #3498db;
            color: #fff;
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <h2>🔍 Search Feedback</h2>

    <form method="POST">
        <label>Subject:</label>
        <select name="subject_id">
            <option value="">-- All Subjects --</option>
            <?php while ($s = $subjects->fetch_assoc()): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['subject_name']) ?></option>
            <?php endwhile; ?>
        </select>

        <label>Rating:</label>
        <select name="rating">
            <option value="">-- All Ratings --</option>
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> Star</option>
            <?php endfor; ?>
        </select>

        <label>Date Range:</label>
        <input type="date" name="from">
        <input type="date" name="to">

        <input type="submit" class="btn" value="Search">
    </form>

    <?php if ($results && $results->num_rows > 0): ?>
        <table>
            <tr>
                <th>Student ID</th>
                <th>Faculty ID</th>
                <th>Subject ID</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Date</th>
            </tr>
            <?php while ($row = $results->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['student_id'] ?></td>
                    <td><?= $row['faculty_id'] ?></td>
                    <td><?= $row['subject_id'] ?></td>
                    <td><?= $row['rating'] ?></td>
                    <td><?= htmlspecialchars($row['comment']) ?></td>
                    <td><?= date("d M Y", strtotime($row['submitted_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php elseif ($_SERVER["REQUEST_METHOD"] === "POST"): ?>
        <p style="text-align:center;">❌ No feedbacks found for selected filters.</p>
    <?php endif; ?>

    <div class="back-link">
        <a href="dashboard.php">🔙 Back to Dashboard</a>
    </div>

</body>

</html>