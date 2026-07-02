<?php
session_start();

// Check if the user session is set
if (isset($_SESSION['user'])) {
    $loggedInProgram = $_SESSION['user']['program']; // Fetch program from session
    $userRole = $_SESSION['user']['role']; // Fetch role if needed
} else {
    // Redirect to login if the session is not set
    header("Location: index.php");
    exit();
}

// Get the logged-in user's information
$loggedInProgram = $_SESSION['user']['program'];
$userRole = $_SESSION['user']['role']; // Get the user role from session
$loggedInEmail = $_SESSION['user']['email']; // Fetch email from session

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "research";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the selected program, storing it in the session
if (isset($_POST['program'])) {
    $selected_program = $_POST['program'];
    $_SESSION['selected_program'] = $selected_program;
} else {
    $selected_program = isset($_SESSION['selected_program']) ? $_SESSION['selected_program'] : $loggedInProgram;
}

// Pagination logic
$filesPerPage = 5; // Number of files per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from URL, default to 1
$offset = ($page - 1) * $filesPerPage; // Calculate the offset for the SQL query

// Ensure the SQL query selects files for the selected program
$sql = "SELECT * FROM research_file";
if ($selected_program) {
    $sql .= " WHERE program = '" . $conn->real_escape_string($selected_program) . "'";
}
$sql .= " LIMIT $offset, $filesPerPage"; // Add pagination to SQL query
$result = $conn->query($sql);

// Get the total number of files to calculate total pages
$totalFilesSql = "SELECT COUNT(*) FROM research_file";
if ($selected_program) {
    $totalFilesSql .= " WHERE program = '" . $conn->real_escape_string($selected_program) . "'";
}
$totalFilesResult = $conn->query($totalFilesSql);
$totalFiles = $totalFilesResult->fetch_row()[0];
$totalPages = ceil($totalFiles / $filesPerPage); // Calculate total pages

// Determine the welcome message based on the user role
if ($userRole === 'admin') {
    $welcome_message = "Hello Welcome back, Master. The future is in your hands...";
} elseif ($userRole === 'user') {
    $welcome_message = "You have successfully logged in, Welcome dear user, Explore the features below.";
} else {
    $welcome_message = "Welcome to the QSU Research Archive.";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Research Archive Dashboard</title>
    <style>
        body {
           position: relative;
           margin: 0;
           padding-top: 70px; /* Padding for fixed header */
           font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
 
        body::before {
           content: "";
           position: fixed;
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
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000; /* Ensure it stays on top of other elements */
        }
        header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #2e7d32;
            padding-right: 67.5rem;
        }
        .menu-button {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 30px;
            height: 21px;
            background: transparent;
            border: none;
            cursor: pointer;
            z-index: 10;
            position: relative;
            padding-right: 3rem;
        }
        .menu-button .bar {
            width: 30px;
            height: 3px;
            background-color: #388e3c;
            transition: all 0.3s;
        }
        .menu-button.open .bar:nth-child(1) {
            transform: rotate(45deg);
            position: relative;
        }
        .menu-button.open .bar:nth-child(2) {
            opacity: 0;
        }
        .menu-button.open .bar:nth-child(3) {
            transform: rotate(-45deg);
            position: relative;
        }
        .menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: #ffffff;
            color: #333;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            overflow: hidden;
            width: 200px;
            display: none;
            transition: opacity 0.3s, transform 0.3s;
        }
        .menu.show {
            display: block;
            transform: translateY(10px);
            opacity: 1;
        }
        .menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu ul li {
            padding: 0.8rem 1.2rem;
        }
        .menu ul li a {
            text-decoration: none;
            color: #333;
            display: block;
            transition: background-color 0.3s, color 0.3s;
        }
        .menu ul li a:hover {
            background-color: #a5d6a7;
            color: #fff;
        }
        h2 {
            color: #333;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        header h2 {
            margin: 0;
            font-size: 1.8rem;
            color: #2e7d32;
            padding-right: 950px;
        }

        header h5 {
           margin: 0;
           font-size: 16px;
           color: black;
           font-style: italic;
           padding-left: 1px;
        }
      /* Container */
