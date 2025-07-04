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
    <title>📊 3D Bar Chart - Feedback</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #e0e0e0;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            text-align: center;
        }

        .chart-container {
            max-width: 850px;
            margin: auto;
            background: #e0e0e0;
            padding: 40px;
            border-radius: 25px;
            box-shadow: 20px 20px 60px #bebebe,
                -20px -20px 60px #ffffff;
        }

        canvas {
            margin-top: 20px;
            background: #f1f1f1;
            border-radius: 15px;
            padding: 15px;
            box-shadow: inset 8px 8px 15px #cfcfcf,
                inset -8px -8px 15px #ffffff;
        }

        .btn-container {
            margin-top: 30px;
        }

        .btn {
            padding: 12px 25px;
            margin: 0 10px;
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            background: linear-gradient(145deg, #1ecbe1, #1aaebd);
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: inline-block;
            transition: 0.3s ease;
        }

        .btn:hover {
            background: linear-gradient(145deg, #1aaebd, #1ecbe1);
            transform: scale(1.05);
        }

        h2 {
            margin-bottom: 30px;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="chart-container">
        <h2>📊 Feedback Rating Distribution </h2>
        <canvas id="barChart" width="700" height="400"></canvas>

        <div class="btn-container">
            <a href="dashboard.php" class="btn">🔙 Back to Dashboard</a>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('barChart').getContext('2d');

        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(75, 192, 192, 0.9)');
        gradient.addColorStop(1, 'rgba(75, 192, 192, 0.2)');

        const barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels); ?>,
                datasets: [{
                    label: 'Rating',
                    data: <?= json_encode($ratings); ?>,
                    backgroundColor: gradient,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    borderRadius: 10,
                    barThickness: 30
                }]
            },
            options: {
                scales: {
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            color: '#555'
                        }
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5,
                        grid: {
                            color: 'rgba(0,0,0,0.05)'
                        },
                        ticks: {
                            color: '#555'
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: '#333',
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#fff',
                        titleColor: '#000',
                        bodyColor: '#000',
                        borderColor: '#ccc',
                        borderWidth: 1
                    }
                }
            }
        });
    </script>
</body>

</html>