<?php
include('../config/db.php');

if (isset($_GET['subject_id'])) {
    $subject_id = intval($_GET['subject_id']);

    $stmt = $conn->prepare("
        SELECT faculty.id, faculty.name 
        FROM faculty 
        INNER JOIN subjects ON subjects.faculty_id = faculty.id 
        WHERE subjects.id = ?
    ");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>-- Choose Faculty --</option>";
    if ($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
    }
}