.container {
    width: 90%;
    max-width: 1200px;
    margin: 2rem auto;
    background: #fff;
    padding: 2rem;
    border-radius: 15px; /* Slightly rounded corners */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1); /* Softer shadow */
    opacity: 0; /* Initially hidden for fade-in effect */
    transform: scale(0.98); /* Slightly scaled down for scale effect */
    animation: fadeInUp 0.6s ease-out forwards; /* Animation */
}

/* Animation for Container */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Headings */
h2 {
    color: #333;
    font-size: 2.2rem; /* Larger font size for emphasis */
    margin-bottom: 1rem;
    /*opacity: 0; /* Initially hidden for slide-in effect */
    /*transform: translateY(-20px); /* Start from above */
    /*animation: slideIn 0.6s ease-out forwards; /* Animation */
}

/* Animation for Headings */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Buttons */
.btn-logout {
    background-color: #e53935; /* Red color */
    color: #fff;
    border: none;
    padding: 0.8rem 1.5rem; /* Increased padding for better touch */
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s, transform 0.3s; /* Added transform transition */
}

.btn-logout:hover {
    background-color: #c62828; /* Darker red on hover */
    transform: scale(1.05); /* Slightly scale up */
}

/* Filter Section */
.filter-section {
    margin-bottom: 20px;
    padding: 1rem;
    background: #fff; /* Background for better contrast */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    opacity: 0; /* Initially hidden for slide-up effect */
    transform: translateY(20px); /* Start from below */
    animation: slideUp 0.6s ease-out forwards; /* Animation */
}

/* Animation for Filter Section */
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Reverted Dropdown Design */
.filter-section select {
    padding: 0.7rem;
    font-size: 1rem;
    width: 100%;
    border-radius: 5px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease; /* Smooth transitions */
}

/* Animation for Focus Effect */
.filter-section select:focus {
    border-color: #a5d6a7; /* Highlight border color */
    box-shadow: 0 0 8px rgba(165, 214, 167, 0.5); /* Glow effect */
    transform: scale(1.02); /* Slightly enlarge */
}

/* Smooth Dropdown Appearance */
.filter-section select {
    transition: border-color 0.3s ease, box-shadow 0.3s ease, transform 0.3s ease; /* Ensure transitions are smooth */
}


/* Research Items */
.research-item {
    padding: 1rem;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    background-color: #fafafa;
    border-radius: 8px; /* Slightly rounded corners */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Added transition for hover effect */
}

/* Animation for Research Items on Hover */
.research-item:hover {
    transform: scale(1.05); /* Slightly scale up */
    box-shadow: 0 0 15px rgba(255, 223, 0, 0.5); /* Glowing effect with yellow light */
}

/* Research Links */
.research-links a {
    text-decoration: none;
    color: #007bff;
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    transition: color 0.3s, transform 0.3s; /* Added transform transition */
    cursor: pointer;
}

.research-links a:hover {
    color: #0056b3;
    transform: translateX(5px); /* Slide effect on hover */
}

/* Footer */
footer {
   background-color: #004f23;
   color: white;
   padding: 5px;
   text-align: center;
   position: relative; /* Make it normal in document flow */
   width: 100%; /* Ensure it spans the full width of the page */
   margin-top: 20px; /* Place it outside the container */
}
/* Container for the search input and button */
.search-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px; /* Adds spacing between input and button */
}

#search {
    width: 100%;
    padding: 0.8rem;
    font-size: 1rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    transition: border-color 0.3s, box-shadow 0.3s ease, transform 0.3s ease;
}

#search:focus {
    border-color: #a5d6a7;
    box-shadow: 0 0 8px rgba(165, 214, 167, 0.5);
    transform: scale(1.02);
}

.search-btn {
    background-color: #4caf50; /* Green color */
    color: #fff;
    padding: 0.8rem 1.5rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    transition: background-color 0.3s, transform 0.3s;
}

