<?php
session_start();

// Check if the user session is set
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Retrieve user details from session
$loggedInEmail = $_SESSION['user']['email'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch files uploaded by the logged-in user
$sql = "SELECT * FROM research_file WHERE uploaded_by = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $loggedInEmail); // Filter by logged-in user's email
$stmt->execute();
$result = $stmt->get_result();

// Handle file actions (delete, etc.)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $fileId = $_POST['file_id'];

        // Fetch the file path before deletion
        $filePathQuery = "SELECT file_path FROM research_file WHERE id = ?";
        $filePathStmt = $conn->prepare($filePathQuery);
        $filePathStmt->bind_param("i", $fileId);
        $filePathStmt->execute();
        $filePathResult = $filePathStmt->get_result();

        if ($filePathResult->num_rows > 0) {
            $fileRow = $filePathResult->fetch_assoc();
            $filePath = $fileRow['file_path'];

            // Delete the file from the database
            $deleteQuery = "DELETE FROM research_file WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $fileId);
            $deleteStmt->execute();

            // Delete the file from the server
            if ($deleteStmt->affected_rows > 0) {
                if (file_exists($filePath)) {
                    unlink($filePath); // Remove the file from the server
                }
                echo "<script>alert('File deleted successfully!');</script>";
            }
        }
    }
}
$loggedInRole = $_SESSION['user']['role'];
$loggedInProgram = $_SESSION['user']['program']; // Program associated with the user
$loggedInEmail = $_SESSION['user']['email']; // Email of the logged-in user
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Your Uploaded Files</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7fc;
        }

        .container {
            margin-top: 50px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            max-width: 1100px;
            margin-left: auto;
            margin-right: auto;
        }

        h2 {
            font-size: 28px;
            color: #4e4e4e;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .table {
            font-size: 16px;
            border-collapse: collapse;
        }

        .table th, .table td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #ddd;
        }

        .table thead {
            background-color: #6f42c1;
            color: white;
        }

        .table td a {
            margin-right: 10px;
        }

        .btn {
            font-size: 14px;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background-color: #0069d9;
        }

        .btn-warning {
            background-color: #ffc107;
            color: white;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .alert {
            display: none;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }

        .alert-error {
            background-color: #dc3545;
            color: white;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
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

<div class="container">
<a href="dashboard.php" class="back-button">Back to Dashboard</a>
    <div class="container">
        <p><strong>Logged in as:</strong> <?php echo htmlspecialchars($loggedInEmail); ?></p>
        <h2>Your Uploaded Files</h2>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="alert alert-success">File deleted successfully!</div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Program</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['program']); ?></td>
                            <td>
                                <a href="upload.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Add</a>
                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" target="_blank" class="btn btn-success btn-sm">View</a>
                                <a href="<?php echo htmlspecialchars($row['file_path']); ?>" class="btn btn-primary btn-sm" download>Download</a>
                                <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>

                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="file_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">No files uploaded.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        // Show alert for file deletion success
        <?php if (isset($_GET['deleted'])): ?>
            document.querySelector('.alert').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>
