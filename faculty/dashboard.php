<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit();
}

$faculty_id = $_SESSION['user']['id'];
$faculty_name = $_SESSION['user']['name'];
$course = isset($_SESSION['user']['subject']) ? $_SESSION['user']['subject'] : 'N/A';


// Count students enrolled
$student_count = 0;
$avg_rating = 0;
$recent_comments = [];

$student_query = $conn->prepare("SELECT COUNT(*) as total FROM feedback WHERE faculty_id = ?");
$student_query->bind_param("i", $faculty_id);
$student_query->execute();
$result = $student_query->get_result();
if ($row = $result->fetch_assoc()) {
    $student_count = $row['total'];
}
$student_query->close();

// Average rating
$rating_query = $conn->prepare("SELECT AVG(rating) as average FROM feedback WHERE faculty_id = ?");
$rating_query->bind_param("i", $faculty_id);
$rating_query->execute();
$rating_result = $rating_query->get_result();
if ($row = $rating_result->fetch_assoc()) {
    $avg_rating = round($row['average'], 2);
}
$rating_query->close();

// Pagination setup
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Get paginated feedback
$comments_query = $conn->prepare("SELECT comment, submitted_at FROM feedback WHERE faculty_id = ? ORDER BY submitted_at DESC LIMIT ? OFFSET ?");
$comments_query->bind_param("iii", $faculty_id, $limit, $offset);
$comments_query->execute();
$comment_result = $comments_query->get_result();

$recent_comments = [];
while ($row = $comment_result->fetch_assoc()) {
    $recent_comments[] = [
        'comment' => $row['comment'],
        'submitted_at' => date("d M Y, h:i A", strtotime($row['submitted_at']))
    ];
}
$comments_query->close();

// Count total feedback for pagination
$total_count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM feedback WHERE faculty_id = ?");
$total_count_stmt->bind_param("i", $faculty_id);
$total_count_stmt->execute();
$total_result = $total_count_stmt->get_result()->fetch_assoc();
$total_pages = ceil($total_result['total'] / $limit);
$total_count_stmt->close();


while ($row = $comment_result->fetch_assoc()) {
    $recent_comments[] = [
        'comment' => $row['comment'],
        'submitted_at' => date("d M Y, h:i A", strtotime($row['submitted_at']))
    ];
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>

<body>
    <div class="container">
        <h2>👩‍🏫 Faculty Dashboard</h2>

        <div class="card">
            <h3>👤 Name: <?= htmlspecialchars($faculty_name) ?></h3>
            <h4>📚 Course: <?= htmlspecialchars($course) ?></h4>
        </div>

        <div class="card">
            <h3>👨‍🎓 Students Enrolled: <span id="totalFeedback"><?= $student_count ?></span></h3>
        </div>

        <div class="card">
            <h3>📊 Average Rating: <span id="avgRating"><?= $avg_rating ?></span></h3>
        </div>

        <div class="card">
            <h3>🗣 Recent Feedback</h3>
            <ul id="feedbackList">
                <?php if (count($recent_comments) > 0): ?>
                    <?php foreach ($recent_comments as $feedback): ?>
                        <li>
                            <strong>Anonymous:</strong> <?= htmlspecialchars($feedback['comment']) ?><br>
                            <small>🕒 <?= $feedback['submitted_at'] ?></small>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No feedback yet.</li>
                <?php endif; ?>
            </ul>
            <!-- Pagination Controls -->
            <div style="text-align: center; margin-top: 15px;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>" style="margin: 0 5px; padding: 6px 12px; text-decoration: none;
        background-color: <?= $i == $page ? '#4CAF50' : '#ccc' ?>; color: <?= $i == $page ? 'white' : 'black' ?>;
        border-radius: 6px;"><?= $i ?></a>
                <?php endfor; ?>
            </div>

        </div>


        <div class="bottom-actions">
            <a href="analytics.php" class="btn-analytics">📈 View Analytics</a>
            <a href="../logout.php" class="btn-logout">🔴 Logout</a>
        </div>


    </div>
    <script>
        setInterval(() => {
            fetch('get_feedback_stats.php')
                .then(res => res.json())
                .then(data => {
                    document.getElementById('totalFeedback').innerText = data.total_feedback;
                    document.getElementById('avgRating').innerText = data.avg_rating;
                });

            fetch('get_recent_feedback.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('feedbackList').innerHTML = html;
                });
        }, 5000); // refresh every 5 seconds
    </script>



</body>

</html>