.search-btn:hover {
    background-color: #388e3c;
    transform: scale(1.05);
}
.abstract-container {
  position: relative;
  max-width: 400px; /* Adjust based on your design */
  word-wrap: break-word;
}

.abstract-text {
  display: inline;
}

.full-abstract {
  display: none; /* Hidden by default */
}

.toggle-abstract-btn {
  background: none;
  border: none;
  color: #007bff;
  cursor: pointer;
  font-size: 14px;
  text-decoration: underline;
  margin-left: 5px;
}

.toggle-abstract-btn:hover {
  color: #0056b3;
}


        
</style>
    <script>
        window.onload = function() {
            // Restore selected program from session if available
            var selectedProgram = "<?php echo $selected_program; ?>";
            if (selectedProgram) {
                document.getElementById('program').value = selectedProgram;
            }
        };

        function persistProgram() {
            var selectedProgram = document.getElementById('program').value;
            // Persist selected program to session
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.send("program=" + selectedProgram);
        }

        function toggleMenu() {
            var menu = document.getElementById('menu');
            var button = document.querySelector('.menu-button');
            menu.classList.toggle('show');
            button.classList.toggle('open');
        }
    </script>
</head>
<body>
    <header>
        <img src="images/qsu.png" alt="QSU Logo" style="height: 70px;">
        <div>
        <h2>Quirino State University Research</h2>
        <h5>Preserving Knowledge, Inspiring Discovery</h5>
        <!--h5>Innovating Minds, Shaping Futures</h5-->
        </div>
        <button class="menu-button" onclick="toggleMenu()">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>
        <div id="menu" class="menu">
        <ul>
            <li><a href="mstrlist.php">View Masterlist</a></li>
            <?php if ($userRole === 'admin'): ?>
                <li><a href="upload.php">Upload Research</a></li>
                <li><a href="uploaded.php">Your Uploads</a></li>
                <li><a href="reports.php">Reports</a></li>
                <li>
    <a href="<?php echo strtolower($loggedInProgram); ?>_dashboard.php">
        View Dashboard
    </a>
</li>
                <?php if ($loggedInEmail === 'a47226801@gmail.com'): ?>
                <!--li><a href="admin_dashboard.php">Your Dashboard</a></li-->
                    <li><a href="Add_admin.php">Add Coordinator Account</a></li> 
                <?php endif; ?>
            <?php endif; ?>
            <li><a href="whatsnew.php">What's New</a></li>
            <li><a href="support.php">Help & Support</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="logout.php" class="btn-logout">Logout</a></li>
        </ul>
    </div>

    

    </header>
    <div class="container">
        <h2>Welcome to the QSU Student Researches!</h2>
        <p><?php echo htmlspecialchars($welcome_message); ?></p>

        <!-- Combo-box for selecting program -->
        <div class="filter-section">
        <form method="post">
    <label for="program">Select Program:</label>
    <select name="program" id="program" onchange="this.form.submit()">
        <!-- If the user is CRIM admin, only show CRIM option -->
        <?php if ($userRole === 'admin' && $loggedInProgram === 'CCJE'): ?>
            <option value="CCJE" <?php if ($selected_program === 'CCJE') echo 'selected'; ?>>CCJE</option>
        <?php else: ?>
        <?php if ($userRole === 'admin' && $loggedInProgram === 'BSIT'): ?>
            <option value="BSIT" <?php if ($selected_program === 'BSIT') echo 'selected'; ?>>BSIT</option>
        <?php else: ?>
        <?php if ($userRole === 'admin' && $loggedInProgram === 'BSOA'): ?>
            <option value="BSOA" <?php if ($selected_program === 'BSOA') echo 'selected'; ?>>BSOA</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSHM'): ?>
            <option value="BSHM" <?php if ($selected_program === 'BSOA') echo 'selected'; ?>>BSHM</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSED'): ?>
            <option value="BSED" <?php if ($selected_program === 'BSED') echo 'selected'; ?>>BSED</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSABE'): ?>
            <option value="BSABE" <?php if ($selected_program === 'BSABE') echo 'selected'; ?>>BSABE</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSND'): ?>
            <option value="BSND" <?php if ($selected_program === 'BSND') echo 'selected'; ?>>BSND</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSA'): ?>
            <option value="BSA" <?php if ($selected_program === 'BSA') echo 'selected'; ?>>BSA</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BTLED'): ?>
            <option value="BTLED" <?php if ($selected_program === 'BTLED') echo 'selected'; ?>>BTLED</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSTM'): ?>
            <option value="BSTM" <?php if ($selected_program === 'BSTM') echo 'selected'; ?>>BSTM</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BEED'): ?>
            <option value="BEED" <?php if ($selected_program === 'BEED') echo 'selected'; ?>>BEED</option>
        <?php else: ?>
          <?php if ($userRole === 'admin' && $loggedInProgram === 'BSF'): ?>
            <option value="BSF" <?php if ($selected_program === 'BSF') echo 'selected'; ?>>BSF</option>
        <?php else: ?>


            <!-- For other roles or users, show all programs -->
            <option value="">All</option>
