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
$loggedInRole = $_SESSION['user']['role'];
$loggedInProgram = $_SESSION['user']['program']; // Program associated with the user
$loggedInEmail = $_SESSION['user']['email']; // Email of the logged-in user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BEED Research File Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        h1, h2 {
            text-align: center;
            color: #34495e;
        }
        h1 {
            font-size: 36px;
            margin-top: 20px;
            color: #27ae60;
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
            background-color: #27ae60;
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
            background-color: #2ecc71;
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
        <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
        }
        h1, h2 {
            text-align: center;
            color: #333;
        }
        h1 {
            margin-top: 20px;
            font-size: 36px;
            color: #4CAF50;
        }
        h2 {
            font-size: 24px;
            color: #555;
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
            background-color: #4CAF50;
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
            background-color: #45a049;
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
        body {
            position: relative;
            margin: 0;
            padding-top: 70px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/qsu.jpg') no-repeat center center;
            background-size: cover;
            filter: blur(8px);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.4);
            z-index: -1;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(90deg, #ffffff, #a5d6a7);
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #2e7d32;
            padding-right: 67.5rem;
        }

        .menu-button {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 21px;
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 10;
            position: relative;
            padding-right: 3rem;
        }

        .menu-button .bar {
            width: 30px;
            height: 3px;
            background-color: #388e3c;
            transition: all 0.3s;
        }

        .menu-button.open .bar:nth-child(1) {
            transform: rotate(45deg);
            position: relative;
        }

        .menu-button.open .bar:nth-child(2) {
            opacity: 0;
        }

        .menu-button.open .bar:nth-child(3) {
            transform: rotate(-45deg);
            position: relative;
        }

        .menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #ffffff;
            color: #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            width: 200px;
            display: none;
            transition: opacity 0.3s, transform 0.3s;
        }

        .menu.show {
            display: block;
            transform: translateY(10px);
            opacity: 1;
        }

        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .menu ul li {
            padding: 0.8rem 1.2rem;
        }

        .menu ul li a {
            text-decoration: none;
            color: #333;
            display: block;
            transition: background-color 0.3s, color 0.3s;
        }

        .menu ul li a:hover {
            background-color: #a5d6a7;
            color: #fff;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        p {
            color: #555;
            line-height: 1.6;
        }

        .btn-logout {
            background-color: red;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-logout:hover {
            background-color: #c0392b;
        }

        footer {
            background-color: #004f23;
            color: white;
            padding: 5px;
            text-align: center;
            margin-top: 0;
        }
    </style>
    <script>
        function toggleMenu() {
            var menu = document.getElementById('menu');
            var button = document.querySelector('.menu-button');
            menu.classList.toggle('show');
            button.classList.toggle('open');
        }
    </script>
</head>
<body>
    <header>
        <img src="images/qsu.png" alt="QSU Logo" style="height: 70px;">
        <h2>QSU Student Research</h2>
        <button class="menu-button" onclick="toggleMenu()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div id="menu" class="menu">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <?php if ($loggedInRole === 'admin'): ?>
                <li><a href="uploaded.php">Your Uploads</a></li>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <li><a href="reports.php">Reports</a></li>
                <?php endif; ?>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="support.php">Help & Support</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
        </div>
    </header>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
        <h1>BEED Research File Dashboard</h1>
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
                    backgroundColor: 'rgba(39, 174, 96, 0.5)',
                    borderColor: 'rgba(39, 174, 96, 1)',
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
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    borderColor: 'rgba(46, 204, 113, 1)',
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
