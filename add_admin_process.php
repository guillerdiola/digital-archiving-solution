<?php
session_start();

// Include the database connection file
require_once 'db.php'; // Ensure this path is correct

// Check if the form is submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the form data
    $user = trim($_POST['user']);  // Add this line
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $program = trim($_POST['program']);
    
    // Hash the password for security
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Validate input fields
    if (empty($user) || empty($email) || empty($password) || empty($program)) {
        $_SESSION['error'] = "All fields are required!";
        header("Location: add_admin.php"); // Redirect back to the form
        exit();
    } else {
        // Prepare the SQL query to insert the new admin
        $query = "INSERT INTO userss (user, email, password, program, role) VALUES (?, ?, ?, ?, 'admin')";
        $stmt = $conn->prepare($query);
        
        // Bind the parameters to the query
        $stmt->bind_param("ssss", $user, $email, $hashedPassword, $program); // Add 'user' here

        // Execute the query
        if ($stmt->execute()) {
            $_SESSION['success'] = "Coordinator added successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
    
    // Redirect to the form page after processing
    header("Location: add_admin.php");
    exit();
}

?>
