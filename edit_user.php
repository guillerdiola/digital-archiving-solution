<?php
session_start();

// Include the database connection file
require_once 'db.php';

// Check if the user ID is passed
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Fetch the user's details from the database
    $sql = "SELECT * FROM userss WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the user data
        $user = $result->fetch_assoc();
    } else {
        $_SESSION['error'] = "User not found!";
        header("Location: add_admin.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Invalid user ID!";
    header("Location: add_admin.php");
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated data
    $email = trim($_POST['email']);
    $program = trim($_POST['program']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate input
    if (empty($email) || empty($program)) {
        $_SESSION['error'] = "All fields are required!";
    } else {
        // Password change logic
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $_SESSION['error'] = "Passwords do not match!";
            } else {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Update user data with the new password
                $sql = "UPDATE userss SET email = ?, program = ?, password = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $email, $program, $hashed_password, $user_id);
            }
        } else {
            // Update user data without changing the password
            $sql = "UPDATE userss SET email = ?, program = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $email, $program, $user_id);
        }

        if ($stmt->execute()) {
            $_SESSION['success'] = "User updated successfully!";
            header("Location: add_admin.php");
            exit();
        } else {
            $_SESSION['error'] = "Error updating user: " . $stmt->error;
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="path/to/your/styles.css">
</head>
<style>
    /* General Body Styling */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f9f9f9;
    margin: 0;
    padding: 0;
}

/* Header Styling */
header {
    background-color: #009688;
    color: white;
    padding: 20px;
    text-align: center;
}

header h1 {
    font-size: 2em;
    margin: 0;
}

/* Container Styling */
.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Form Styling */
form {
    max-width: 600px;
    margin: 0 auto;
}

label {
    font-size: 1.1em;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

input {
    width: 100%;
    padding: 12px;
    font-size: 1em;
    border: 1px solid #ccc;
    border-radius: 4px;
    margin-bottom: 20px;
    background-color: #f9f9f9;
}

input:focus {
    border-color: #009688;
    outline: none;
    background-color: #fff;
}

/* Button Styling */
button {
    display: inline-block;
    width: 100%;
    padding: 12px;
    background-color: #009688;
    color: white;
    font-size: 1.1em;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

button:hover {
    background-color: #00796b;
}

/* Alert Styling */
.alert {
    text-align: center;
    padding: 10px;
    margin: 20px 0;
    border-radius: 4px;
    font-weight: bold;
}

.alert.error {
    background-color: #f44336;
    color: white;
}

.alert.success {
    background-color: #4caf50;
    color: white;
}

/* Back Link Styling */
a {
    display: inline-block;
    margin-top: 20px;
    padding: 10px 20px;
    background-color: #009688;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 1em;
    transition: background-color 0.3s ease;
}

a:hover {
    background-color: #00796b;
}

/* Media Query for Smaller Screens */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    form {
        max-width: 100%;
    }

    button, a {
        width: 100%;
    }

    label, input {
        font-size: 1em;
    }
}
</style>
<body>
    <header>
        <h1>Edit User</h1>
    </header>

    <div class="container">
        <!-- Display messages -->
        <?php
        if (isset($_SESSION['error'])) {
            echo "<div class='alert error'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo "<div class='alert success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']);
        }
        ?>

        <!-- Edit User Form -->
        <form action="edit_user.php?id=<?php echo $user['id']; ?>" method="POST">
    <label for="email">Email:</label>
    <input type="email" name="email" id="email" value="<?php echo $user['email']; ?>" required><br><br>

    <label for="program">Program:</label>
    <input type="text" name="program" id="program" value="<?php echo $user['program']; ?>" required><br><br>

    <!-- Password Change Section -->
    <label for="new_password">New Password:</label>
    <input type="password" name="new_password" id="new_password"><br><br>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password"><br><br>

    <button type="submit">Update User</button>
</form>


        <a href="add_admin.php">Back</a>
    </div>
</body>
</html>
