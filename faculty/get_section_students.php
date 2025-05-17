<?php
include '../config.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['instructor_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$section_id = intval($_GET['section_id']);
$semester = $_GET['semester'];
$school_year = $_GET['school_year'];

// Get section info
$sectionStmt = $conn->prepare("
    SELECT sec.section_name, COALESCE(p.program_name, 'No Program') as program_name
    FROM section sec
    LEFT JOIN student_information si ON sec.section_id = si.section_id
    LEFT JOIN program p ON si.program_id = p.program_id
    WHERE sec.section_id = ?
    LIMIT 1
");
$sectionStmt->bind_param("i", $section_id);
$sectionStmt->execute();
$sectionInfo = $sectionStmt->get_result()->fetch_assoc();

if (!$sectionInfo) {
    echo json_encode(['error' => 'Section not found']);
    exit();
}

// Get students in section
$students = [];
$studentStmt = $conn->prepare("
    SELECT si.student_id, si.firstname, si.middle_name, si.lastname, si.year_level, p.program_name, si.program_id
    FROM student_information si
    LEFT JOIN program p ON si.program_id = p.program_id
    WHERE si.section_id = ?
    ORDER BY si.lastname, si.firstname
");
$studentStmt->bind_param("i", $section_id);
$studentStmt->execute();
$studentResult = $studentStmt->get_result();

$year_map = [1 => '1st', 2 => '2nd', 3 => '3rd', 4 => '4th'];

while ($student = $studentResult->fetch_assoc()) {
    // Map year_level integer to year_offered string
    $year_offered = isset($year_map[$student['year_level']]) ? $year_map[$student['year_level']] : $student['year_level'];
    $subjects = [];
    $subjectStmt = $conn->prepare("
        SELECT s.subject_id, s.subject_name, s.subject_code
        FROM program_subject ps
        INNER JOIN subject s ON ps.subject_id = s.subject_id
        WHERE ps.program_id = ? AND ps.year_offered = ? AND ps.semester_offered = ?
    ");
    $subjectStmt->bind_param("iss", $student['program_id'], $year_offered, $semester);
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();

    while ($subject = $subjectResult->fetch_assoc()) {
        // Get grade if exists
        $gradeStmt = $conn->prepare("
            SELECT final_grade, scholastic_status
            FROM student_grades
            WHERE student_id = ? AND subject_id = ? AND school_year = ? AND semester = ?
        ");
        $gradeStmt->bind_param("iiss", $student['student_id'], $subject['subject_id'], $school_year, $semester);
        $gradeStmt->execute();
        $gradeResult = $gradeStmt->get_result();
        $gradeData = $gradeResult->fetch_assoc();

        $subjects[] = [
            'subject_id' => $subject['subject_id'],
            'subject_name' => $subject['subject_name'],
            'subject_code' => $subject['subject_code'],
            'final_grade' => $gradeData['final_grade'] ?? 0,
            'scholastic_status' => $gradeData['scholastic_status'] ?? ''
        ];
    }

    $students[] = [
        'student_id' => $student['student_id'],
        'student_name' => $student['lastname'] . ', ' . $student['firstname'] . ' ' . $student['middle_name'],
        'program_name' => $student['program_name'],
        'year_level' => $student['year_level'],
        'subjects' => $subjects
    ];
}

echo json_encode([
    'section_name' => $sectionInfo['section_name'],
    'program_name' => $sectionInfo['program_name'],
    'student_count' => count($students),
    'students' => $students
]);
$conn->close();
?>