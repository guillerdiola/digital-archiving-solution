<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHMailer\Exception;

require 'vendor/autoload.php'; // Include Composer's autoloader

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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $verification_code = rand(100000, 999999); // Generate a 6-digit verification code

    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM userss WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $error_message = "Email already exists!";
    } else {
        // Insert user into the database with the verification code
        $stmt = $pdo->prepare("INSERT INTO userss (email, password, token) VALUES (?, ?, ?)");
        if ($stmt->execute([$email, $password, $verification_code])) {
            // Send verification email with the code
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'gdiola550@gmail.com'; // Your Gmail address
                $mail->Password = 'zntj wwez kvpz xzqg'; // Your Gmail App Password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587; // Use port 587 for TLS, 465 for SSL

                $mail->setFrom('gdiola550@gmail.com', 'Qsu Research');
                $mail->addAddress($email);
                $mail->Subject = 'Email Verification Code';
                $mail->isHTML(true);
                $mail->Body = "
                    <p>Your verification code is:</p>
                    <h2>$verification_code</h2>
                    <p>Please enter this code to complete your registration.</p>
                ";

                $mail->send();

                // Redirect to a verification page
                header("Location: verify.php?email=" . urlencode($email));
                exit();

            } catch (Exception $e) {
                $error_message = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error_message = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(to bottom right, #32a852, #1e7b34);
            font-family: 'Roboto', sans-serif;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('images/itbuilding.jpg') no-repeat center center;
            background-size: cover;
            filter: blur(5px);
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            z-index: -1;
        }

        .register-container {
            background: white;
            padding: 40px;
            border-radius: 40px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 100%;
            text-align: left;
            position: relative;
        }

        .register-container h2 {
            text-align: center;
            font-weight: 700;
            color: #1e7b34;
            margin-bottom: 30px;
        }

        .password-container {
            position: relative;
        }

        .password-container input {
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 10px 40px 10px 15px; /* Adjust padding for icon spacing */
            width: 100%;
        }

        .password-container .toggle-password {
            position: absolute;
            top: 70%;
            right: 15px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            font-size: 1.2rem; /* Resize the icon */
        }

        .register-container input[type="submit"], 
        .register-container .btn-login {
            background: linear-gradient(to right, #1e7b34, #32a852);
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 25px;
            padding: 10px;
            width: 100%;
            margin-top: 15px;
            font-weight: bold;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .register-container input[type="submit"]:hover, 
        .register-container .btn-login:hover {
            background: linear-gradient(to right, #32a852, #1e7b34);
            transform: translateY(-3px);
        }

        .register-container input[type="submit"]:active, 
        .register-container .btn-login:active {
            transform: translateY(0);
        }

        .register-container small {
            display: block;
            margin-top: 15px;
            color: #555;
        }

        @media (max-width: 576px) {
            .register-container {
                padding: 20px;
                max-width: 100%;
            }

            .register-container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2>Sign Up</h2>
        <?php if (isset($error_message)): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" name="email" id="email" class="form-control shadow-sm" placeholder="Enter your email" required>
            </div>
            <div class="mb-3 password-container">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" id="password" class="form-control shadow-sm" placeholder="Enter your password" required>
                <i class="fas fa-eye toggle-password" id="togglePassword"></i>
            </div>
            <input type="submit" value="Register">
        </form>
        <button onclick="window.location.href='index.php'" class="btn-login shadow-sm">Already have an account? Login</button>
        <!--small>By signing up, you agree to our <a href="#">Terms & Conditions</a></small-->
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.type === 'password' ? 'text' : 'password';
            passwordInput.type = type;
            togglePassword.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
