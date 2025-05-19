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
$dayOfWeek = $_POST['day_of_week'] ?? null;
$startTime = $_POST['start_time'] ?? null;
$endTime = $_POST['end_time'] ?? null;
$roomNumber = $_POST['room_number'] ?? null;

if (
    !$instructorId || !$subjectId || !$sectionId ||
    !$dayOfWeek || !$startTime || !$endTime || !$roomNumber
) {
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
    $sisId = $conn->insert_id;

    // Insert the schedule
    $stmt2 = $conn->prepare("
        INSERT INTO schedule (sis_id, day_of_week, start_time, end_time, room_number)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt2->bind_param("isssi", $sisId, $dayOfWeek, $startTime, $endTime, $roomNumber);
    $stmt2->execute();

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