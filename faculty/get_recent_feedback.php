<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    http_response_code(403);
    exit('Unauthorized');
}

$faculty_id = $_SESSION['user']['id'];
$stmt = $conn->prepare("SELECT comment, submitted_at FROM feedback WHERE faculty_id = ? ORDER BY id DESC LIMIT 5");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo "<li><strong>Anonymous:</strong> " . htmlspecialchars($row['comment']) . "<br>";
    echo "<small>🕒 " . date("d M Y, h:i A", strtotime($row['submitted_at'])) . "</small></li>";
}
$stmt->close();
