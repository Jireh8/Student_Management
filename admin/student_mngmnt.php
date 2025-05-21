<?php
require_once '../config.php';
header('Content-Type: application/json');
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
        $year_level = trim($_POST['year_level'] ?? '');
        $current_semester = $_POST['current_semester'] ?? '';
        $sex = trim($_POST['sex'] ?? '');

        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert contact information
            $stmt = $conn->prepare("INSERT INTO contact_information 
                                  (address, phone_number, email, contact_role, password) 
                                  VALUES (?, ?, ?, 'Student', ?)");
            $stmt->bind_param("ssss", $address, $phone_number, $email, $password);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert contact information: " . $stmt->error);
            }
            $contact_id = $stmt->insert_id;

            // Insert student information
            $stmt2 = $conn->prepare("INSERT INTO student_information 
                                   (lastname, firstname, middle_name, birthdate, contact_id, 
                                    program_id, section_id, year_level, current_semester, sex) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ssssiiisss", $lastname, $firstname, $middle_name, $birthdate, 
                              $contact_id, $program_id, $section_id, $year_level, $current_semester, $sex);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to insert student information: " . $stmt2->error);
            }
            $student_id = $stmt2->insert_id;

            // Create academic records and student grades for next 4 years
            $current_year = date('Y');
            $current_month = date('n');
            $is_second_semester = ($current_month >= 6); // Adjust according to your academic calendar

            $yearLevelStrings = ['1st', '2nd', '3rd', '4th'];
            $currentYearIndex = array_search($year_level, $yearLevelStrings); // $year_level is a string like '1st'

            for ($year_offset = 0; $year_offset < 4; $year_offset++) {
                $studentYearIndex = $currentYearIndex + $year_offset;

                // Skip if > 4th year
                if ($studentYearIndex >= count($yearLevelStrings)) {
                    continue;
                }

    $school_year = ($current_year + $year_offset) . '-' . ($current_year + $year_offset + 1);
    $year_offered = $yearLevelStrings[$studentYearIndex]; // This replaces $student_year_level


                // Create academic records for both semesters
                foreach (['1st', '2nd'] as $semester) {
                    // Insert academic record
                    $stmt3 = $conn->prepare("INSERT INTO academic_records 
                                           (student_id, school_year, semester, gwa) 
                                           VALUES (?, ?, ?, 0.00)");
                    $stmt3->bind_param("iss", $student_id, $school_year, $semester);
                    if (!$stmt3->execute()) {
                        throw new Exception("Failed to create academic record: " . $stmt3->error);
                    }

                    // Get subjects for this program, year level, and semester
                    $subjects = $conn->prepare("
                        SELECT ps.subject_id 
                        FROM program_subject ps
                        WHERE ps.program_id = ?
                        AND ps.year_offered = ?
                        AND ps.semester_offered = ?
                    ");
                    $subjects->bind_param("iss", $program_id, $year_offered, $semester);
                    if (!$subjects->execute()) {
                        throw new Exception("Failed to fetch subjects: " . $subjects->error);
                    }
                    $subjectResults = $subjects->get_result();

                    // Create grade records for each subject
                    while ($subject = $subjectResults->fetch_assoc()) {
                        $stmt4 = $conn->prepare("
                            INSERT INTO student_grades 
                            (student_id, subject_id, final_grade, school_year, semester, scholastic_status, grade_year_level) 
                            VALUES (?, ?, 0.00, ?, ?, 'Regular', ?)
                        ");
                        $stmt4->bind_param("iisss", $student_id, $subject['subject_id'], $school_year, $semester, $year_offered);
                        if (!$stmt4->execute()) {
                            throw new Exception("Failed to create grade record: " . $stmt4->error);
                        }
                    }
                }
            }

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Student added successfully with academic records for 4 years.',
                'student_id' => $student_id
            ]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ]);
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
        $year_level = trim($_POST['year_level'] ?? '');
        $current_semester = $_POST['current_semester'] ?? '';
        $sex = trim($_POST['sex'] ?? '');

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
                                       program_id=?, section_id=?, year_level=?, current_semester=?, sex =?
                                   WHERE student_id=?");
            $stmt2->bind_param("ssssiisisi", $lastname, $firstname, $middle_name, $birthdate,
                              $program_id, $section_id, $year_level, $current_semester, $sex, $student_id);
             if (!$stmt2->execute()) {
            throw new Exception("Failed to update student information: " . $stmt2->error);
            }

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
       
        break;
}
