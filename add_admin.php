<?php
session_start();

// Include the database connection file
require_once 'db.php'; // Ensure this path is correct

// Check for any session messages
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Toggle the visibility of users when the button is clicked
if (isset($_POST['view_users'])) {
    if (!isset($_SESSION['view_users'])) {
        $_SESSION['view_users'] = true; // First time click, set to true
    } else {
        $_SESSION['view_users'] = !$_SESSION['view_users']; // Toggle visibility
    }
}

// Fetch users if the session is set to show users
if (isset($_SESSION['view_users']) && $_SESSION['view_users'] === true) {
    $sql = "SELECT * FROM userss";
    $result = $conn->query($sql);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Admin</title>
    <link rel="stylesheet" href="path/to/your/styles.css"> <!-- Ensure to include your CSS -->
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        header {
            background-color: #009688;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .menu {
            background-color: #333;
            overflow: hidden;
        }

        .menu ul {
            list-style-type: none;
            margin: 0;
            padding: 0;
        }

        .menu ul li {
            display: inline;
            margin-right: 20px;
        }

        .menu ul li a {
            color: white;
            text-decoration: none;
            padding: 10px;
        }

        .menu ul li a:hover {
            background-color: #575757;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        form {
            max-width: 600px;
            margin: 0 auto;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #009688;
            color: white;
            border: none;
            border-radius: 4px;
            margin-top: 20px;
            cursor: pointer;
        }

        button:hover {
            background-color: #00796b;
        }

        .alert {
            text-align: center;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }

        .alert.error {
            background-color: #f44336;
            color: white;
        }

        .alert.success {
            background-color: #4caf50;
            color: white;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .action-links {
            margin-top: 10px;
        }
        /* Table Styling */
table {
    width: 100%;
    margin-top: 20px;
    border-collapse: collapse;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #009688;
    color: white;
    font-size: 16px;
    text-transform: uppercase;
}

td {
    font-size: 14px;
    color: #333;
}

tr:hover {
    background-color: #f9f9f9;
}

tr:last-child td {
    border-bottom: none;
}

/* Action Links Styling */
.action-links {
    display: flex;
    justify-content: space-around;
    align-items: center;
}

.action-links a {
    padding: 8px 12px;
    color: #fff;
    text-decoration: none;
    background-color: #009688;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.action-links a:hover {
    background-color: #00796b;
}

/* Responsiveness */
@media (max-width: 768px) {
    table {
        font-size: 12px;
    }

    th, td {
        padding: 8px 10px;
    }

    .action-links a {
        font-size: 12px;
    }
}
/* Custom styling for select (dropdown) to match input fields */
select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

select:focus {
    border-color: #009688; /* Match button color */
    outline: none;
    box-shadow: 0 0 5px rgba(0, 150, 136, 0.5); /* Highlight when focused */
}

button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #009688;
    color: white;
    border: none;
    border-radius: 4px;
    margin-top: 20px;
    cursor: pointer;
}

button:hover {
    background-color: #00796b;
}


</style>
</head>
<body>

    <!-- Header and Menu -->
    <header>
        <h1>Quirino State University Research Archive</h1>
        <p>Manage Coordinators</p>
    </header>

    <div class="menu">
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <!--li><a href="mstrlist.php">View Masterlist</a></li-->
            <!--li><a href="upload.php">Upload Research</a></li-->
            <!--li><a href="whatsnew.php">What's New</a></li-->
            <!--li><a href="support.php">Help & Support</a></li-->
            <!--li><a href="about.php">About</a></li-->
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Section -->
    <div class="container">
        <h2>Add Research Coordinator</h2>

        <!-- Display messages -->
        <?php
        if (isset($error_message)) {
            echo "<div class='alert error'>" . $error_message . "</div>";
        }

        if (isset($success_message)) {
            echo "<div class='alert success'>" . $success_message . "</div>";
        }
        ?>

        <!-- Add Admin Form -->
        <form action="add_admin_process.php" method="POST">
    <label for="user">Complete Name:</label>
    <input type="text" name="user" id="user" required><br><br>

    <label for="email">Email:</label>
    <input type="email" name="email" id="email" required><br><br>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required><br><br>

    <!--label for="program">Program:</label>
    <select name="program" id="program" required>
        <option value="">Select Program</option>
        <option value="BSIT">BSIT</option>
        <option value="BSOA">BSOA</option>
        <option value="BSHM">BSHM</option>
        <option value="CRIM">CRIM</option>
        <option value="BSCS">BSCS</option>
        <option value="BSED">BSED</option>
        <option value="BSBA">BSBA</option>
        <option value="BSN">BSN</option>
        <option value="BSED">BSCHE</option>
        <option value="BSED">BSME</option>
        <option value="BSED">BSEE</option>
        <option value="BSED">BSA</option>
        <option value="BSED">BSCE</option>
        <option value="BSED">BSIS</option>
        <option value="BSED">BSPS</option>
        <option value="BSED">BSAG</option>
        <option value="BSED">BSP</option>
        <option value="BSED">BSFA</option>
        <option value="BSED">BTLED</option-->
        <!-- Add more programs as needed -->
    <!--/select><br><br-->
    <label for="program">Program:</label>
            <input type="text" name="program" id="program" required><br><br>

    <button type="submit">Add Coordinator</button>
</form>

        <!-- View All Users Button -->
        <div class="action-links">
            <form action="add_admin.php" method="POST">
                <button type="submit" name="view_users">
                    <?php echo isset($_SESSION['view_users']) && $_SESSION['view_users'] ? 'Hide All Users' : 'View All Users'; ?>
                </button>
            </form>
        </div>

       <!-- Display all users in a table if session variable view_users is true -->
<?php if (isset($_SESSION['view_users']) && $_SESSION['view_users'] === true && isset($result) && $result->num_rows > 0) { ?>
    <h3>Existing Users</h3>
    <table>
        <tr>
            <th>Names</th> <!-- Added User Column -->
            <th>Email</th>
            <th>Program</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        <?php
        while($row = $result->fetch_assoc()) {
            echo "<tr><td>" . $row["user"] . "</td>"; // Display the User
            echo "<td>" . $row["email"] . "</td>";
            echo "<td>" . $row["program"] . "</td>";
            echo "<td>" . $row["role"] . "</td>";
            
            // Check if the email is the admin's email
            if ($row["email"] == 'a47226801@gmail.com') {
                echo "<td>Admin</td>"; // Hide Edit/Delete for this email
            } else {
                echo "<td><a href='edit_user.php?id=" . $row["id"] . "'>Edit</a> | <a href='delete_user.php?id=" . $row["id"] . "'>Delete</a></td>";
            }
            
            echo "</tr>";
        }
        echo "</table>";
        ?>
<?php } elseif (isset($_SESSION['view_users']) && $_SESSION['view_users'] === true) { ?>
    <p>No users found.</p>
<?php } ?>
