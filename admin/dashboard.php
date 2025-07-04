<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../config/db.php';
$admin = $_SESSION['user'];

// Stats
$totalStudents = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$totalFaculty  = $conn->query("SELECT COUNT(*) FROM faculty")->fetch_row()[0];
$totalFeedback = $conn->query("SELECT COUNT(*) FROM feedback")->fetch_row()[0];
$avgRating     = $conn->query("SELECT ROUND(AVG(rating), 2) FROM feedback")->fetch_row()[0];

// Rating distribution for pie chart
$ratingCounts = [];
for ($i = 1; $i <= 5; $i++) {
    $count = $conn->query("SELECT COUNT(*) FROM feedback WHERE rating = $i")->fetch_row()[0];
    $ratingCounts[] = $count;
}

// Monthly performance data for line chart
$months = [];
$monthlyAvgRatings = [];
for ($m = 1; $m <= 12; $m++) {
    $monthLabel = date("M", mktime(0, 0, 0, $m, 1));
    $months[] = $monthLabel;
    $avg = $conn->query("SELECT ROUND(AVG(rating), 2) FROM feedback WHERE MONTH(submitted_at) = $m")->fetch_row()[0] ?? 0;
    $monthlyAvgRatings[] = $avg ?: 0;
}

// Recent comments
$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$commentsQuery = $conn->prepare("SELECT comment, submitted_at FROM feedback ORDER BY submitted_at DESC LIMIT ? OFFSET ?");
$commentsQuery->bind_param("ii", $limit, $offset);
$commentsQuery->execute();
$comment_result = $commentsQuery->get_result();
$comments = $comment_result->fetch_all(MYSQLI_ASSOC);
$commentsQuery->close();

// Count total comments for pagination
$totalCount = $conn->query("SELECT COUNT(*) as total FROM feedback")->fetch_assoc();
$total_pages = ceil($totalCount['total'] / $limit);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #e0e0e0;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        .dashboard {
            max-width: 1300px;
            margin: auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }

        .card,
        .chart-container {
            background: #ffffff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 20px 20px 60px #bebebe, -20px -20px 60px #ffffff;
        }

        .card h3 {
            margin-bottom: 10px;
        }

        .stat {
            font-size: 1.2rem;
            margin: 10px 0;
        }

        .logout-btn {
            display: block;
            margin: 40px auto 0;
            padding: 12px 30px;
            background-color: #e74c3c;
            color: #fff;
            border-radius: 10px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s ease;
            text-align: center;
            width: max-content;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        .comments {
            list-style: none;
            padding: 0;
            margin-top: 10px;
        }

        .comments li {
            background: #f0f0f0;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: inset 4px 4px 8px #d0d0d0, inset -4px -4px 8px #ffffff;
            font-size: 14px;
        }

        canvas {
            margin-top: 15px;
            background: #f9f9f9;
            border-radius: 12px;
            padding: 15px;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <style>
        a:hover {
            transform: scale(1.08);
            transition: all 0.2s ease-in-out;
        }
    </style>

</head>

<body>

    <h2>👩‍💼 Welcome, <?= htmlspecialchars($admin['username']) ?> (Admin)</h2>

    <div class="dashboard">

        <div class="card">
            <h3>📊 Platform Stats</h3>
            <p class="stat">👨‍🎓 Total Students: <strong><?= $totalStudents ?></strong></p>
            <p class="stat">👩‍🏫 Total Faculty: <strong><?= $totalFaculty ?></strong></p>
            <p class="stat">📝 Total Feedbacks: <strong><?= $totalFeedback ?></strong></p>
            <p class="stat">⭐ Average Rating: <strong><?= $avgRating ?></strong></p>
            <a href="export_feedback.php" class="logout-btn" style="margin-top: 15px; background:#3498db">⬇ Export CSV</a>
            <a href="search_feedback.php" class="logout-btn" style="margin-top: 15px; background:#27ae60">🔍 Search Feedback</a>
        </div>

        <div class="card">
            <h3>💬 Recent Feedback</h3>
            <ul class="comments">
                <?php foreach ($comments as $c): ?>
                    <li>
                        <strong>Anonymous:</strong> <?= htmlspecialchars($c['comment']) ?><br>
                        <small>🕒 <?= date("d M, H:i", strtotime($c['submitted_at'])) ?></small>
                    </li>
                <?php endforeach; ?>
            </ul>

        </div>

        <!-- Styled Pagination Card to Match Dashboard UI -->
        <div class="card" style="padding: 25px; text-align: center; margin-top: 0px; box-shadow: 20px 20px 60px #bebebe, -20px -20px 60px #ffffff;">
            <h3 style="margin-bottom: 30px;">📄 Feedback Pages</h3>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>"
                        style="
                    padding: 20px 20px;
                    min-width: 40px;
                    font-weight: 600;
                    text-decoration: none;
                    font-size: 15px;
                    color: <?= $i == $page ? '#fff' : '#333' ?>;
                    background-color: <?= $i == $page ? '#3498db' : '#f0f0f0' ?>;
                    border-radius: 12px;
                    box-shadow: <?= $i == $page ? 'inset 2px 2px 6px #2c7cb8, inset -2px -2px 6px #3cb6ff' : '8px 8px 16px #d0d0d0, -8px -8px 16px #ffffff' ?>;
                    transition: all 0.3s ease;
               ">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>



        <div class="chart-container">
            <h3>📊 Student vs Faculty</h3>
            <canvas id="userChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>📈 Rating Distribution</h3>
            <canvas id="ratingPieChart"></canvas>
        </div>

        <div class="chart-container">
            <h3>📆 Monthly Avg Ratings</h3>
            <canvas id="lineChart"></canvas>
        </div>

    </div>

    <a href="../logout.php" class="logout-btn">🔓 Logout</a>

    <script>
        new Chart(document.getElementById('userChart'), {
            type: 'bar',
            data: {
                labels: ['Students', 'Faculty'],
                datasets: [{
                    label: 'Count',
                    data: [<?= $totalStudents ?>, <?= $totalFaculty ?>],
                    backgroundColor: ['#4CAF50', '#FF6384'],
                    borderRadius: 10
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        new Chart(document.getElementById('ratingPieChart'), {
            type: 'pie',
            data: {
                labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
                datasets: [{
                    data: <?= json_encode($ratingCounts) ?>,
                    backgroundColor: ['#FF9999', '#FFCC99', '#FFFF99', '#CCFF99', '#99FF99']
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('lineChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($months) ?>,
                datasets: [{
                    label: 'Avg Rating',
                    data: <?= json_encode($monthlyAvgRatings) ?>,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false,
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