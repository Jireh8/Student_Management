<?php
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$instructorId = $_POST['instructor_id'] ?? null;
$subjectId = $_POST['subject_id'] ?? null;
$sectionId = $_POST['section_id'] ?? null;

if (!$instructorId || !$subjectId || !$sectionId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // Insert the assignment
    $stmt = $conn->prepare("
        INSERT INTO subject_instructor_section (subject_id, instructor_id, section_id)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("iii", $subjectId, $instructorId, $sectionId);
    $stmt->execute();
    $sisId = $stmt->insert_id;
    
    // Get subject and section details for response
    $detailsQuery = $conn->prepare("
        SELECT s.subject_code, s.subject_name, s.units, sec.section_name
        FROM subject s
        JOIN section sec ON sec.section_id = ?
        WHERE s.subject_id = ?
    ");
    $detailsQuery->bind_param("ii", $sectionId, $subjectId);
    $detailsQuery->execute();
    $details = $detailsQuery->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'sis_id' => $sisId,
        'subject_code' => $details['subject_code'],
        'subject_name' => $details['subject_name'],
        'units' => $details['units'],
        'section_name' => $details['section_name'],
        'subject_id' => $subjectId
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>