<?php
session_start();
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['program'])) {
    header("Location: index.php");
    exit();
}

require_once "db_connection.php";

$program = $_SESSION['user']['program']; // Get the program of the logged-in user

// Query the database for upload data based on the user's program
$sql = "SELECT year, COUNT(*) as uploads FROM research_file WHERE program = ? GROUP BY year ORDER BY year ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $program);
$stmt->execute();
$result = $stmt->get_result();

$yearly_data = [];
while ($row = $result->fetch_assoc()) {
    $yearly_data[] = $row;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BSPSY Research File Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #ecf0f1;
        }
        h1, h2 {
            text-align: center;
            color: #2c3e50;
        }
        h1 {
            font-size: 36px;
            margin-top: 20px;
            color: #e74c3c;
        }
        h2 {
            font-size: 24px;
            color: #7f8c8d;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .back-button {
            background-color: #e74c3c;
            color: white;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            border-radius: 5px;
            text-decoration: none;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .back-button:hover {
            background-color: #c0392b;
        }
        .chart-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        .chart {
            width: 300px;
            height: 300px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        p {
            color: #34495e;
            font-size: 16px;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        <h1>BSPSY Research File Dashboard</h1>
        <h2>Program: <?php echo htmlspecialchars($program); ?></h2>

        <div class="chart-container">
            <div>
                <canvas id="barChart" class="chart"></canvas>
                <p>Bar Chart</p>
            </div>
            <div>
                <canvas id="lineChart" class="chart"></canvas>
                <p>Line Chart</p>
            </div>
            <div>
                <canvas id="pieChart" class="chart"></canvas>
                <p>Pie Chart</p>
            </div>
        </div>
    </div>

    <script>
        // Data from PHP
        const yearlyData = <?php echo json_encode($yearly_data); ?>;
        const years = yearlyData.map(data => data.year);
        const uploads = yearlyData.map(data => data.uploads);

        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: true },
            },
        };

        // Bar Chart
        new Chart(document.getElementById('barChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: years,
                datasets: [{
                    label: 'Number of Uploads',
                    data: uploads,
                    backgroundColor: 'rgba(231, 76, 60, 0.5)',
                    borderColor: 'rgba(231, 76, 60, 1)',
                    borderWidth: 1
                }]
            },
            options: commonOptions
        });

        // Line Chart
        new Chart(document.getElementById('lineChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: years,
                datasets: [{
                    label: 'Number of Uploads',
                    data: uploads,
                    backgroundColor: 'rgba(236, 112, 99, 0.2)',
                    borderColor: 'rgba(236, 112, 99, 1)',
                    borderWidth: 1,
                    tension: 0.4,
                }]
            },
            options: commonOptions
        });

        // Pie Chart
        new Chart(document.getElementById('pieChart').getContext('2d'), {
            type: 'pie',
            data: {
                labels: years,
                datasets: [{
                    data: uploads,
                    backgroundColor: years.map(() => `rgba(${Math.random() * 255}, ${Math.random() * 255}, ${Math.random() * 255}, 0.6)`),
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                },
            }
        });
    </script>
</body>
</html>
