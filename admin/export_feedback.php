<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="feedback_export.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Student Name', 'Faculty Name', 'Subject', 'Rating', 'Comment', 'Submitted At']);

$query = "
SELECT 
    s.username AS student_name,
    f.username AS faculty_name,
    sub.subject_name AS subject,
    fb.rating,
    fb.comment,
    fb.submitted_at
FROM feedback fb
JOIN students s ON fb.student_id = s.id
JOIN faculty f ON fb.faculty_id = f.id
JOIN subjects sub ON fb.subject_id = sub.id
ORDER BY fb.submitted_at DESC
";


$result = $conn->query($query);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