<option value="BSIT" <?php if ($selected_program === 'BSIT') echo 'selected'; ?>>BSIT (Bachelor of Science in Information Technology)</option>
<option value="BSOA" <?php if ($selected_program === 'BSOA') echo 'selected'; ?>>BSOA (Bachelor of Science in Office Administration)</option>
<option value="BSHM" <?php if ($selected_program === 'BSHM') echo 'selected'; ?>>BSHM (Bachelor of Science in Hospitality Management)</option>
<option value="CCJE" <?php if ($selected_program === 'CCJE') echo 'selected'; ?>>CCJE (College of Criminal Justice Education)</option>
<option value="BSED" <?php if ($selected_program === 'BSED') echo 'selected'; ?>>BSED (Bachelor of Secondary Education)</option>
<option value="BSABE" <?php if ($selected_program === 'BSABE') echo 'selected'; ?>>BSABE (Bachelor of Science in Agricultural and Bio system Engineering)</option>
<option value="BSND" <?php if ($selected_program === 'BSND') echo 'selected'; ?>>BSND (Bachelor of Science in Nutrition and Dietetics)</option>
<option value="BSA" <?php if ($selected_program === 'BSA') echo 'selected'; ?>>BSA (Bachelor of Science in Agriculture)</option> <!-- New course added -->
<option value="BTLED" <?php if ($selected_program === 'BTLED') echo 'selected'; ?>>BTLED (Bachelor of Science in Education)</option> <!-- New course added -->
<option value="BSTM" <?php if ($selected_program === 'BSTM') echo 'selected'; ?>>BSTM (Bachelor of Science in Tourism Management)</option> <!-- New course added -->
<option value="BEED" <?php if ($selected_program === 'BEED') echo 'selected'; ?>>BEED (Bachelor of Science in Elementary Education)</option> <!-- New course added -->
<option value="BSF" <?php if ($selected_program === 'BSF') echo 'selected'; ?>>BSF (Bachelor of Science in Forestry)</option> <!-- New course added -->

       
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>

       
    </select>
</form>
<!-- Search Section -->
<div class="filter-section">
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="search-form">
    <label for="search" class="visually-hidden">Search:</label>
    <div class="search-container">
      <input type="text" id="search" name="search" placeholder="Search by title, authors, year, adviser or abstract">
      <button type="submit" class="search-btn">Search</button>
    </div>
  </form>
</div>

