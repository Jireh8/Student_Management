<?php
// Start output buffering
ob_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

include '../config.php';
session_start();

// Function to send JSON response
function sendJsonResponse($data, $statusCode = 200) {
    ob_clean(); // Clear any previous output
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

if (!isset($_SESSION['instructor_id'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (isset($_GET['section_id']) && isset($_GET['semester']) && isset($_GET['school_year'])) {
    $section_id = $_GET['section_id'];
    $semester = $_GET['semester'];
    $school_year = $_GET['school_year'];
    
    try {
        // Check if the instructor teaches this section
        $checkInstructor = $conn->prepare("SELECT 1 FROM subject_instructor_section WHERE section_id = ? AND instructor_id = ?");
        $checkInstructor->bind_param("ii", $section_id, $_SESSION['instructor_id']);
        $checkInstructor->execute();
        $result = $checkInstructor->get_result();
        if ($result->num_rows == 0) {
            echo json_encode(['error' => 'Unauthorized access to section']);
            exit();
        }

        // Get section name, program, and student count
        $sectionInfoStmt = $conn->prepare("SELECT s.section_name, p.program_name, COUNT(si.student_id) as student_count FROM section s INNER JOIN student_information si ON s.section_id = si.section_id INNER JOIN program p ON si.program_id = p.program_id WHERE s.section_id = ? GROUP BY s.section_id, p.program_name");
        $sectionInfoStmt->bind_param("i", $section_id);
        $sectionInfoStmt->execute();
        $sectionInfoResult = $sectionInfoStmt->get_result();
        $sectionInfo = $sectionInfoResult->fetch_assoc();

        // Get students in the section with their GWA for the selected semester and school year
        $stmt = $conn->prepare("SELECT si.student_id, CONCAT(si.firstname, ' ', si.lastname) as student_name, p.program_name, si.year_level, ? as semester, ar.gwa FROM student_information si INNER JOIN program p ON si.program_id = p.program_id INNER JOIN section sec ON si.section_id = sec.section_id LEFT JOIN academic_records ar ON si.student_id = ar.student_id AND ar.school_year = ? AND ar.semester = ? WHERE si.section_id = ? ORDER BY si.year_level, sec.section_name, p.program_name, si.student_id");
        $stmt->bind_param("sssi", $semester, $school_year, $semester, $section_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = [];
        while ($row = $result->fetch_assoc()) {
            $students[] = $row;
        }
        echo json_encode([
            'section_id' => $section_id,
            'section_name' => $sectionInfo['section_name'],
            'program_name' => $sectionInfo['program_name'],
            'student_count' => $sectionInfo['student_count'],
            'students' => $students
        ]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error']);
    }
} else {
    echo json_encode(['error' => 'Missing parameters']);
} 