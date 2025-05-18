<?php
require_once '../config.php';
header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $address = trim($_POST['address'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);

        $conn->begin_transaction();
        try {
            // Insert contact information
            $stmt = $conn->prepare("INSERT INTO contact_information (address, phone_number, email, contact_role, password) VALUES (?, ?, ?, 'Faculty', ?)");
            $stmt->bind_param("ssss", $address, $phone_number, $email, $password);
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert contact information: " . $stmt->error);
            }
            $contact_id = $stmt->insert_id;
            
            // Insert instructor
            $stmt2 = $conn->prepare("INSERT INTO instructor (department_id, contact_id, instructor_name) VALUES (?, ?, ?)");
            $stmt2->bind_param("iis", $department_id, $contact_id, $instructor_name);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to insert instructor: " . $stmt2->error);
            }
            $instructor_id = $stmt2->insert_id;

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Instructor added successfully.',
                'instructor_id' => $instructor_id
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;

    case 'edit':
        $instructor_id = intval($_POST['instructor_id'] ?? 0);
        $contact_id = intval($_POST['contact_id'] ?? 0);
        $instructor_name = trim($_POST['instructor_name'] ?? '');
        $department_id = intval($_POST['department_id'] ?? 0);
        $address = trim($_POST['address'] ?? '');
        $phone_number = trim($_POST['phone_number'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        $conn->begin_transaction();
        try {
            // Update contact info
            if ($password) {
                $stmt = $conn->prepare("UPDATE contact_information SET address=?, phone_number=?, email=?, password=? WHERE contact_id=?");
                $stmt->bind_param("ssssi", $address, $phone_number, $email, $password, $contact_id);
            } else {
                $stmt = $conn->prepare("UPDATE contact_information SET address=?, phone_number=?, email=? WHERE contact_id=?");
                $stmt->bind_param("sssi", $address, $phone_number, $email, $contact_id);
            }
            if (!$stmt->execute()) {
                throw new Exception("Failed to update contact information: " . $stmt->error);
            }

            // Update instructor info
            $stmt2 = $conn->prepare("UPDATE instructor SET department_id=?, instructor_name=? WHERE instructor_id=?");
            $stmt2->bind_param("isi", $department_id, $instructor_name, $instructor_id);
            if (!$stmt2->execute()) {
                throw new Exception("Failed to update instructor: " . $stmt2->error);
            }

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Instructor updated successfully.'
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;

    case 'delete':
        $instructor_id = intval($_POST['instructor_id'] ?? 0);

        $conn->begin_transaction();
        try {
            // Get contact_id first
            $stmt = $conn->prepare("SELECT contact_id FROM instructor WHERE instructor_id = ?");
            $stmt->bind_param("i", $instructor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $instructor = $result->fetch_assoc();
            $contact_id = $instructor['contact_id'];

            // Delete instructor
            $stmt1 = $conn->prepare("DELETE FROM instructor WHERE instructor_id = ?");
            $stmt1->bind_param("i", $instructor_id);
            $stmt1->execute();

            // Delete contact information
            $stmt2 = $conn->prepare("DELETE FROM contact_information WHERE contact_id = ?");
            $stmt2->bind_param("i", $contact_id);
            $stmt2->execute();

            $conn->commit();
            echo json_encode([
                'success' => true,
                'message' => 'Instructor deleted successfully.'
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}