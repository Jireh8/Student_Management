<?php
include '../config.php';

$instructorId = $_GET['instructor_id'] ?? null;
if (!$instructorId) {
    echo json_encode(['success' => false, 'message' => 'Missing instructor_id']);
    exit;
}

$stmt = $conn->prepare("
    SELECT 
        sis.sis_id,
        s.subject_name,
        sec.section_name,
        sch.day_of_week,
        sch.start_time,
        sch.end_time,
        sch.room_number
    FROM subject_instructor_section sis
    JOIN subject s ON sis.subject_id = s.subject_id
    JOIN section sec ON sis.section_id = sec.section_id
    LEFT JOIN schedule sch ON sis.sis_id = sch.sis_id
    WHERE sis.instructor_id = ?
");
$stmt->bind_param("i", $instructorId);
$stmt->execute();
$result = $stmt->get_result();

$assignments = [];
while ($row = $result->fetch_assoc()) {
    $assignments[] = $row;
}

echo json_encode(['success' => true, 'assignments' => $assignments]);
?>