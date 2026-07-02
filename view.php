<?php
// Database connection (adjust as needed)
include('db_connection.php');

// Check if the 'id' is provided in the URL
if (isset($_GET['id'])) {
    $researchId = $_GET['id'];

    // Query the database to fetch the file details using the provided ID
    $sql = "SELECT * FROM documents WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $researchId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the file details
        $row = $result->fetch_assoc();
        $filePath = $row['file_path']; // assuming the file path is stored in this column

        // Check if the file exists
        if (file_exists($filePath)) {
            // Display the file based on its type (for example, PDF, image, etc.)
            $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);

            if ($fileExt == 'pdf') {
                // Display PDF in the browser
                header('Content-Type: application/pdf');
                readfile($filePath);
            } elseif (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                // Display image files
                header('Content-Type: image/' . $fileExt);
                readfile($filePath);
            } else {
                // For other file types, you might want to give a download option
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
                readfile($filePath);
            }
        } else {
            echo "File not found.";
        }
    } else {
        echo "No research found for the given ID.";
    }
} else {
    echo "No ID provided.";
}
?>