<?php
if ($result->num_rows > 0) {
  // Check if there's a search query
  if (isset($_POST['search'])) {
    $searchQuery = trim(htmlspecialchars($_POST['search'])); // Trim spaces and sanitize input

    // If the search query is empty, don't process the search
    if (empty($searchQuery)) {
      echo "<p>Please enter a search term.</p>";
    } else {
      // Filter research data based on search query
      $filtered_data = [];
      while ($row = $result->fetch_assoc()) {
        $match = false;

        // Perform case-insensitive search in multiple fields
        if (
          stripos($row['title'], $searchQuery) !== false ||
          stripos($row['authors'], $searchQuery) !== false ||
          (isset($row['data']) && stripos($row['data'], $searchQuery) !== false) ||
          stripos($row['year'], $searchQuery) !== false ||
          stripos($row['abstract'], $searchQuery) !== false ||
          stripos($row['adviser'], $searchQuery) !== false // Search in adviser field
        ) {
          $match = true;
        }

        if ($match) {
          $filtered_data[] = $row;
        }
      }

      // Display filtered research items in a table
      if (count($filtered_data) > 0) {
        echo "<table class='research-table'>";
        echo "<thead><tr>
        <th>Title</th><th>Authors</th><th>Year</th><th>Abstract</th><th>Adviser</th><th>Actions</th></tr></thead>";
        echo "<tbody>";
        foreach ($filtered_data as $row) {
          echo "<tr>";
          echo "<td>" . htmlspecialchars($row['title']) . "</td>";
          echo "<td>" . htmlspecialchars($row['authors']) . "</td>";
          echo "<td>" . htmlspecialchars($row['year']) . "</td>";
          
          // Abstract truncation with Read More functionality
          $abstract = htmlspecialchars($row['abstract']);

          // Split sentences based on punctuation marks (., ?, !)
          $sentences = preg_split('/(?<=[.?!])\s+/', $abstract, -1, PREG_SPLIT_NO_EMPTY);

          if (count($sentences) > 4) {
            $shortAbstract = implode(' ', array_slice($sentences, 0, 4));
            echo "<td>
              <div class='abstract-container'>
                <span class='abstract-text'>" . htmlspecialchars($shortAbstract) . "...</span>
                <button class='toggle-abstract-btn' onclick='toggleAbstract(this)'>Read More</button>
                <span class='full-abstract' style='display: none;'>" . htmlspecialchars($abstract) . "</span>
              </div>
            </td>";
          } else {
            echo "<td>
              <div class='abstract-container'>
                <span class='abstract-text'>" . htmlspecialchars($abstract) . "</span>
              </div>
            </td>";
          }

          // Adviser field
          echo "<td>" . htmlspecialchars($row['adviser']) . "</td>";

          echo "<td>
                    <div class='research-item-actions'>
                      <a href='" . htmlspecialchars($row['file_path']) . "' class='view-btn' target='_blank'>View</a>
                      <a href='" . htmlspecialchars($row['file_path']) . "' class='download-btn' download>Download</a>
                    </div>
                  </td>";
          echo "</tr>";
        }
        echo "</tbody></table>";
      } else {
        echo "<p>No search results found for \"$searchQuery\".</p>";
      }
    }
  } else {
    // Display all research items in a table if no search query
    echo "<table class='research-table'>";
    echo "<thead><tr><th>Title</th><th>Authors</th><th>Year</th><th>Abstract/Project Context</th><th>Adviser</th><th>Actions</th></tr></thead>";
    echo "<tbody>";
    while ($row = $result->fetch_assoc()) {
      echo "<tr>";
      echo "<td>" . htmlspecialchars($row['title']) . "</td>";
      echo "<td>" . htmlspecialchars($row['authors']) . "</td>";
      echo "<td>" . htmlspecialchars($row['year']) . "</td>";

      // Abstract truncation for default display
      $abstract = htmlspecialchars($row['abstract']);
      echo "<td>
        <div class='abstract-container'>
          <span class='abstract-text'>" . htmlspecialchars(substr($row['abstract'], 0, 150)) . (strlen($row['abstract']) > 150 ? "..." : "") . "</span>
          <button class='toggle-abstract-btn' onclick='toggleAbstract(this)'>Read More</button>
          <span class='full-abstract' style='display: none;'>" . htmlspecialchars($row['abstract']) . "</span>
        </div>
      </td>";

      // Adviser field for default display
      echo "<td>" . htmlspecialchars($row['adviser']) . "</td>";

      echo "<td>
                <div class='research-item-actions'>
                  <a href='" . htmlspecialchars($row['file_path']) . "' class='view-btn' target='_blank'>View</a>
                  <a href='" . htmlspecialchars($row['file_path']) . "' class='download-btn' download>Download</a>
                </div>
              </td>";
      echo "</tr>";
    }
    echo "</tbody></table>";
  }
} else {
  echo "<p>No research files found for the selected program.</p>";
}
?>
<script>
  function toggleAbstract(button) {
    const container = button.closest('.abstract-container');
    const abstractText = container.querySelector('.abstract-text');
    const fullAbstract = container.querySelector('.full-abstract');

    if (button.textContent === "Read More") {
      abstractText.style.display = "none";
      fullAbstract.style.display = "inline";
      button.textContent = "Show Less";
    } else {
      abstractText.style.display = "inline";
      fullAbstract.style.display = "none";
      button.textContent = "Read More";
    }
  }
