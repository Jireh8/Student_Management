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
        $checkUserExist->bind_param("s", $email);
        $checkUserExist->execute();
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
                header("Location: student_login.html?error=invalid_credentials");
                exit();
            }
        }
        else {
            header("Location: student_login.html?error=invalid_credentials");
            echo "<script type='text/javascript'>alert('Invalid credentials. Please try again.');</script>";
            exit();
        }
        $checkUserExist->close();
    }
 
?>
