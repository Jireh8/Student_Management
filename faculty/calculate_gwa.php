<?php
/**
 * Calculate and update GWA for a student for a given school year and semester.
 * @param mysqli $conn
 * @param int $student_id
 * @param string $school_year
 * @param string $semester
 * @return float|null The calculated GWA, or null if no grades found.
 */
function updateStudentGWA($conn, $student_id, $school_year, $semester) {
    // Calculate average of all final grades for this student, year, and semester
    $stmt = $conn->prepare("
        SELECT AVG(final_grade) AS gwa
        FROM student_grades
        WHERE student_id = ? AND school_year = ? AND semester = ? AND final_grade > 0
    ");
    $stmt->bind_param("iss", $student_id, $school_year, $semester);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $gwa = $row && $row['gwa'] !== null ? round($row['gwa'], 2) : null;

    if ($gwa !== null) {
        // Check if record exists in academic_records
        $check = $conn->prepare("SELECT 1 FROM academic_records WHERE student_id = ? AND school_year = ? AND semester = ?");
        $check->bind_param("iss", $student_id, $school_year, $semester);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;

        if ($exists) {
            // Update existing record
            $update = $conn->prepare("UPDATE academic_records SET gwa = ? WHERE student_id = ? AND school_year = ? AND semester = ?");
            $update->bind_param("diss", $gwa, $student_id, $school_year, $semester);
            $update->execute();
        } else {
            // Insert new record
            $insert = $conn->prepare("INSERT INTO academic_records (student_id, school_year, semester, gwa) VALUES (?, ?, ?, ?)");
            $insert->bind_param("issd", $student_id, $school_year, $semester, $gwa);
            $insert->execute();
        }
    }
    return $gwa;
}