</script>



<!-- Pagination controls -->
<div class="pagination-container">
  <div class="pagination">
    <ul class="pagination justify-content-center">
      <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
          <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
        </li>
      <?php } ?>
      <li class="page-item <?php if ($page >= $totalPages) echo 'disabled'; ?>">
        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
      </li>
    </ul>
  </div>
</div>


<!-- Optional design styles for the table and buttons -->
<style>
  .research-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border: 1px solid #ddd;
  }
  .research-table th, .research-table td {
    padding: 12px 15px;
    text-align: left;
    border: 1px solid #ddd;
  }
  .research-table th {
    background-color: #f4f4f4;
    font-weight: bold;
  }
  .research-table tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  .research-table tr:hover {
    background-color: #f1f1f1;
  }
  .research-item-actions {
    display: flex; /* Display buttons on the same line */
    gap: 10px; /* Add space between buttons */
  }
  .research-item-actions a {
    padding: 6px 12px; /* Adjust padding for compactness */
    font-size: 14px; /* Set smaller font size */
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    display: inline-block;
  }
  .view-btn {
    background-color: #28a745;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.view-btn:hover {
    background-color: #218838;
    transform: scale(1.05); /* Slight zoom effect */
}

.view-btn:active {
    background-color: #1e7e34; /* Darker shade on click */
    transform: scale(0.98); /* Small scale down effect */
}

.download-btn {
    background-color: #007bff;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease, transform 0.2s ease;
}

.download-btn:hover {
    background-color: #0056b3;
    transform: scale(1.05); /* Slight zoom effect */
}

.download-btn:active {
    background-color: #004085; /* Darker shade on click */
    transform: scale(0.98); /* Small scale down effect */
}

  .pagination {
    margin-top: 20px;
  }
  .pagination .page-item.disabled .page-link {
    color: #ccc;
    pointer-events: none;
  }
  .pagination .page-item.active .page-link {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
  }
  .pagination .page-item .page-link {
    color: #007bff;
  }
  .pagination-container {
  overflow-x: auto;  /* Horizontal scrolling */
  white-space: nowrap;  /* Prevent pagination from wrapping */
  width: 100%;  /* Ensure the container takes full width of its parent */
  padding: 10px 0;  /* Optional: add padding for spacing */
}

.pagination {
  display: inline-flex;  /* Use inline-flex to make items horizontal */
  list-style: none;  /* Remove default list styling */
}

.page-item {
  margin: 0 5px;  /* Optional: add space between pagination items */
}

.page-link {
  padding: 8px 16px;  /* Adjust the size of each page link */
}

  .search-container {
    display: flex;
    gap: 10px;
  }
  .search-container input[type="text"] {
    padding: 8px;
    width: 250px;
    border: 1px solid #ccc;
  }
  .search-btn {
    padding: 8px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    cursor: pointer;
  }
  .search-btn:hover {
    background-color: #0056b3;
  }
  
</style>

<div class="main-container">
  </div>

<footer>
  © 2024 Quirino State University. All rights reserved.
</footer>
</body>
</html>

<?php
$conn->close();
?>
