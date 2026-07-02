<?php
// download.php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = 'path/to/your/research/files/' . basename($file); // Adjust path

    if (file_exists($filePath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    } else {
        echo "File not found.";
    }
} else {
    echo "No file specified.";
}
?>
