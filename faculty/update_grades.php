<?php
// Start output buffering and ensure no output
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();
include '../config.php';

// Function to redirect with message
function redirect($message) {
    ob_end_clean(); // End and clean the buffer
    header("Location: faculty_ui.php?" . $message);
    exit();
}

// Log any errors
function logError($message) {
    error_log("Update Grades Error: " . $message);
}

try {
    if (!isset($_SESSION['instructor_id'])) {
        redirect("error=unauthorized");
    }

    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $student_id = $_POST['student_id'];
        $action = $_POST['action'];
        $gwa = $_POST['grade'];
        $school_year = '2024-2025';
        $current_semester = isset($_POST['semester']) ? $_POST['semester'] : '1st';

        // Validate GWA
        if ($gwa < 0 || $gwa > 100) {
            redirect("error=invalid_grade");
        }

        // Check if the instructor teaches this student's section
        $checkInstructor = $conn->prepare("SELECT 1 
                                         FROM student_information si
                                         INNER JOIN subject_instructor_section sis ON si.section_id = sis.section_id
                                         WHERE si.student_id = ? AND sis.instructor_id = ?");
        $checkInstructor->bind_param("ii", $student_id, $_SESSION['instructor_id']);
        $checkInstructor->execute();
        $result = $checkInstructor->get_result();

        if ($result->num_rows == 0) {
            redirect("error=unauthorized_student");
        }

        // Check if a record exists
        $checkRecord = $conn->prepare("SELECT 1 FROM academic_records 
                                     WHERE student_id = ? AND school_year = ? AND semester = ?");
        $checkRecord->bind_param("iss", $student_id, $school_year, $current_semester);
        $checkRecord->execute();
        $recordExists = $checkRecord->get_result()->num_rows > 0;

        if ($recordExists) {
            // Update existing record
            $updateGWA = $conn->prepare("UPDATE academic_records 
                                       SET gwa = ? 
                                       WHERE student_id = ? AND school_year = ? AND semester = ?");
            $updateGWA->bind_param("diss", $gwa, $student_id, $school_year, $current_semester);
        } else {
            // Insert new record
            $updateGWA = $conn->prepare("INSERT INTO academic_records 
                                       (student_id, school_year, semester, gwa) 
                                       VALUES (?, ?, ?, ?)");
            $updateGWA->bind_param("issd", $student_id, $school_year, $current_semester, $gwa);
        }
        
        if ($updateGWA->execute()) {
            $conn->close();
            redirect("success=gwa_updated");
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