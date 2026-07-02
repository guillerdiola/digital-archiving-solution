<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Define database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's details
$loggedInRole = $_SESSION['user']['role'];
$loggedInProgram = $_SESSION['user']['program'];
$loggedInEmail = $_SESSION['user']['email'];

// Predefined list of programs
$programs = ["BSIT", "BSOA", "CRIM", "BSHM", "BSEd", "BSBA", "BSN", "BSCS", "BSCHE"];

// Process the form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') { 
    if ($loggedInRole !== 'admin') {
        $error = "You do not have permission to upload research.";
    } else {
        // Get form inputs and sanitize
        $title = trim($_POST['title']);
        $authors = trim($_POST['authors']);
        $year = trim($_POST['year']);
        $programs = trim($_POST['program']);  // Ensure this matches form name
        $abstract = trim($_POST['abstract']);
        $adviser = trim($_POST['adviser']);
        $researchFile = $_FILES['research_file'];

        // Validate inputs
        if (empty($title) || empty($authors) || empty($year) || empty($programs) || empty($abstract) || empty($adviser) || empty($researchFile['name'])) {
            $error = "All fields are required.";
        } else {
            // Process the file upload
            $uploadDir = './uploads/';
            $uploadFile = $uploadDir . basename($researchFile['name']);

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($researchFile['tmp_name'], $uploadFile)) {
                // Prepare and bind
                $stmt = $conn->prepare("INSERT INTO research_file (title, authors, year, program, file_path, abstract, adviser, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssisssss", $title, $authors, $year, $programs, $uploadFile, $abstract, $adviser, $loggedInEmail);
                    if ($stmt->execute()) {
                        $success = "Research uploaded successfully.";
                    } else {
                        $error = "Error saving details to the database: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $error = "Error preparing SQL statement: " . $conn->error;
                }
            } else {
                $error = "Failed to upload file.";
            }
        }
    }
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Upload Research</title>
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
           filter: blur(8px); /* Adjust the blur intensity */
           z-index: -2; /* Place it behind the overlay */
        }

        body::after {
           content: "";
           position: fixed;
           top: 0;
           left: 0;
           width: 100%;
           height: 100%;
           background: rgba(255, 255, 255, 0.4); /* Light white overlay */
           z-index: -1; /* Place it above the blurred background */
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.7rem;
            font-size: 1rem;
            border-radius: 5px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: border-color 0.3s;
        }
        .form-group input[type="file"] {
            border: none;
        }
        .form-group textarea {
            resize: vertical;
        }
        .form-group button {
            background-color: #388e3c;
            color: #fff;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .form-group button:hover {
            background-color: #2e7d32;
        }
        .message {
            margin: 1rem 0;
            padding: 0.5rem;
            border-radius: 5px;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
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
                <li><a href="reports.php">Reports</a></li>
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
</head>
<body>
<div class="container">
    <h2>Upload New Research</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="authors">Authors</label>
            <input type="text" id="authors" name="authors" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="adviser">Adviser</label>
            <input type="text" id="adviser" name="adviser" class="form-control" required placeholder="Enter adviser name">
        </div>

        <div class="form-group">
            <label for="year">Year</label>
            <input type="text" name="year" id="year" class="form-control" required oninput="validateYear()">
        </div>

        <script>
            function validateYear() {
                var yearInput = document.getElementById("year");
                var yearValue = yearInput.value;

                if (!/^\d+$/.test(yearValue)) {
                    yearInput.setCustomValidity("Please enter a valid number.");
                } else {
                    var currentYear = new Date().getFullYear();
                    if (yearValue < 2020 || yearValue > currentYear) {
                        yearInput.setCustomValidity("Please enter a year between 2020 and " + currentYear + ".");
                    } else {
                        yearInput.setCustomValidity("");
                    }
                }
            }
        </script>

        <div class="form-group">
            <label for="program">Program</label>
            <select id="program" name="program" class="form-control" required>
                <?php
                if ($loggedInRole === 'admin') {
                    echo "<option value='$loggedInProgram'>$loggedInProgram</option>";
                } else {
                    echo "<option value='' disabled selected>Select your program</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="abstract">Abstract/Project Context</label>
            <textarea id="abstract" name="abstract" rows="5" maxlength="500" oninput="updateCharCount()" required></textarea>
            <small id="charCount">500 characters remaining</small>
        </div>

        <script>
            function updateCharCount() {
                const textarea = document.getElementById("abstract");
                const charCount = document.getElementById("charCount");
                const remaining = 500 - textarea.value.length;
                charCount.textContent = `${remaining} characters remaining`;
            }
        </script>

        <div class="form-group">
            <label for="research_file">Upload PDF File</label>
            <input type="file" name="research_file" id="research_file" accept=".pdf" class="form-control" required>
            <?php
            if (isset($error) && strpos($error, 'Only PDF files are allowed.') !== false) {
                echo '<small class="text-danger">Only PDF files are allowed.</small>';
            }
            ?>
        </div>

        <button type="submit" class="btn btn-primary mt-3">Upload Research</button>
    </form>
</div>
