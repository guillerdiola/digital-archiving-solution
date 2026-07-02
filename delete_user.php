<?php
session_start();

// Include the database connection file
require_once 'db.php';

// Check if the user ID is passed
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete the user from the database
    $sql = "DELETE FROM userss WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "User deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting user: " . $stmt->error;
    }

    // Redirect back to the masterlist
    header("Location: add_admin.php");
    exit();
} else {
    $_SESSION['error'] = "Invalid user ID!";
    header("Location: add_admin.php");
    exit();
}
?>
