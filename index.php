<?php
session_start(); // Start the session

// Database configuration
$host = 'localhost';
$db = 'database';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Query the user data
    $stmt = $pdo->prepare("SELECT * FROM userss WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            // Ensure program is correctly fetched
            $program = !empty($user['program']) ? $user['program'] : ""; // Fallback to a default value if empty
            $role = $user['role']; // Get the role

            // Set session variables
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'program' => $program, // Program should be set here
                'role' => $role        // Role should be set here
            ];

            // Increment login count for the program
            $updateStmt = $pdo->prepare("UPDATE login_counts SET count = count + 1 WHERE program = ?");
            $updateStmt->execute([$program]);

            // Create welcome message
            $welcome_message = $role === 'admin'
                ? "Hello Welcome back, Master. The future is in your hands..."
                : "You have successfully logged in Welcome dear user. Explore the features below.";

            // Redirect with a welcome message
            header("Location: dashboard.php?message=" . urlencode($welcome_message));
            exit();
        } else {
            $error_message = "Incorrect password!";
        }
    } else {
        $error_message = "Email not found!";
    }
}

// Fetch login counts for display
$stmt = $pdo->prepare("SELECT program, count FROM login_counts ORDER BY count DESC");
$stmt->execute();
$loginCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - QSU Student Research</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">


    <style>
        /* Existing styles */
        body {
            background: url('images/.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            color: #333;
            position: relative;
            padding-top: 80px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f1f8f6; /* Light green background */
            border: 2px solid #c5e1a5; /* Slightly darker green border */
            color: #333;
        }
        body::before {
            content: "";
            position: absolute;
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
            background: linear-gradient(90deg, #ffffff, #a5d6a7); /* Light green gradient background */
            padding: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
            z-index: 1000; /* Ensure it stays on top of other elements */
        }

        header {
           display: flex;
           align-items: center;
           justify-content: space-between;
        }

        header div {
           display: flex;
           flex-direction: column;
        }

        header h2 {
           margin: 0;
           font-size: 1.8rem;
           color: #2e7d32;
           /*font-weight: bold;*/
           padding-left: 10px;
        }

        header h5 {
           margin: 0;
           font-size: 16px;
           color: black;
           font-style: italic;
           padding-left: 10px;
        }

        .navbar {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            flex: 2;
            justify-content: flex-end;
        }
        .navbar li {
            margin-left: 20px;
        }
        .navbar li a {
            color: #ffffff;
            text-decoration: none;
            padding: 8px 15px;
            background-color: #388e3c;
            border-radius: 5px;
        }
        .navbar li a:hover {
            background-color: #2e7d32;
        }
        .search-bar {
            display: flex;
            align-items: center;
            background-color: #ffffff;
            padding: 5px;
            border-radius: 5px;
            margin-right: 20px;
        }
        .search-bar input {
            border: none;
            outline: none;
            padding: 5px;
        }
        .search-bar button {
            background-color: transparent;
            border: none;
            cursor: pointer;
        }
        .login-btn {
            background-color: #388e3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 20px;
            margin-left: 65rem;
        }
        .login-btn:hover {
            background-color: #2e7d32;
        }
        .carousel {
  width: 80%; 
  margin: 0 auto; /* Center the carousel horizontally */
  padding-left: 5rem;
  padding-right: 5rem;
  padding-top: 10px;
}

.carousel img {
  width: 100%;
  height: 40rem;
  border-radius: 10px;
}

.carousel-caption {
  background-color: rgba(0, 0, 0, 0.5);
  padding: 10px;
  border-radius: 5px;
}
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 56px); /* Adjust height considering navbar height */
        }
        .login-form {
            background: #e8f5e9; /* Light green background for form */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background-color: #4caf50; /* Light green for button */
            border: none;
        }
        .btn-primary:hover {
            background-color: #388e3c; /* Darker green on hover */
        }
        .about-us {
            background-color: #e8f5e9; /* Light green background */
            padding: -1rem;
            text-align: center;
            font-family: 'Arial'; /* Use a serif font */
            margin-top: 20px; /* Reduced margin from the section above */
            border: 2px solid #c5e1a5; /* Slightly darker green border */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Subtle shadow effect */
        }

        .about-us h3 {
            font-size: 2rem;
            color: #2e7d32; /* Dark green color */
            margin-bottom: 1px;
        }
        .about-us p {
            font-size: 1rem;
            color: #666;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.6;
        }
        .card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.02);
            box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }

        .card-text {
            color: #666;
        }
        .news-section .card {
            margin-top: -30px;
            margin-bottom: -20px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        footer {
    background-color: #004f23;
    color: white;
    padding: 5px;
    text-align: center;
    margin-top: 0; /* No margin at the top */
        }
        /* Existing styles */
        .hidden {
        display: none;
        }
        /* Container for the bubbles */
.bubble-container {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 15px;
    padding-top: 20px;
}
/* Center the Analytics header */
h3 {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 30px;
            color: #333;
        }

/* Style for each bubble */
.bubble {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100px !important; /* Fixed size */
    height: 100px !important; /* Fixed size */
    border-radius: 50%;
    background-color: #3498db; /* Bubble color */
    color: white;
    font-weight: bold;
    text-align: center;
    font-size: 14px;
    transition: transform 0.3s ease; /* Smooth hover effect */
    cursor: pointer;
    position: relative;
}

/* Add hover effect */
.bubble:hover {
    transform: scale(1.1);
}

/* Bubble text */
.bubble-text {
    font-size: 12px;
}

/* Customize colors for each program */
.bubble[data-program="BSIT"] {
    background-color: #2e86c1;
}

.bubble[data-program="BSOA"] {
    background-color: #f39c12;
}

.bubble[data-program="CRIM"] {
    background-color: #e74c3c;
}

.bubble[data-program="Other"] {
    background-color: #8e44ad;
}

.bubble[data-program="BSCS"] {
    background-color: #27ae60;
}

.bubble[data-program="BSED"] {
    background-color: #8e44ad;
}

.bubble[data-program="BSBA"] {
    background-color: #16a085;
}

.bubble[data-program="BSHM"] {
    background-color: #e74c3c;
}

.bubble[data-program="BSN"] {
    background-color: #2980b9;
}

.bubble[data-program="BSCHE"] {
    background-color: #f1c40f;
}

/* Ensure no resizing based on dynamic attributes */
.bubble[data-count] {
    width: 100px !important;
    height: 100px !important;
}

/* Optional: Reset inline styles if dynamically applied */
.bubble {
    width: 100px !important;
    height: 100px !important;
}

/* Center the Analytics title */
h3 {
            text-align: center;
            margin-bottom: 40px;
        }

        /* Bubble container styling */
        .bubble-container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }

        /* Individual bubble styling */
        .bubble {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100px;
            height: 100px;
            background-color: #4CAF50;
            color: white;
            border-radius: 50%;
            font-size: 16px;
            text-align: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        /* Hover effect */
        .bubble:hover {
            transform: scale(1.1);
        }

        /* Center the select input container */
        .select-container {
            text-align: center;
            margin-top: 40px;
        }

        select {
            padding: 10px;
            font-size: 16px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        /* Styling for the eye icon */
.toggle-password {
    position: absolute;
    top: 73%;               /* Center it vertically inside the input */
    right: 10px;            /* Adjust the distance from the right */
    transform: translateY(-50%); /* Align the icon to be perfectly centered */
    cursor: pointer;       /* Change the cursor to a pointer when hovering */
}

/* Optional: Adjust the icon's size */
.toggle-password {
    font-size: 1.2em;  /* Increase or decrease icon size */
}

        
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const emailInput = document.getElementById('email');
            const programDiv = document.getElementById('program-div');

            if (emailInput && programDiv) {
                emailInput.addEventListener('input', function() {
                    console.log('Email input detected:', emailInput.value); // Debugging line
                    if (emailInput.value.trim() === 'a47226801@gmail.com') {
                        programDiv.classList.add('hidden');
                    } else {
                        programDiv.classList.remove('hidden');
                    }
                });
            }
        });
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const bubbles = document.querySelectorAll('.bubble');
        
        bubbles.forEach(bubble => {
            // Get the login count
            const count = parseInt(bubble.getAttribute('data-count'), 10);
            
            // Calculate the size of the bubble based on the count (you can tweak this factor)
            const size = 50 + (count * 20); // 50px + 20px per login
            
            // Set the width and height
            bubble.style.width = `${size}px`;
            bubble.style.height = `${size}px`;
        });
    });
