<?php
session_start();
include('../config/db.php');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'faculty') {
    header("Location: ../login.php");
    exit();
}

$faculty_id = $_SESSION['user']['id'];
$ratings = [];
$labels = [];

$stmt = $conn->prepare("SELECT rating, submitted_at FROM feedback WHERE faculty_id = ? ORDER BY submitted_at");
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $ratings[] = $row['rating'];
    $labels[] = date("d M", strtotime($row['submitted_at']));
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>📊 Feedback Line Chart</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f1f1;
            padding: 40px;
            text-align: center;
        }

        .chart-container {
            max-width: 800px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 5px 20px rgba(0, 0, 0, 0.2);
        }

        canvas {
            margin-top: 20px;
            border-radius: 12px;
        }

        .btn-group {
            margin-top: 30px;
        }

        .btn-group a {
            padding: 10px 20px;
            font-weight: bold;
            background-color: rgb(20, 162, 172);
            color: white;
            border-radius: 10px;
            text-decoration: none;
            margin: 0 10px;
        }

        .btn-group a:hover {
            background-color: rgb(9, 170, 188);
        }
    </style>
</head>

<body>

    <div class="chart-container">
        <h2>📈 Feedback Rating Over Time </h2>
        <canvas id="ratingChart" width="600" height="400"></canvas>
        <div class="btn-group">
            <a href="bar-analytics.php">➡ Next: Bar Chart</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('ratingChart').getContext('2d');
        const ratingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Rating',
                    data: <?= json_encode($ratings); ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5
                    }
                }
            }
        });
    </script>
</body>

</html>