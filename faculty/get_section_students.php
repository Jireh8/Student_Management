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
$subjectId = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : null;

if (!$subjectId) {
    echo json_encode(['error' => 'Missing subject_id']);
    exit();
}

// Get section info and verify the instructor teaches this section
$sectionStmt = $conn->prepare("
    SELECT sec.section_name, COALESCE(p.program_name, 'No Program') as program_name
    FROM section sec
    LEFT JOIN student_information si ON sec.section_id = si.section_id
    LEFT JOIN program p ON si.program_id = p.program_id
    WHERE sec.section_id = ?
    GROUP BY sec.section_name, p.program_name
    LIMIT 1
");
$sectionStmt->bind_param("i", $section_id);
$sectionStmt->execute();
$sectionInfo = $sectionStmt->get_result()->fetch_assoc();

if (!$sectionInfo) {
    echo json_encode(['error' => 'Section not found']);
    exit();
}

// Verify instructor teaches this section
$verifyStmt = $conn->prepare("
    SELECT 1 FROM subject_instructor_section 
    WHERE section_id = ? AND instructor_id = ?
");
$verifyStmt->bind_param("ii", $section_id, $_SESSION['instructor_id']);
$verifyStmt->execute();
if ($verifyStmt->get_result()->num_rows === 0) {
    echo json_encode(['error' => 'You are not assigned to this section']);
    exit();
}

// Get available semesters for this section from program_subject
$semesterStmt = $conn->prepare("
    SELECT DISTINCT ps.semester_offered
    FROM subject_instructor_section sis
    JOIN program_subject ps ON sis.subject_id = ps.subject_id
    JOIN student_information si ON sis.section_id = si.section_id
    WHERE sis.section_id = ? 
    AND sis.instructor_id = ?
    AND ps.program_id = si.program_id
");
$semesterStmt->bind_param("ii", $section_id, $_SESSION['instructor_id']);
$semesterStmt->execute();
$semesterResult = $semesterStmt->get_result();
$availableSemesters = [];
while ($semRow = $semesterResult->fetch_assoc()) {
    $availableSemesters[] = $semRow['semester_offered'];
}

// If no specific semester is provided or the semester doesn't match available ones,
// use the first available semester
if (empty($semester) || !in_array($semester, $availableSemesters)) {
    $semester = !empty($availableSemesters) ? $availableSemesters[0] : '1st';
}

// Get students in section
$students = [];
$studentStmt = $conn->prepare("
    SELECT 
        si.student_id, 
        si.firstname, 
        si.middle_name, 
        si.lastname, 
        si.year_level, 
        si.current_semester,
        p.program_name, 
        p.program_id
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
        if ($student['current_semester'] != $semester) {
        continue;
    }
    $year_offered = isset($year_map[$student['year_level']]) ? $year_map[$student['year_level']] : $student['year_level'];

    // Get the specific subject for this student
    $subjectStmt = $conn->prepare("
        SELECT 
            s.subject_id, 
            s.subject_name, 
            s.subject_code,
            ps.semester_offered
        FROM subject s
        INNER JOIN program_subject ps ON s.subject_id = ps.subject_id
        WHERE ps.program_id = ? 
        AND ps.year_offered = ?
        AND ps.semester_offered = ?
        AND s.subject_id = ?
    ");
    $subjectStmt->bind_param("issi", $student['program_id'], $year_offered, $semester, $subjectId);
    $subjectStmt->execute();
    $subjectResult = $subjectStmt->get_result();

    if ($subject = $subjectResult->fetch_assoc()) {
        // Verify this instructor teaches this subject in this section
        $instructorSubjectStmt = $conn->prepare("
            SELECT 1 FROM subject_instructor_section
            WHERE section_id = ?
            AND subject_id = ?
            AND instructor_id = ?
        ");
        $instructorSubjectStmt->bind_param("iii", $section_id, $subject['subject_id'], $_SESSION['instructor_id']);
        $instructorSubjectStmt->execute();

        if ($instructorSubjectStmt->get_result()->num_rows > 0) {
            // Get grade if exists for this specific semester
            $gradeStmt = $conn->prepare("
                SELECT final_grade, scholastic_status
                FROM student_grades
                WHERE student_id = ? 
                AND subject_id = ? 
                AND school_year = ? 
                AND semester = ?
            ");
            $gradeStmt->bind_param("iiss", $student['student_id'], $subject['subject_id'], $school_year, $semester);
            $gradeStmt->execute();
            $gradeResult = $gradeStmt->get_result();
            $gradeData = $gradeResult->fetch_assoc();

            $students[] = [
                'student_id' => $student['student_id'],
                'student_name' => trim($student['lastname'] . ', ' . $student['firstname'] . ' ' . $student['middle_name']),
                'program_name' => $student['program_name'],
                'year_level' => $student['year_level'],
                'subject' => [
                    'subject_id' => $subject['subject_id'],
                    'subject_name' => $subject['subject_name'],
                    'subject_code' => $subject['subject_code'],
                    'semester_offered' => $subject['semester_offered'],
                    'final_grade' => $gradeData ? $gradeData['final_grade'] : 0,
                    'scholastic_status' => $gradeData ? $gradeData['scholastic_status'] : ''
                ]
            ];
        }
    }
}

echo json_encode([
    'section_name' => $sectionInfo['section_name'],
    'program_name' => $sectionInfo['program_name'],
    'semester' => $semester,
    'available_semesters' => $availableSemesters,
    'student_count' => count($students),
    'students' => $students
]);
$conn->close();