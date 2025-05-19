<?php
include '../config.php';

header('Content-Type: application/json');

$instructorId = $_GET['instructor_id'] ?? null;

if (!$instructorId) {
    echo json_encode(['success' => false, 'message' => 'Instructor ID is required']);
    exit;
}

try {
    $query = $conn->prepare("
        SELECT 
            sis.sis_id,
            s.subject_name,
            sec.section_name
        FROM subject_instructor_section sis
        JOIN subject s ON sis.subject_id = s.subject_id
        JOIN section sec ON sis.section_id = sec.section_id
        WHERE sis.instructor_id = ?
        ORDER BY s.subject_name
    ");
    $query->bind_param("i", $instructorId);
    $query->execute();
    $result = $query->get_result();
    
    echo json_encode([
        'success' => true,
        'assignments' => $result->fetch_all(MYSQLI_ASSOC)
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>