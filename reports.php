<?php
// Start session to access session variables
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch distinct programs, years, and advisers for dropdowns
$programsSql = "SELECT DISTINCT program FROM research_file";
$yearsSql = "SELECT DISTINCT year FROM research_file";
$advisersSql = "SELECT DISTINCT adviser FROM research_file";

$programsResult = $conn->query($programsSql);
$yearsResult = $conn->query($yearsSql);
$advisersResult = $conn->query($advisersSql);

// Get selected filters
$selectedProgram = isset($_GET['program']) ? $_GET['program'] : '';
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '';
$selectedAdviser = isset($_GET['adviser']) ? $_GET['adviser'] : '';

// Fetch report data based on filters
$sql = "SELECT id, program, title, authors, year, adviser, file_path 
        FROM research_file
        WHERE (program = '$selectedProgram' OR '$selectedProgram' = '')
        AND (year = '$selectedYear' OR '$selectedYear' = '')
        AND (adviser = '$selectedAdviser' OR '$selectedAdviser' = '')";

$result = $conn->query($sql);

// Get the logged-in user's details
$loggedInRole = $_SESSION['user']['role'];
$loggedInProgram = $_SESSION['user']['program'];
$loggedInEmail = $_SESSION['user']['email'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QSU Styled Page</title>
    <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            margin: 0;
            padding-top: 70px;
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
        }
        .menu-button.open .bar:nth-child(2) {
            opacity: 0;
        }
        .menu-button.open .bar:nth-child(3) {
            transform: rotate(-45deg);
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
        }
        .menu.show {
            display: block;
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
            max-width: 800px;
            margin: 2rem auto;
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        .container h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 1rem;
            font-weight: bold;
        }
        .form-group {
            margin-bottom: 1.5rem;
            display: inline-block;
            margin-right: 10px;
        }
        .form-group label {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group select,
        .form-group button {
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            cursor: pointer;
        }
        .form-group button {
            background-color: #388e3c;
            color: #fff;
            transition: background-color 0.3s;
        }
        .form-group button:hover {
            background-color: #2e7d32;
        }
        .btn-logout {
            background-color: red;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-logout:hover {
            background-color: #c0392b;
        }
        /* Horizontal layout for dropdowns */
        .filters-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .filters-container .form-group {
            margin-bottom: 0;
        }

        .filters-container select {
            width: 30%;
        }

        .table {
            width: 100%;
            margin-top: 2rem;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #388e3c;
            color: white;
        }
        .table tr:hover {
            background-color: #f1f1f1;
        }
        .btn-print {
            background-color: #4CAF50; /* Green color */
            color: white;
            padding: 0.7rem 1.2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 2rem;
            font-size: 14px;
        }
        .btn-print:hover {
            background-color:rgb(80, 196, 86);
        }
      select {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
    min-width: 200px; /* Ensure the dropdown is wide enough */
    max-width: 100%; /* Allow it to expand to fit the container */
    white-space: nowrap; /* Prevent wrapping the options */
    background-color: #f9f9f9;
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

select:focus {
    background-color: #e3f2fd;
    border-color: #42a5f5;
    outline: none;
}

select option {
    padding: 10px;
    font-size: 16px;
}

/* Styling for the 'Generate' button */
button[type="submit"] {
    background-color: #4CAF50; /* Green color */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

button[type="submit"]:hover {
    background-color: rgb(101, 192, 106); /* Slightly darker green on hover */
}

/* Print button styling */
.btn-print {
    background-color: #4CAF50; /* Green color */
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    margin-top: 10px;
}

.btn-print:hover {
    background-color: rgb(80, 196, 86); /* Slightly darker green on hover */
}



    </style>
    <script>
        function toggleMenu() {
            var menu = document.getElementById('menu');
            var button = document.querySelector('.menu-button');
            menu.classList.toggle('show');
            button.classList.toggle('open');
        }

        function printTable() {
            var printContent = document.getElementById("printableTable").innerHTML;
            var originalContent = document.body.innerHTML;
            document.body.innerHTML = printContent;
            window.print();
            document.body.innerHTML = originalContent;
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
                <li>
    <a href="<?php echo strtolower($loggedInProgram); ?>_dashboard.php">
        View Dashboard
    </a>
</li>
                <?php if ($loggedInEmail === 'a47226801@gmail.com'): ?>
                <!--li><a href="admin_dashboard.php">Your Dashboard</a></li-->
                    <li><a href="Add_admin.php">Add Admin Account</a></li> 
                <?php endif; ?>
            <?php endif; ?>
                <li><a href="support.php">Help & Support</a></li>
                <li><a href="whatsnew.php">What's New</a></li>
                <li><a href="mstrlist.php">View Masterlist</a></li>
                <!--li><a href="contact.php">Contact Us</a></li-->
                <li><a href="about.php">About</a></li>
                <li><a href="logout.php" class="btn-logout">Logout</a></li>
            </ul>
    </div>
</header>

<div class="container">
    <h2>Research Report</h2>
    <form method="get" action="">
        <div class="filters-container">
            <div class="form-group">
                <label for="program">Program</label>
                <select name="program" id="program">
                    <option value="">All</option>
                    <?php while ($row = $programsResult->fetch_assoc()) { ?>
                        <option value="<?= $row['program'] ?>" <?= $row['program'] === $selectedProgram ? 'selected' : '' ?>>
                            <?= $row['program'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="year">Year</label>
                <select name="year" id="year">
                    <option value="">All</option>
                    <?php while ($row = $yearsResult->fetch_assoc()) { ?>
                        <option value="<?= $row['year'] ?>" <?= $row['year'] === $selectedYear ? 'selected' : '' ?>>
                            <?= $row['year'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="adviser">Adviser</label>
                <select name="adviser" id="adviser">
                    <option value="">All</option>
                    <?php while ($row = $advisersResult->fetch_assoc()) { ?>
                        <option value="<?= $row['adviser'] ?>" <?= $row['adviser'] === $selectedAdviser ? 'selected' : '' ?>>
                            <?= $row['adviser'] ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <button type="submit">Generate</button>
        <button class="btn-print" onclick="printTable()">Print Report</button>
    </form>

    <div id="printableTable">
        <table class="table">
            <thead>
                <tr>
                    <th>Program</th>
                    <th>Title</th>
                    <th>Authors</th>
                    <th>Year</th>
                    <th>Adviser</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['program'] ?></td>
                        <td><?= $row['title'] ?></td>
                        <td><?= $row['authors'] ?></td>
                        <td><?= $row['year'] ?></td>
                        <td><?= $row['adviser'] ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    
</div>

</body>
</html>
