<?php
require_once '../config.php';
header('Content-Type: application/json'); 
$student_id = $_GET['student_id'] ?? 0;

$query = $conn->prepare("
    SELECT si.*, ci.*, p.program_id, s.section_id 
    FROM student_information AS si
    INNER JOIN contact_information AS ci ON si.contact_id = ci.contact_id
    INNER JOIN program AS p ON si.program_id = p.program_id
    INNER JOIN section AS s ON si.section_id = s.section_id
    WHERE si.student_id = ?
");
$query->bind_param("i", $student_id);
$query->execute();
$result = $query->get_result();

if($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'student_id' => $student['student_id'],
        'contact_id' => $student['contact_id'],
        'lastname' => $student['lastname'],
        'firstname' => $student['firstname'],
        'middle_name' => $student['middle_name'],
        'birthdate' => $student['birthdate'],
        'address' => $student['address'],
        'phone_number' => $student['phone_number'],
        'email' => $student['email'],
        'program_id' => $student['program_id'],
        'section_id' => $student['section_id'],
        'year_level' => $student['year_level'],
        'current_semester' => $student['current_semester']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Student not found']);
}
?>