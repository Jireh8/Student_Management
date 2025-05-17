<?php
require_once '../db_connection.php'; // adjust path as needed

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        // Collect and sanitize input
        $lastname = trim($_POST['lastname'] ?? '');
        $firstname = trim($_POST['firstname'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        $program_id = intval($_POST['program_id'] ?? 0);
        $section_id = intval($_POST['section_id'] ?? 0);
        $year_level = intval($_POST['year_level'] ?? 0);
        $current_semester = $_POST['current_semester'] ?? '';

        // Insert contact info
        $stmt = $conn->prepare("INSERT INTO contact_information (address, phone_number, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $address, $phone_number, $email);
        if ($stmt->execute()) {
            $contact_id = $stmt->insert_id;

            // Insert student info
            $stmt2 = $conn->prepare("INSERT INTO student_information (lastname, firstname, middle_name, birthdate, contact_id, program_id, section_id, year_level, current_semester, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssiiiiss", $lastname, $firstname, $middle_name, $birthdate, $contact_id, $program_id, $section_id, $year_level, $current_semester, $password);
            if ($stmt2->execute()) {
                echo "Student added successfully.";
            } else {
                echo "Error adding student: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            echo "Error adding contact info: " . $stmt->error;
        }
        $stmt->close();
        break;

    default:
        echo "Invalid action.";
        break;
}
?>