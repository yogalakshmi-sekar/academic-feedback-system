<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$faculty_id = $_SESSION['user']['id'];

// Total feedback count
$count_query = $conn->prepare("SELECT COUNT(*) AS total FROM feedback WHERE faculty_id = ?");
$count_query->bind_param("i", $faculty_id);
$count_query->execute();
$count_result = $count_query->get_result();
$count = $count_result->fetch_assoc()['total'] ?? 0;
$count_query->close();

// Average rating
$rating_query = $conn->prepare("SELECT ROUND(AVG(rating), 2) AS average FROM feedback WHERE faculty_id = ?");
$rating_query->bind_param("i", $faculty_id);
$rating_query->execute();
$rating_result = $rating_query->get_result();
$avg = $rating_result->fetch_assoc()['average'] ?? 0;
$rating_query->close();

echo json_encode([
    'total_feedback' => $count,
    'avg_rating' => $avg
]);
