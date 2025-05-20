<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
include '../config.php';

function redirect($message) {
    ob_end_clean();
    header("Location: faculty_ui.php?" . $message);
    exit();
}

try {
    if (!isset($_SESSION['instructor_id'])) {
        redirect("error=unauthorized");
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $student_id = $_POST['student_id'];
        $subject_id = $_POST['subject_id'];
        $action = $_POST['action'];
        $final_grade = $_POST['grade'];
        $current_semester = isset($_POST['semester']) ? $_POST['semester'] : '1st';

        // Get school_year from POST or fetch from student_grades
        if (!empty($_POST['school_year'])) {
            $school_year = $_POST['school_year'];
        } else {
            $syStmt = $conn->prepare("SELECT school_year FROM student_grades WHERE student_id = ? AND subject_id = ? AND semester = ? ORDER BY school_year DESC LIMIT 1");
            $syStmt->bind_param("iis", $student_id, $subject_id, $current_semester);
            $syStmt->execute();
            $syResult = $syStmt->get_result();
            if ($syRow = $syResult->fetch_assoc()) {
                $school_year = $syRow['school_year'];
            } else {
                $school_year = date('Y') . '-' . (date('Y') + 1);
            }
        }

        // Validate grade (assuming 1.00-5.00 scale)
        if ($final_grade < 0 || $final_grade > 5) {
            redirect("error=invalid_grade");
        }

        // Set scholastic status: >3.00 = Irregular, <=3.00 = Regular
        $scholastic_status = ($final_grade > 3.00) ? 'Irregular' : 'Regular';

        // Check if the instructor teaches this student's section and subject
        $checkInstructor = $conn->prepare("
            SELECT 1 
            FROM student_information si
            INNER JOIN subject_instructor_section sis ON si.section_id = sis.section_id
            WHERE si.student_id = ? AND sis.instructor_id = ? AND sis.subject_id = ?
        ");
        $checkInstructor->bind_param("iii", $student_id, $_SESSION['instructor_id'], $subject_id);
        $checkInstructor->execute();
        $result = $checkInstructor->get_result();

        if ($result->num_rows == 0) {
            redirect("error=unauthorized_student");
        }

        // Check if a record exists
        $checkRecord = $conn->prepare("SELECT 1 FROM student_grades 
            WHERE student_id = ? AND subject_id = ? AND school_year = ? AND semester = ?");
        $checkRecord->bind_param("iiss", $student_id, $subject_id, $school_year, $current_semester);
        $checkRecord->execute();
        $recordExists = $checkRecord->get_result()->num_rows > 0;

        if ($recordExists) {
            // Update existing record
            $updateGrade = $conn->prepare("UPDATE student_grades 
                SET final_grade = ?, scholastic_status = ?
                WHERE student_id = ? AND subject_id = ? AND school_year = ? AND semester = ?");
            $updateGrade->bind_param("dsisss", $final_grade, $scholastic_status, $student_id, $subject_id, $school_year, $current_semester);
        } else {
            // Insert new record
            $updateGrade = $conn->prepare("INSERT INTO student_grades 
                (student_id, subject_id, final_grade, scholastic_status, school_year, semester) 
                VALUES (?, ?, ?, ?, ?, ?)");
            $updateGrade->bind_param("iidsss", $student_id, $subject_id, $final_grade, $scholastic_status, $school_year, $current_semester);
        }

        if ($updateGrade->execute()) {
            $conn->close();
            redirect("success=grade_updated");
        } else {
            $conn->close();
            redirect("error=update_failed");
        }
    }

    $conn->close();
    redirect("");
} catch (Exception $e) {
    if (isset($conn)) {
        $conn->close();
    }
    redirect("error=system_error");
}
?>