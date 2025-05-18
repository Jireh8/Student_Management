<?php
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

try {
    $instructor_id = intval($_GET['instructor_id'] ?? 0);

    if ($instructor_id <= 0) {
        throw new Exception("Invalid instructor ID");
    }

    $query = $conn->prepare("
        SELECT i.instructor_id, i.instructor_name, i.department_id, d.department_name,
               ci.contact_id, ci.address, ci.phone_number, ci.email
        FROM instructor AS i
        INNER JOIN department AS d ON i.department_id = d.department_id
        INNER JOIN contact_information AS ci ON i.contact_id = ci.contact_id
        WHERE i.instructor_id = ?
    ");

    if (!$query) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!$query->bind_param("i", $instructor_id)) {
        throw new Exception("Bind failed: " . $query->error);
    }

    if (!$query->execute()) {
        throw new Exception("Execute failed: " . $query->error);
    }

    $result = $query->get_result();

    if ($result->num_rows > 0) {
        $instructor = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'data' => [
                'instructor_id' => $instructor['instructor_id'],
                'instructor_name' => $instructor['instructor_name'],
                'department_id' => $instructor['department_id'],
                'department_name' => $instructor['department_name'],
                'contact_id' => $instructor['contact_id'],
                'address' => $instructor['address'],
                'phone_number' => $instructor['phone_number'],
                'email' => $instructor['email']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Instructor not found']);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}