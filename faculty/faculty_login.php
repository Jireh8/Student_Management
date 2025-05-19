<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../login.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <header id="univ-header">
        <!--<img src="university-logo.png" alt="University Logo" id="univ-logo"><-->
        <span id="univ-logo" class="material-icons">school</span>
        <h1 id="univ-name">Xydle University</h1>
    </header>

    <div class="login-container" id="login-div">
        <h2>Faculty Login</h2>
        <?php if(isset($_GET['error'])): ?>
            <div class="error-message">
                <?php 
                    switch($_GET['error']) {
                        case 'invalid_credentials':
                            echo "Invalid email or password";
                            break;
                        case 'db_error':
                            echo "Database error occurred";
                            break;
                        default:
                            echo "An error occurred";
                    }
                ?>
            </div>
        <?php endif; ?>
        <form action="faculty_login.php" method="POST">
            <div class="child-login">
                <label>Email</label>
                <input type="email" name="username" required>
            </div>
            <div class="child-login">
                <label>Password</label>
                <input type="password" name="password" required>
                <span>Forgot password? <a href="">Contact us</a></span>
            </div>
            <button type="submit">Login</button>
            <p>Are you a student? <a href="../student/student_login.html">Login here</a></p>
        </form>
    </div>
</body>
</html>

<?php
    session_start();
    include '../config.php';

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Debug: Print received credentials
        error_log("Attempting login with username: " . $username);

        $checkUserExist = $conn->prepare("SELECT * 
                                        FROM instructor AS ins
                                        INNER JOIN contact_information AS ci
                                            ON ins.contact_id = ci.contact_id
                                        WHERE ci.email=?");
        if (!$checkUserExist) {
            error_log("Prepare failed: " . $conn->error);
            header("Location: faculty_login.php?error=db_error");
            exit();
        }

        $checkUserExist->bind_param("s", $username);
        if (!$checkUserExist->execute()) {
            error_log("Execute failed: " . $checkUserExist->error);
            header("Location: faculty_login.php?error=db_error");
            exit();
        }

        $result = $checkUserExist->get_result();
        
        if($result->num_rows != 0) {
            $user = $result->fetch_assoc();
            // Check hashed password
            if (password_verify($password, $user['password'])) {
                error_log("Login successful for user: " . $username);

                $_SESSION['instructor_id'] = $user['instructor_id'];
                $_SESSION['username'] = $user['instructor_name'];

                header("Location: faculty_ui.php?success=1");
                exit();
            } else {
                error_log("Login failed for user: " . $username . " (invalid password)");
                header("Location: faculty_login.php?error=invalid_credentials");
                exit();
            }
        }
        else {
            error_log("Login failed for user: " . $username . " (no such user)");
            header("Location: faculty_login.php?error=invalid_credentials");
            exit();
        }
        $checkUserExist->close();
    }
?>