</script>

</head>
<body>
    <header>
        <img src="images/qsu.png" alt="QSU Logo" style="height: 70px;">
        <div>
        <h2>QSU Research Archive</h2>
        <h5>Preserving Knowledge, Inspiring Discovery</h5>
        <!--h5>Innovating Minds, Shaping Futures</h5-->
        </div>
        <div class="navbar">
            <ul class="navbar">
                <!--li><a href="#">Contact Us</a></li-->
            </ul>
            <button type="button" class="login-btn" data-bs-toggle="modal" data-bs-target="#loginModal">
                Login
            </button>
        </div>
    </header>

    <!-- Carousel for images -->
    <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators"> 
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2" aria-label="Slide 3"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="3" aria-label="Slide 4"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="4" aria-label="Slide 5"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="5" aria-label="Slide 6"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="6" aria-label="Slide 7"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="7" aria-label="Slide 8"></button>
            <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="8" aria-label="Slide 9"></button>
            
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/itbuilding.jpg" class="d-block w-100" alt="Slide 1">
                
            </div>
            <div class="carousel-item">
                <img src="images/COPS.jpg" class="d-block w-100" alt="Slide 2">
            </div>
            <div class="carousel-item">
                <img src="images/BSABE.jpg" class="d-block w-100" alt="Slide 3">
            </div>
            <div class="carousel-item">
                <img src="images/BSED.jpg" class="d-block w-100" alt="Slide 4">
            </div>
            <div class="carousel-item">
                <img src="images/CHIM.jpg" class="d-block w-100" alt="Slide 5">
            </div>
            <div class="carousel-item">
                <img src="images/BSA.jpg" class="d-block w-100" alt="Slide 6">
            </div>
            <div class="carousel-item">
                <img src="images/BSND.jpg" class="d-block w-100" alt="Slide 7">
            </div>
            <!--div class="carousel-item">
                <img src="images/NUTRI.jpg" class="d-block w-100" alt="Slide 8">
                <div class="carousel-caption d-none d-md-block">
                    <h5>BSND Department</h5>
                </div>
            </div-->
            <div class="carousel-item">
                <img src="images/BSOA.jpg" class="d-block w-100" alt="Slide 8">
                <div class="carousel-caption d-none d-md-block">
                    <h5>BSOA Department</h5>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/library10.jpg" class="d-block w-100" alt="Slide 9">
                <div class="carousel-caption d-none d-md-block">
                    <h5>QSU knowledge Center</h5>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <!-- About Us Section -->
    <div class="about-us">
        <h3>About Us</h3>
        <p>Welcome to the Quirino State University Student Research portal. Our goal is to provide a comprehensive archive of student research projects, making it easier for you to access and contribute to the wealth of knowledge created by our students.</p>
    </div>


    <!-- Login Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header text-white" style="background-color: #28a745;">
                <h5 class="modal-title" id="loginModalLabel">Welcome</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($error_message)) : ?>
                    <div class="alert alert-danger"><?= $error_message; ?></div>
                <?php endif; ?>
                <form action="index.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label fw-bold">Email Address</label>
                        <input type="email" class="form-control rounded-pill shadow-sm" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label fw-bold">Password</label>
                        <input type="password" class="form-control rounded-pill shadow-sm" id="password" name="password" placeholder="Enter your password" required>
                        <!-- Eye toggle icon -->
                        <i class="fas fa-eye toggle-password" id="togglePassword"></i>

                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary rounded-pill shadow-sm">Login</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer justify-content-center">
                <p class="text-muted">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register</a></p>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome CDN -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

<script>
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');

    togglePassword.addEventListener('click', () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePassword.classList.toggle('fa-eye-slash');
    });
</script>


    <footer>
        <p>&copy; 2024 Quirino State University. All rights reserved.</p>
    </footer>

    <!-- Optional JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
