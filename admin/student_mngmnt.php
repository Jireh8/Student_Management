<?php
require_once '../config.php';

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
        $sex = 'Others'; // Default value, you might want to add this to your form

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert contact info
            $stmt = $conn->prepare("INSERT INTO contact_information (address, phone_number, email, contact_role, password) 
                                   VALUES (?, ?, ?, 'Student', ?)");
            $stmt->bind_param("ssss", $address, $phone_number, $email, $password);
            $stmt->execute();
            $contact_id = $stmt->insert_id;

            // Insert student info
            $stmt2 = $conn->prepare("INSERT INTO student_information 
                                   (lastname, firstname, middle_name, birthdate, contact_id, program_id, 
                                    section_id, year_level, current_semester, sex) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssiiiiss", $lastname, $firstname, $middle_name, $birthdate, 
                              $contact_id, $program_id, $section_id, $year_level, $current_semester, $sex);
            $stmt2->execute();
            $student_id = $stmt2->insert_id;

            // Create academic records for next 4 years
            $current_year = date('Y');
            for ($year = 0; $year < 4; $year++) {
                $school_year = ($current_year + $year) . '-' . ($current_year + $year + 1);
                
                // 1st semester
                $stmt3 = $conn->prepare("INSERT INTO academic_records 
                                       (student_id, school_year, semester, gwa) 
                                       VALUES (?, ?, '1st', 0.00)");
                $stmt3->bind_param("is", $student_id, $school_year);
                $stmt3->execute();
                
                // 2nd semester
                $stmt4 = $conn->prepare("INSERT INTO academic_records 
                                       (student_id, school_year, semester, gwa) 
                                       VALUES (?, ?, '2nd', 0.00)");
                $stmt4->bind_param("is", $student_id, $school_year);
                $stmt4->execute();
            }

            // Get all subjects for the student's program
            $subjects = $conn->query("
                SELECT subject_id FROM program_subject 
                WHERE program_id = $program_id
                ORDER BY year_offered, semester_offered
            ");

            // Create student grades for all subjects in the program
            while ($subject = $subjects->fetch_assoc()) {
                $subject_id = $subject['subject_id'];
                
                $stmt5 = $conn->prepare("INSERT INTO student_grades 
                                       (student_id, subject_id, final_grade, school_year, semester, scholastic_status) 
                                       VALUES (?, ?, 0.00, ?, '1st', 'Regular')");
                $stmt5->bind_param("iis", $student_id, $subject_id, $school_year);
                $stmt5->execute();
                
                $stmt6 = $conn->prepare("INSERT INTO student_grades 
                                       (student_id, subject_id, final_grade, school_year, semester, scholastic_status) 
                                       VALUES (?, ?, 0.00, ?, '2nd', 'Regular')");
                $stmt6->bind_param("iis", $student_id, $subject_id, $school_year);
                $stmt6->execute();
            }

            $conn->commit();
            echo "Student added successfully with all academic records.";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
        break;

    case 'edit':
        $student_id = intval($_POST['student_id'] ?? 0);
        $contact_id = intval($_POST['contact_id'] ?? 0);
        $lastname = trim($_POST['lastname'] ?? '');
        $firstname = trim($_POST['firstname'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $birthdate = $_POST['birthdate'] ?? '';
        $address = trim($_POST['address'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        $program_id = intval($_POST['program_id'] ?? 0);
        $section_id = intval($_POST['section_id'] ?? 0);
        $year_level = intval($_POST['year_level'] ?? 0);
        $current_semester = $_POST['current_semester'] ?? '';

        // Start transaction
        $conn->begin_transaction();

        try {
            // Update contact info
            if ($password) {
                $stmt = $conn->prepare("UPDATE contact_information 
                                      SET address=?, phone_number=?, email=?, password=?
                                      WHERE contact_id=?");
                $stmt->bind_param("ssssi", $address, $phone_number, $email, $password, $contact_id);
            } else {
                $stmt = $conn->prepare("UPDATE contact_information 
                                      SET address=?, phone_number=?, email=?
                                      WHERE contact_id=?");
                $stmt->bind_param("sssi", $address, $phone_number, $email, $contact_id);
            }
            $stmt->execute();

            // Update student info
            $stmt2 = $conn->prepare("UPDATE student_information 
                                   SET lastname=?, firstname=?, middle_name=?, birthdate=?,
                                       program_id=?, section_id=?, year_level=?, current_semester=?
                                   WHERE student_id=?");
            $stmt2->bind_param("ssssiiiisi", $lastname, $firstname, $middle_name, $birthdate,
                              $program_id, $section_id, $year_level, $current_semester, $student_id);
            $stmt2->execute();

            $conn->commit();
            echo "Student updated successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error updating student: " . $e->getMessage();
        }
        break;

    case 'delete':
        $student_id = intval($_POST['student_id'] ?? 0);
        
        // Start transaction
        $conn->begin_transaction();

        try {
            // First get contact_id to delete contact info later
            $stmt = $conn->prepare("SELECT contact_id FROM student_information WHERE student_id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            $contact_id = $student['contact_id'];

            // Delete academic records
            $stmt1 = $conn->prepare("DELETE FROM academic_records WHERE student_id = ?");
            $stmt1->bind_param("i", $student_id);
            $stmt1->execute();

            // Delete student grades
            $stmt2 = $conn->prepare("DELETE FROM student_grades WHERE student_id = ?");
            $stmt2->bind_param("i", $student_id);
            $stmt2->execute();

            // Delete student information
            $stmt3 = $conn->prepare("DELETE FROM student_information WHERE student_id = ?");
            $stmt3->bind_param("i", $student_id);
            $stmt3->execute();

            // Delete contact information
            $stmt4 = $conn->prepare("DELETE FROM contact_information WHERE contact_id = ?");
            $stmt4->bind_param("i", $contact_id);
            $stmt4->execute();

            $conn->commit();
            echo "Student deleted successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error deleting student: " . $e->getMessage();
        }
        break;

    default:
        echo "Invalid action.";
        break;
}
?>