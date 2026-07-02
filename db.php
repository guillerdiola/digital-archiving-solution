<?php
// db.php - Database connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname_users = "database"; // Database for users
$dbname_research = "research"; // Database for research
$dbname_add = 'database'; // Change to your database name

// Create a new database connection using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname_add); // Use $servername instead of $host

// Check for a connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create connection for users
$conn_users = new mysqli($servername, $username, $password, $dbname_users);
if ($conn_users->connect_error) {
    die("Connection failed: " . $conn_users->connect_error);
}

// Create connection for research files
$conn_research = new mysqli($servername, $username, $password, $dbname_research);
if ($conn_research->connect_error) {
    die("Connection failed: " . $conn_research->connect_error);
}
?>
