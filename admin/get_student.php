<?php
require_once '../config.php';

// Set headers first to ensure proper JSON response
header('Content-Type: application/json');

try {
    $student_id = intval($_GET['student_id'] ?? 0);
    
    if ($student_id <= 0) {
        throw new Exception("Invalid student ID");
    }

    $query = $conn->prepare("
        SELECT si.*, ci.address, ci.phone_number, ci.email, ci.contact_id, 
               p.program_id, s.section_id 
        FROM student_information AS si
        INNER JOIN contact_information AS ci ON si.contact_id = ci.contact_id
        INNER JOIN program AS p ON si.program_id = p.program_id
        INNER JOIN section AS s ON si.section_id = s.section_id
        WHERE si.student_id = ?
    ");
    
    if (!$query) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    if (!$query->bind_param("i", $student_id)) {
        throw new Exception("Bind failed: " . $query->error);
    }
    
    if (!$query->execute()) {
        throw new Exception("Execute failed: " . $query->error);
    }
    
    $result = $query->get_result();
    
    if ($result->num_rows > 0) {
        $student = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => [
                'student_id' => $student['student_id'],
                'contact_id' => $student['contact_id'],
                'lastname' => $student['lastname'],
                'firstname' => $student['firstname'],
                'middle_name' => $student['middle_name'] ?? '',
                'birthdate' => $student['birthdate'],
                'sex' => $student['sex'],
                'address' => $student['address'],
                'phone_number' => $student['phone_number'],
                'email' => $student['email'],
                'program_id' => $student['program_id'],
                'section_id' => $student['section_id'],
                'year_level' => $student['year_level'],
                'current_semester' => $student['current_semester']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Student not found']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}