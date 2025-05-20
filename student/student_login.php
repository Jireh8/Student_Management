<?php
    session_start();
    include '../config.php';

    if($_SERVER['REQUEST_METHOD'] == "POST") {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $checkUserExist = $conn->prepare("SELECT *
                                        FROM contact_information AS ci
                                        INNER JOIN student_information AS si
                                            ON ci.contact_id = si.contact_id
                                        INNER JOIN program AS prog
                                            ON si.program_id = prog.program_id
                                        INNER JOIN academic_records AS ar
                                            ON si.student_id = ar.student_id
                                        INNER JOIN department AS dept
                                            ON prog.department_id = dept.department_id 
                                        INNER JOIN student_grades AS sg
                                            ON si.student_id = sg.student_id
                                        WHERE ci.email=?");
        if (!$checkUserExist) {
            header("Location: student_login.php?error=db_error");
            exit();
        }
        $checkUserExist->bind_param("s", $email);
        if (!$checkUserExist->execute()) {
            header("Location: student_login.php?error=db_error");
            exit();
        }
        $result = $checkUserExist->get_result();

        if($result->num_rows != 0) {
            $user = $result->fetch_assoc();
            // Use password_verify to check hashed password
            if (password_verify($password, $user['password'])) {
                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['middle_name'] = $user['middle_name'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['birthdate'] = $user['birthdate'];
                $_SESSION['sex'] = $user['sex'];
                $_SESSION['address'] = $user['address'];
                $_SESSION['phone_number'] = $user['phone_number'];
                $_SESSION['email'] = $email;
                $_SESSION['year_level'] = $user['year_level'];
                $_SESSION['program'] = $user['program_name'];
                $_SESSION['sem'] = $user['current_semester'];
                $_SESSION['scho_status'] = $user['scholastic_status'];
                $_SESSION['school_year'] = $user['school_year'];
                $_SESSION['department'] = $user['department_name'];
                $_SESSION['program_id'] = $user['program_id'];

                header("Location: student_ui.php?success=1");
                exit();
            } else {
                header("Location: student_login.php?error=invalid_credentials");
                exit();
            }
        }
        else {
            header("Location: student_login.php?error=invalid_credentials");
            exit();
        }
        $checkUserExist->close();
    }
?>
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
        <h2>Student Login</h2>
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
        <form action="student_login.php" method="POST">
            <div class="child-login">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="child-login">
                <label>Password</label>
                <input type="password" name="password" required>
                <span>Forgot password? <a href="">Contact us</a></span>
            </div>
            <button type="submit">Login</button>
            <p>Faculty member? <a href="../faculty/faculty_login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>