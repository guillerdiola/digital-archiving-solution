<?php
session_start();

// Check if the user session is set
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the file ID is provided
if (!isset($_GET['id'])) {
    echo "No file selected for editing.";
    exit();
}

$fileId = $_GET['id'];

// Fetch the file details
$sql = "SELECT * FROM research_file WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fileId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "File not found.";
    exit();
}

$file = $result->fetch_assoc();

// Handle form submission for updating the file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newTitle = $_POST['title'];
    $newProgram = $_POST['program'];
    $newAbstract = $_POST['abstract']; // Get the abstract from the form
    $newAdviser = $_POST['adviser']; // Get the adviser from the form

    $updateQuery = "UPDATE research_file SET title = ?, program = ?, abstract = ?, adviser = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ssssi", $newTitle, $newProgram, $newAbstract, $newAdviser, $fileId);

    if ($updateStmt->execute()) {
        echo "<script>alert('File details updated successfully!');</script>";
        header("Location: uploaded.php"); // Redirect back to the file list
        exit();
    } else {
        echo "Error updating file: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Edit File</title>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit File</h2>
        <form method="POST">
            <div class="mb-3">
                <label for="title" class="form-label">File Name</label>
                <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($file['title']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="program" class="form-label">Program</label>
                <input type="text" class="form-control" id="program" name="program" value="<?php echo htmlspecialchars($file['program']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="abstract" class="form-label">Abstract</label>
                <textarea class="form-control" id="abstract" name="abstract" rows="5" required><?php echo htmlspecialchars($file['abstract']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="adviser" class="form-label">Adviser</label>
                <input type="text" class="form-control" id="adviser" name="adviser" value="<?php echo htmlspecialchars($file['adviser']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="uploaded.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
