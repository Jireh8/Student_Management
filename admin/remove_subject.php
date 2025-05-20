<?php
include '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$sisId = $_POST['sis_id'] ?? null;

if (!$sisId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

try {
    // First get subject details before deleting
    $getQuery = $conn->prepare("
        SELECT s.subject_id, s.subject_code, s.subject_name
        FROM subject_instructor_section sis
        JOIN subject s ON sis.subject_id = s.subject_id
        WHERE sis.sis_id = ?
    ");
    $getQuery->bind_param("i", $sisId);
    $getQuery->execute();
    $subjectDetails = $getQuery->get_result()->fetch_assoc();
    
    if (!$subjectDetails) {
        echo json_encode(['success' => false, 'message' => 'Assignment not found']);
        exit;
    }

    // First, delete related schedules
    $deleteScheduleStmt = $conn->prepare("DELETE FROM schedule WHERE sis_id = ?");
    $deleteScheduleStmt->bind_param("i", $sisId);
    $deleteScheduleStmt->execute();

    // Now delete the assignment
    $deleteStmt = $conn->prepare("DELETE FROM subject_instructor_section WHERE sis_id = ?");
    $deleteStmt->bind_param("i", $sisId);
    $deleteStmt->execute();
    
    echo json_encode([
        'success' => true,
        'subject_id' => $subjectDetails['subject_id'],
        'subject_code' => $subjectDetails['subject_code'],
        'subject_name' => $subjectDetails['subject_name']
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>