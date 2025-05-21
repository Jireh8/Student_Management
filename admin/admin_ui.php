<?php 
    include '../config.php';
?>
<?php
function getAssignedSubjects($conn, $instructorId) {
    $query = $conn->prepare("
        SELECT s.subject_id, s.subject_name, s.subject_code, s.units, 
               sec.section_id, sec.section_name, sis.sis_id
        FROM subject_instructor_section sis
        JOIN subject s ON sis.subject_id = s.subject_id
        JOIN section sec ON sis.section_id = sec.section_id
        WHERE sis.instructor_id = ?
        ORDER BY s.subject_name
    ");
    $query->bind_param("i", $instructorId);
    $query->execute();
    $result = $query->get_result();
    
    if ($result->num_rows === 0) {
        return '<div class="no-subjects-message">
                <span class="material-icons">menu_book</span>
                <p>No subjects assigned yet</p>
                </div>';
    }
    
    $html = '<table class="assigned-subjects-table">
              <thead>
                <tr>
                  <th>Subject Code</th>
                  <th>Subject Name</th>
                  <th>Units</th>
                  <th>Section</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>';
    
    while ($row = $result->fetch_assoc()) {
        $html .= '<tr data-sis-id="'.$row['sis_id'].'">
                    <td>'.htmlspecialchars($row['subject_code']).'</td>
                    <td>'.htmlspecialchars($row['subject_name']).'</td>
                    <td>'.$row['units'].'</td>
                    <td>'.htmlspecialchars($row['section_name']).'</td>
                    <td>
                      <button class="btn-delete remove-subject-btn" 
                              data-sis-id="'.$row['sis_id'].'">
                        Remove
                      </button>
                    </td>
                  </tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function getAvailableSubjects($conn, $instructorId) {
    $query = $conn->prepare("
        SELECT s.subject_id, s.subject_name, s.subject_code
        FROM subject s
        WHERE s.subject_id NOT IN (
            SELECT subject_id 
            FROM subject_instructor_section 
            WHERE instructor_id = ?
        )
        ORDER BY s.subject_name
    ");
    $query->bind_param("i", $instructorId);
    $query->execute();
    $result = $query->get_result();
    
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="'.$row['subject_id'].'">
                     '.htmlspecialchars($row['subject_code'].' - '.$row['subject_name']).'
                     </option>';
    }
    
    return $options;
}

function getAllSections($conn) {
    $result = $conn->query("SELECT section_id, section_name FROM section ORDER BY section_name");
    
    $options = '';
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="'.$row['section_id'].'">
                     '.htmlspecialchars($row['section_name']).'
                     </option>';
    }
    
    return $options;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link rel="stylesheet" href="admin_ui.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <header id="univ-header">
        <!--<img src="university-logo.png" alt="University Logo" id="univ-logo"><-->
        <span id="univ-logo" class="material-icons">school</span>
        <h1 id="univ-name">Xydle University</h1>
    </header>

    <div id="main-content">
        <!-- tab bars -->
        <nav id="navbar" class="collapsed"> 
            <button id="toggle-nav"><span class="material-icons">menu</span></button>
            <ul>
                <li><a href="#school-calendar"><span class="material-icons">calendar_month</span>
                    <span class="nav-label"> School Calendar</span></a></li>
                <li><a href="#student-management"><span class="material-icons">manage_accounts</span>
                    <span class="nav-label"> Student Management</span></a></li>
                <li><a href="#instructor-management"><span class="material-icons">co_present</span>
                    <span class="nav-label"> Instructor Management</span></a></li>
                <li><a href="#subject-management"><span class="material-icons">menu_book</span>
                    <span class="nav-label"> Subject Management</span></a></li>
                <li>
                    <form action="logout.php" method="POST" id="logout-form">
                        <button type="submit"><span class="material-icons">logout</span>
                        <span class="nav-label"> Logout</span></button>
                    </form>
                </li>
            </ul>
        </nav>

        <div id="school-calendar" class="display-page">
            <div class="title-container">
                <h2 class = "title-style">School Calendar</h2>
            </div>
        </div>
        <!-- Student Management -->
        <div id="student-management" class="display-page">
                <div class="title-container">
                    <h2 class="title-style">Student Management</h2>
                    <button id="add-student" class="btn-style">+ Add Student</button>
                </div>
            <table class="table-style tr:hover">
                <thead>
                    <tr>
                        <th> Student ID</th>
                        <th> Student Name</th>
                        <th> Email</th>
                        <th> Program</th>
                        <th> Year</th>
                        <th> Section</th>
                        <th> Semester</th>
                        <th> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $studQuery = $conn->prepare("SELECT * FROM student_information AS si
                                INNER JOIN contact_information AS ci
                                    ON si.contact_id = ci.contact_id
                                INNER JOIN program AS p
                                    ON si.program_id = p.program_id
                                INNER JOIN section AS s
                                    ON si.section_id = s.section_id
                                ORDER BY si.year_level, si.program_id, si.section_id, si.lastname
                                ");
                        $studQuery->execute();
                        $studLists = $studQuery->get_result();

                        if($studLists->num_rows > 0) {
                            while($row = $studLists->fetch_assoc()) {
                                echo "<tr>";
                                    echo "<td>" . $row['student_id'] . "</td>";
                                    echo "<td>" . $row['lastname'] . ", " . $row['firstname'] . " " . $row['middle_name'] . "</td>";
                                    echo "<td>" . $row['email'] . "</td>";
                                    echo "<td>" . $row['program_name'] . "</td>";
                                    echo "<td>" . $row['year_level'] . "</td>";
                                    echo "<td>" . $row['section_name'] . "</td>";
                                    echo "<td>" . $row['current_semester'] . "</td>";
                                    echo "<td>";
                                        echo "<button class='btn-edit' id='edit-student'>Edit</button>";
                                        echo "<button class='btn-delete' id ='delete-student'>Delete</button>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr class="no-students-row">';
                                echo '<td colspan="8">';
                                    echo '<div class="empty-state">';
                                        echo '<span class="material-icons">school</span>';
                                        echo '<h3>No Students Found</h3>';
                                        echo '<p>There are currently no students in the database.</p>';
                                        echo '<button id="add-first-student" class="btn-style">Add First Student</button>';
                                    echo '</div>';
                                echo '</td>';
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- Instructor Management -->
        <div id="instructor-management" class="display-page">
            <div class="title-container">
                <h2 class = "title-style">Instructor Management</h2>
                <button id="add-student" class="btn-style">+ Add Instructor</button>
            </div>
            
            <table class="table-style tr:hover">
                <thead>
                    <tr>
                        <th> Instructor ID</th>
                        <th> Instructor Name</th>
                        <th> Email</th>
                        <th> Department</th>
                        <th> Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $instQuery = $conn->prepare("SELECT i.instructor_id, i.instructor_name, ci.email, d.department_name
                            FROM instructor AS i
                            INNER JOIN contact_information AS ci ON i.contact_id = ci.contact_id
                            INNER JOIN department AS d ON i.department_id = d.department_id
                            ORDER BY i.instructor_name
                        ");
                        $instQuery->execute();
                        $instLists = $instQuery->get_result();

                        if ($instLists->num_rows > 0) {
                            while ($row = $instLists->fetch_assoc()) {
                                echo "<tr>";
                                    echo "<td>" . $row['instructor_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['instructor_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['department_name']) . "</td>";
                                    echo "<td>";
                                        echo "<button class='btn-edit' id='edit-instructor'>Edit</button>";
                                        echo "<button class='btn-delete' id='delete-instructor'>Delete</button>";
                                    echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo '<tr class="no-instructors-row">';
                                echo '<td colspan="5">';
                                    echo '<div class="empty-state">';
                                        echo '<span class="material-icons">co_present</span>';
                                        echo '<h3>No Instructors Found</h3>';
                                        echo '<p>There are currently no instructors in the database.</p>';
                                        echo '<button id="add-first-instructor" class="btn-style">Add First Instructor</button>';
                                    echo '</div>';
                                echo '</td>';
                            echo '</tr>';
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <!-- Subject Management -->
        <div id="subject-management" class="display-page">
            <div class="title-container">
                <h2 class="title-style">Subject Assigning Management</h2>
            </div>
            
            <div class="instructor-cards-container">
                <?php
                // Get all instructors with their subjects and department
                $instructorsQuery = $conn->query("
                    SELECT 
                        i.instructor_id, 
                        i.instructor_name, 
                        d.department_name,
                        GROUP_CONCAT(DISTINCT s.subject_name ORDER BY s.subject_name SEPARATOR ',') as subjects,
                        COUNT(DISTINCT sis.sis_id) as subject_count
                    FROM instructor i
                    JOIN department d ON i.department_id = d.department_id
                    LEFT JOIN subject_instructor_section sis ON i.instructor_id = sis.instructor_id
                    LEFT JOIN subject s ON sis.subject_id = s.subject_id
                    GROUP BY i.instructor_id
                    ORDER BY i.instructor_name
                ");
                
                if ($instructorsQuery->num_rows > 0) {
                    while ($instructor = $instructorsQuery->fetch_assoc()) {
                        echo '<div class="instructor-card">
                                <div class="instructor-header">
                                    <h3>'.htmlspecialchars($instructor['instructor_name']).'</h3>
                                    <span class="department-badge">'.$instructor['department_name'].'</span>
                                </div>
                                
                                <div class="instructor-details">
                                    <div class="detail-row">
                                        <span class="detail-label">Subjects Assigned:</span>
                                        <span class="detail-value">'.$instructor['subject_count'].'</span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <span class="detail-label">Subjects:</span>
                                        <span class="detail-value">'.formatList($instructor['subjects'] ?: 'No subjects assigned').'</span>
                                    </div>
                                </div>
                                
                                <div class="instructor-actions">
                                    <button class="btn-manage manage-btn" data-instructor-id="'.$instructor['instructor_id'].'">
                                        Manage Subjects
                                    </button>
                                </div>
                            </div>';
                    }
                } else {
                    echo '<div class="no-instructors-message">
                            <span class="material-icons">co_present</span>
                            <h3>No Instructors Found</h3>
                            <p>There are currently no instructors in the database.</p>
                        </div>';
                }
                
                // Helper function to format comma-separated lists
                function formatList($items) {
                    if (strpos($items, ',') === false) return $items;
                    return '<ul><li>'.str_replace(',', '</li><li>', $items).'</li></ul>';
                }
                ?>
            </div>
            
            <!-- Assign Subjects Modal -->
            <div id="assign-subjects-modal" class="modal" style="display:none;">
                <div class="modal-content" style="width: 80%; max-width: 600px;">
                    <span class="close" id="close-assign-modal">&times;</span>
                    <h2>Manage Subjects for <span id="modal-instructor-name"></span></h2>
                    
                    <div class="assign-form-container">
                        <form id="assign-subjects-form">
                            <input type="hidden" id="modal-instructor-id" name="instructor_id">
                            
                            <div class="form-group">
                                <div>
                                    <label for="subject-select">Select Subject:</label>
                                    <select id="subject-select" name="subject_id" class="subject-select" required>
                                </div>
                                <option value="">Choose a subject</option>
                                    <?php
                                    // Join subject with program_subject to get year_offered and semester_offered
                                    $subjects = $conn->query("
                                        SELECT s.subject_id, s.subject_name, s.subject_code, 
                                               ps.year_offered, ps.semester_offered
                                        FROM subject s
                                        LEFT JOIN program_subject ps ON s.subject_id = ps.subject_id
                                        ORDER BY s.subject_name
                                    ");
                                    while ($subject = $subjects->fetch_assoc()) {
                                        $year = $subject['year_offered'] ? "Year: " . htmlspecialchars($subject['year_offered']) : "Year: N/A";
                                        $sem = $subject['semester_offered'] ? "Sem: " . htmlspecialchars($subject['semester_offered']) : "Sem: N/A";
                                        echo '<option value="'.$subject['subject_id'].'">'.
                                            htmlspecialchars($subject['subject_code'].' - '.$subject['subject_name'])." ($year, $sem)".
                                            '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="section-select">Select Section:</label>
                                <select id="section-select" name="section_id" class="section-select" required>
                                    <option value="">Choose a section</option>
                                    <?php
                                    $sections = $conn->query("SELECT section_id, section_name FROM section ORDER BY section_name");
                                    while ($section = $sections->fetch_assoc()) {
                                        echo '<option value="'.$section['section_id'].'">'.
                                            htmlspecialchars($section['section_name']).
                                            '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="day_of_week">Day of Week:</label>
                                <select id="day_of_week" name="day_of_week" required>
                                    <option value="">Select Day</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="start_time">Start Time:</label>
                                <input type="time" id="start_time" name="start_time" required>
                            </div>
                            <div class="form-group">
                                <label for="end_time">End Time:</label>
                                <input type="time" id="end_time" name="end_time" required>
                            </div>
                            <div class="form-group">
                                <label for="room_number">Room Number:</label>
                                <input type="number" id="room_number" name="room_number" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-manage">Assign Subject</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="current-assignments" id="current-assignments">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODALS SECTION -->
    <!-- Student Modals -->
        <!-- add student modal -->
        <div id="add-student-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="close-add-student">&times;</span>
                <h2>Add Student</h2>
                <form id="add-student-form" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-style">
                        <label for="lastname" style="grid-row:1;grid-column:1">Last Name*</label>
                        <input type="text" id="lastname" name="lastname" required>
                    </div>
                    <div class="form-style">
                        <label for="firstname" style="grid-row:2;grid-column:1">First Name*</label>
                        <input type="text" id="firstname" name="firstname" required>
                    </div>
                    <div class="form-style" style="grid-row:3;grid-column:1">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name">
                    </div>
                    <div class="form-style" style="grid-row:4;grid-column:1">
                        <label for="birthdate">Birthdate*</label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                    <div class="form-style" style ="grid-row:5;grid-column:1">
                        <label for="sex">Sex*</label>
                        <select id="sex" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:1">
                        <label for="address">Address*</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="form-style" style="grid-row:7;grid-column:1">
                        <label for="phone_number">Phone Number*</label>
                        <input type="text" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="form-style" style="grid-row:1;grid-column:2">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-style" style="grid-row:2;grid-column:2">
                        <label for="password">Password*</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="form-style" style="grid-row:3;grid-column:2">
                        <label for="program_id">Program*</label>
                        <select id="program_id" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php
                                $progQuery = $conn->query("SELECT program_id, program_name FROM program ORDER BY program_name");
                                while($prog = $progQuery->fetch_assoc()) {
                                    echo "<option value='{$prog['program_id']}'>{$prog['program_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:4;grid-column:2">
                        <label for="section_id">Section*</label>
                        <select id="section_id" name="section_id" required>
                            <option value="">Select Section</option>
                            <?php
                                $secQuery = $conn->query("SELECT section_id, section_name FROM section ORDER BY section_name");
                                while($sec = $secQuery->fetch_assoc()) {
                                    echo "<option value='{$sec['section_id']}'>{$sec['section_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:5;grid-column:2">
                        <label for="year_level">Year Level*</label>
                        <select id="year_level" name="year_level" required>
                            <option value="">Select Year</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:2">
                        <label for="current_semester">Semester*</label>
                        <select id="current_semester" name="current_semester" required>
                            <option value="">Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-style">Add Student</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- edit student modal -->
        <div id="edit-student-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="close-edit-student">&times;</span>
                <h2>Edit Student</h2>
                <form id="edit-student-form" action="student_mngmnt.php" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit-student-id" name="student_id">
                    <input type="hidden" id="edit-contact-id" name="contact_id">
                    <div class="form-style">
                        <label for="edit-lastname" style="grid-row:1;grid-column:1">Last Name*</label>
                        <input type="text" id="edit-lastname" name="lastname" required>
                    </div>
                    <div class="form-style">
                        <label for="edit-firstname" style="grid-row:2;grid-column:1">First Name*</label>
                        <input type="text" id="edit-firstname" name="firstname" required>
                    </div>
                    <div class="form-style" style="grid-row:3;grid-column:1">
                        <label for="edit-middle_name">Middle Name</label>
                        <input type="text" id="edit-middle_name" name="middle_name">
                    </div>
                    <div class="form-style" style="grid-row:4;grid-column:1">
                        <label for="edit-birthdate">Birthdate*</label>
                        <input type="date" id="edit-birthdate" name="birthdate" required>
                    </div>
                    <div class="form-style" style ="grid-row:5;grid-column:1">
                        <label for="sex">Sex*</label>
                        <select id="edit-sex" name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:1">
                        <label for="edit-address">Address*</label>
                        <input type="text" id="edit-address" name="address" required>
                    </div>
                    <div class="form-style" style="grid-row:7;grid-column:1">
                        <label for="edit-phone_number">Phone Number*</label>
                        <input type="text" id="edit-phone_number" name="phone_number" required>
                    </div>
                    <div class="form-style" style="grid-row:1;grid-column:2">
                        <label for="edit-email">Email*</label>
                        <input type="email" id="edit-email" name="email" required>
                    </div>
                    <div class="form-style" style="grid-row:2;grid-column:2">
                        <label for="edit-password">Password (leave blank to keep current)</label>
                        <input type="password" id="edit-password" name="password">
                    </div>
                    <div class="form-style" style="grid-row:3;grid-column:2">
                        <label for="edit-program_id">Program*</label>
                        <select id="edit-program_id" name="program_id" required>
                            <option value="">Select Program</option>
                            <?php
                                $progQuery = $conn->query("SELECT program_id, program_name FROM program ORDER BY program_name");
                                while($prog = $progQuery->fetch_assoc()) {
                                    echo "<option value='{$prog['program_id']}'>{$prog['program_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:4;grid-column:2">
                        <label for="edit-section_id">Section*</label>
                        <select id="edit-section_id" name="section_id" required>
                            <option value="">Select Section</option>
                            <?php
                                $secQuery = $conn->query("SELECT section_id, section_name FROM section ORDER BY section_name");
                                while($sec = $secQuery->fetch_assoc()) {
                                    echo "<option value='{$sec['section_id']}'>{$sec['section_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:5;grid-column:2">
                        <label for="edit-year_level">Year Level*</label>
                        <select id="edit-year_level" name="year_level" required>
                            <option value="">Select Year</option>
                            <option value="1st">1st Year</option>
                            <option value="2nd">2nd Year</option>
                            <option value="3rd">3rd Year</option>
                            <option value="4th">4th Year</option>
                        </select>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:2">
                        <label for="edit-current_semester">Semester*</label>
                        <select id="edit-current_semester" name="current_semester" required>
                            <option value="">Select Semester</option>
                            <option value="1st">1st Semester</option>
                            <option value="2nd">2nd Semester</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-style">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- delete student modal -->
        <div id="delete-student-modal" class="modal" style="display:none;">
            <div class="modal-content delete-student-modal">
                <span class="close" id="close-delete-student">&times;</span>
                <h2>Delete Student</h2>
                <p>Are you sure you want to delete this student?</p>
                <button id="confirm-delete" class="btn-delete">Delete</button>
                <button id="cancel-delete" class="btn-style">Cancel</button>
            </div>
        </div>
    <!-- Instructor Modals -->
        <!-- add instructor modal -->
        <div id="add-instructor-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="close-add-instructor">&times;</span>
                <h2>Add Instructor</h2>
                <form id="add-instructor-form" method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="form-style">
                        <label for="instructor_name" style="grid-row:1;grid-column:1">Instructor Name*</label>
                        <input type="text" id="instructor_name" name="instructor_name" required>   
                    </div>
                    <div class="form-style">
                        <label for="instructor_email" style="grid-row:2;grid-column:1">Email*</label>
                        <input type="email" id="instructor_email" name="email" required>
                    </div>
                    <div class="form-style" style="grid-row:3;grid-column:1">
                        <label for="instructor_department">Department*</label>
                        <select id="instructor_department" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php
                                $deptQuery = $conn->query("SELECT department_id, department_name FROM department ORDER BY department_name");
                                while($dept = $deptQuery->fetch_assoc()) {
                                    echo "<option value='{$dept['department_id']}'>{$dept['department_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style">
                        <label for="instructor_phone_number" style="grid-row:4;grid-column:1">Phone Number*</label>
                        <input type="text" id="instructor_phone_number" name="phone_number" required>
                    </div>
                    <div class="form-style">
                        <label for="instructor_password" style="grid-row:5;grid-column:1">Password*</label>
                        <input type="password" id="instructor_password" name="password" required>
                    </div>
                    <div class="form-style">
                        <label for="instructor_address" style="grid-row:6;grid-column:1">Address*</label>
                        <input type="text" id="instructor_address" name="address" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-style">Add Instructor</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- edit instructor modal -->
        <div id="edit-instructor-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="close-edit-instructor">&times;</span>
                <h2>Edit Instructor</h2>
                <form id="edit-instructor-form" method="POST">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit-instructor-id" name="instructor_id">
                    <div class="form-style  ">
                        <label for="edit-instructor_name" style="grid-row:1;grid-column:1">Instructor Name*</label>
                        <input type="text" id="edit-instructor_name" name="instructor_name" required>
                    </div>
                    <div class="form-style  ">
                        <label for="edit-instructor_email" style="grid-row:2;grid-column:1">Email*</label>
                        <input type="email" id="edit-instructor_email" name="email" required>
                    </div>
                    <div class="form-style  ">
                        <label for="edit-instructor_department">Department*</label>
                        <select id="edit-instructor_department" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php
                                $deptQuery = $conn->query("SELECT department_id, department_name FROM department ORDER BY department_name");
                                while($dept = $deptQuery->fetch_assoc()) {
                                    echo "<option value='{$dept['department_id']}'>{$dept['department_name']}</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-style  ">
                        <label for="edit-instructor_phone_number" style="grid-row:4;grid-column:1">Phone Number*</label>
                        <input type="text" id="edit-instructor_phone_number" name="phone_number" required>
                    </div>
                    <div class="form-style  ">
                        <label for="edit-instructor_password" style="grid-row:5;grid-column:1">Password (leave blank to keep current)</label>
                        <input type="password" id="edit-instructor_password" name="password">
                    </div>
                    <div class="form-style  ">
                        <label for="edit-instructor_address" style="grid-row:6;grid-column:1">Address*</label>
                        <input type="text" id="edit-instructor_address" name="address" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-style">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
        <!-- delete instructor modal -->
        <div id="delete-instructor-modal" class="modal" style="display:none;">
            <div class="modal-content delete-instructor-modal">
                <span class="close" id="close-delete-instructor">&times;</span>
                <h2>Delete Instructor</h2>
                <p>Are you sure you want to delete this instructor?</p>
                <button id="confirm-delete-instructor" class="btn-delete">Delete</button>
                <button id="cancel-delete-instructor" class="btn-style">Cancel</button>
            </div>
        </div>
        

        
    
    <script>
        // Toggle navigation bar
                const toggleBtn = document.getElementById('toggle-nav');
                const navbar = document.getElementById('navbar');

                toggleBtn.addEventListener('click', () => {
                    navbar.classList.toggle('expanded');
                    navbar.classList.toggle('collapsed');
                });

                // page navigation logic
                const navLinks = document.querySelectorAll('#navbar a');
                const pages = document.querySelectorAll('.display-page');

                function showPage(id) {
                    // hide all pages
                    pages.forEach(page => page.style.display = 'none');

                    // selected page
                    const target = document.getElementById(id);
                    if (target) target.style.display = 'flex';

                    // Highlight active nav
                    navLinks.forEach(link => link.classList.remove('active'));
                    const activeLink = document.querySelector(`#navbar a[href="#${id}"]`);
                    if (activeLink) activeLink.classList.add('active');
                }

                // clicks
                navLinks.forEach(link => {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetId = link.getAttribute('href').substring(1);
                        showPage(targetId);
                    });
                });

        //student management
            // add student button
            
                const addStudentBtn = document.getElementById('add-student');
                const addStudentModal = document.getElementById('add-student-modal');
                const closeAddStudent = document.getElementById('close-add-student');

                addStudentBtn.addEventListener('click', () => {
                    addStudentModal.style.display = 'block';
                });

                closeAddStudent.addEventListener('click', () => {
                    addStudentModal.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target == addStudentModal) {
                        addStudentModal.style.display = 'none';
                    }
                });
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'add-first-student') {
                    document.getElementById('add-student-modal').style.display = 'block';
                }
            });
            document.getElementById('add-student-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const form = e.target;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                
                // Show loading state
                submitBtn.disabled = true;
                submitBtn.textContent = 'Adding Student...';
                
                try {
                    const response = await fetch('student_mngmnt.php', {
                        method: 'POST',
                        body: new FormData(form)
                    });
                    
                    const result = await response.text();
                    
                    if (response.ok) {
                        alert(result);
                        form.reset();
                        document.getElementById('add-student-modal').style.display = 'none';
                        location.reload(); // Refresh the page
                    } else {
                        throw new Error(result || 'Failed to add student');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error: ' + error.message);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });
            // delete student button
                const deleteStudentBtn = document.getElementById('delete-student');
                const deleteStudentModal = document.getElementById('delete-student-modal');
                const closeDeleteStudent = document.getElementById('close-delete-student');

                deleteStudentBtn.addEventListener('click', () => {
                    deleteStudentModal.style.display = 'block';
                });

                closeDeleteStudent.addEventListener('click', () => {
                    deleteStudentModal.style.display = 'none';
                });

                window.addEventListener('click', (event) => {
                    if (event.target == deleteStudentModal) {
                        deleteStudentModal.style.display = 'none';
                    }
                });
            // Edit student modal - only for buttons with id="edit-student"
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'edit-student') {
                    const row = e.target.closest('tr');
                    const studentId = row.cells[0].textContent;
                    console.log(`Fetching student info for ID: ${studentId}`);
                    
                    // Show loading state
                    const modal = document.getElementById('edit-student-modal');
                    modal.style.display = 'block';
                    modal.querySelector('.modal-content').innerHTML = '<div style="padding:20px;text-align:center;">Loading student data...</div>';
                    
                    // Fetch student data
                    fetchStudentData(studentId);
                }
            });

            // Function to fetch student data
            function fetchStudentData(studentId) {
                fetch(`get_student.php?student_id=${studentId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            populateEditForm(data.data);
                        } else {
                            alert(data.message);
                            closeModal('edit-student-modal');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error fetching student data: ' + error.message);
                        closeModal('edit-student-modal');
                    });
            }

            // Function to generate program options
            function generateProgramOptions(selectedId) {
                //get program options from the DOM 
                const options = [];
                const programSelect = document.getElementById('program_id');
                if (programSelect) {
                    Array.from(programSelect.options).forEach(option => {
                        if (option.value) {
                            const selected = parseInt(option.value) === parseInt(selectedId) ? 'selected' : '';
                            options.push(`<option value="${option.value}" ${selected}>${option.text}</option>`);
                        }
                    });
                }
                return options.join('');
            }

            // Function to generate section options
            function generateSectionOptions(selectedId) {
                // We'll get section options from the DOM since they're already available
                const options = [];
                const sectionSelect = document.getElementById('section_id');
                if (sectionSelect) {
                    Array.from(sectionSelect.options).forEach(option => {
                        if (option.value) {
                            const selected = parseInt(option.value) === parseInt(selectedId) ? 'selected' : '';
                            options.push(`<option value="${option.value}" ${selected}>${option.text}</option>`);
                        }
                    });
                }
                return options.join('');
            }

            // Function to populate edit form with all fields
            function populateEditForm(studentData) {
                const modal = document.getElementById('edit-student-modal');
                
                // Create form HTML with all fields
                modal.querySelector('.modal-content').innerHTML = `
                    <span class="close" id="close-edit-student">&times;</span>
                    <h2>Edit Student</h2>
                    <form id="edit-student-form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit-student-id" name="student_id" value="${studentData.student_id}">
                        <input type="hidden" id="edit-contact-id" name="contact_id" value="${studentData.contact_id}">
                        
                        <div class="form-style" style="grid-row:1;grid-column:1">
                            <label for="edit-lastname">Last Name*</label>
                            <input type="text" id="edit-lastname" name="lastname" value="${studentData.lastname}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:2;grid-column:1">
                            <label for="edit-firstname">First Name*</label>
                            <input type="text" id="edit-firstname" name="firstname" value="${studentData.firstname}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:3;grid-column:1">
                            <label for="edit-middle_name">Middle Name</label>
                            <input type="text" id="edit-middle_name" name="middle_name" value="${studentData.middle_name || ''}">
                        </div>
                        
                        <div class="form-style" style="grid-row:4;grid-column:1">
                            <label for="edit-birthdate">Birthdate*</label>
                            <input type="date" id="edit-birthdate" name="birthdate" value="${studentData.birthdate}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:5;grid-column:1">
                            <label for="edit-sex">Sex*</label>
                            <select id="edit-sex" name="sex" required>
                                <option value="">Select Sex</option>
                                <option value="Male" ${studentData.sex === 'Male' ? 'selected' : ''}>Male</option>
                                <option value="Female" ${studentData.sex === 'Female' ? 'selected' : ''}>Female</option>
                            </select>
                        </div>
                        
                        <div class="form-style" style="grid-row:6;grid-column:1">
                            <label for="edit-address">Address*</label>
                            <input type="text" id="edit-address" name="address" value="${studentData.address}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:7;grid-column:1">
                            <label for="edit-phone_number">Phone Number*</label>
                            <input type="text" id="edit-phone_number" name="phone_number" value="${studentData.phone_number}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:1;grid-column:2">
                            <label for="edit-email">Email*</label>
                            <input type="email" id="edit-email" name="email" value="${studentData.email}" required>
                        </div>
                        
                        <div class="form-style" style="grid-row:2;grid-column:2">
                            <label for="edit-password">Password (leave blank to keep current)</label>
                            <input type="password" id="edit-password" name="password">
                        </div>
                        
                        <div class="form-style" style="grid-row:3;grid-column:2">
                            <label for="edit-program_id">Program*</label>
                            <select id="edit-program_id" name="program_id" required>
                                <option value="">Select Program</option>
                                ${generateProgramOptions(studentData.program_id)}
                            </select>
                        </div>
                        
                        <div class="form-style" style="grid-row:4;grid-column:2">
                            <label for="edit-section_id">Section*</label>
                            <select id="edit-section_id" name="section_id" required>
                                <option value="">Select Section</option>
                                ${generateSectionOptions(studentData.section_id)}
                            </select>
                        </div>
                        
                        <div class="form-style" style="grid-row:5;grid-column:2">
                            <label for="edit-year_level">Year Level*</label>
                            <select id="edit-year_level" name="year_level" required>
                                <option value="">Select Year</option>
                                <option value="1st" ${studentData.year_level == '1st' ? 'selected' : ''}>1st Year</option>
                                <option value="2nd" ${studentData.year_level == '2nd' ? 'selected' : ''}>2nd Year</option>
                                <option value="3rd" ${studentData.year_level == '3rd' ? 'selected' : ''}>3rd Year</option>
                                <option value="4th" ${studentData.year_level == '4th' ? 'selected' : ''}>4th Year</option>
                            </select>
                        </div>
                        
                        <div class="form-style" style="grid-row:6;grid-column:2">
                            <label for="edit-current_semester">Semester*</label>
                            <select id="edit-current_semester" name="current_semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st" ${studentData.current_semester === '1st' ? 'selected' : ''}>1st Semester</option>
                                <option value="2nd" ${studentData.current_semester === '2nd' ? 'selected' : ''}>2nd Semester</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn-style">Save Changes</button>
                            <button type="button" class="btn-style" id="cancel-edit">Cancel</button>
                        </div>
                    </form>
                `;
                
                // Set up close button
                document.getElementById('close-edit-student').addEventListener('click', () => {
                    closeModal('edit-student-modal');
                });
                
                // Set up cancel button
                document.getElementById('cancel-edit').addEventListener('click', () => {
                    closeModal('edit-student-modal');
                });
                
                // Set up form submission
                document.getElementById('edit-student-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitEditForm(this);
                });
            }

            // Function to submit edit form
            function submitEditForm(form) {
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
                
                fetch('student_mngmnt.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Student updated successfully');
                    location.reload();
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            }

            // Helper function to close modal
            function closeModal(modalId) {
                document.getElementById(modalId).style.display = 'none';
            }
            window.addEventListener('click', function(event) {
                const editModal = document.getElementById('edit-student-modal');
                if (event.target === editModal) {
                closeModal('edit-student-modal');
                }
            });
            
            // delete student modal - updated to handle confirmation
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'delete-student') {
                    const row = e.target.closest('tr');
                    const studentId = row.cells[0].textContent;
                    const studentName = row.cells[1].textContent;

                    // Set student ID for deletion
                    document.getElementById('confirm-delete').dataset.studentId = studentId;

                    // Update confirmation message
                    document.querySelector('#delete-student-modal p').textContent =
                        `Are you sure you want to delete ${studentName} (ID: ${studentId})?`;

                    // Show modal
                    document.getElementById('delete-student-modal').style.display = 'block';
                }
            });

            // Handle delete confirmation
            document.getElementById('confirm-delete').addEventListener('click', function() {
                const studentId = this.dataset.studentId;
                
                fetch('student_mngmnt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&student_id=${studentId}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload(); // Refresh the page
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting student');
                });
            });

            // Cancel delete
            document.getElementById('cancel-delete').addEventListener('click', function() {
            document.getElementById('delete-student-modal').style.display = 'none';
            });
        
        //instructor management
            // Add Instructor Modal
            const addInstructorBtn = document.querySelector('#instructor-management #add-student');
            const addInstructorModal = document.getElementById('add-instructor-modal');
            const closeAddInstructor = document.getElementById('close-add-instructor');

            addInstructorBtn.addEventListener('click', () => {
                addInstructorModal.style.display = 'block';
            });

            closeAddInstructor.addEventListener('click', () => {
                addInstructorModal.style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target == addInstructorModal) {
                    addInstructorModal.style.display = 'none';
                }
            });

            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'add-first-instructor') {
                    document.getElementById('add-instructor-modal').style.display = 'block';
                }
            });

            document.getElementById('add-instructor-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = e.target;
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Adding Instructor...';
                try {
                    const response = await fetch('instructor_mngmnt.php', {
                        method: 'POST',
                        body: new FormData(form)
                    });
                    const result = await response.text();
                    if (response.ok) {
                        alert(result);
                        form.reset();
                        addInstructorModal.style.display = 'none';
                        location.reload();
                    } else {
                        throw new Error(result || 'Failed to add instructor');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalBtnText;
                }
            });

            // Edit Instructor Modal
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'edit-instructor') {
                    const row = e.target.closest('tr');
                    const instructorId = row.cells[0].textContent;
                    // Show loading state
                    const modal = document.getElementById('edit-instructor-modal');
                    modal.style.display = 'block';
                    modal.querySelector('.modal-content').innerHTML = '<div style="padding:20px;text-align:center;">Loading instructor data...</div>';
                    fetchInstructorData(instructorId);
                }
            });

            function fetchInstructorData(instructorId) {
                fetch(`get_instructor.php?instructor_id=${instructorId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            populateEditInstructorForm(data.data);
                        } else {
                            alert(data.message);
                            closeModal('edit-instructor-modal');
                        }
                    })
                    .catch(error => {
                        alert('Error fetching instructor data: ' + error.message);
                        closeModal('edit-instructor-modal');
                    });
            }

            function generateDepartmentOptions(selectedId) {
                const options = [];
                const deptSelect = document.getElementById('instructor_department');
                if (deptSelect) {
                    Array.from(deptSelect.options).forEach(option => {
                        if (option.value) {
                            const selected = parseInt(option.value) === parseInt(selectedId) ? 'selected' : '';
                            options.push(`<option value="${option.value}" ${selected}>${option.text}</option>`);
                        }
                    });
                }
                return options.join('');
            }

            function populateEditInstructorForm(data) {
                const modal = document.getElementById('edit-instructor-modal');
                modal.querySelector('.modal-content').innerHTML = `
                    <span class="close" id="close-edit-instructor">&times;</span>
                    <h2>Edit Instructor</h2>
                    <form id="edit-instructor-form">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" id="edit-instructor-id" name="instructor_id" value="${data.instructor_id}">
                        <div class="form-style">
                            <label for="edit-instructor_name" style="grid-row:1;grid-column:1">Instructor Name*</label>
                            <input type="text" id="edit-instructor_name" name="instructor_name" value="${data.instructor_name}" required>
                        </div>
                        <div class="form-style">
                            <label for="edit-instructor_email" style="grid-row:2;grid-column:1">Email*</label>
                            <input type="email" id="edit-instructor_email" name="email" value="${data.email}" required>
                        </div>
                        <div class="form-style">
                            <label for="edit-instructor_department">Department*</label>
                            <select id="edit-instructor_department" name="department_id" required>
                                <option value="">Select Department</option>
                                ${generateDepartmentOptions(data.department_id)}
                            </select>
                        </div>
                        <div class="form-style">
                            <label for="edit-instructor_phone_number" style="grid-row:4;grid-column:1">Phone Number*</label>
                            <input type="text" id="edit-instructor_phone_number" name="phone_number" value="${data.phone_number}" required>
                        </div>
                        <div class="form-style">
                            <label for="edit-instructor_password" style="grid-row:5;grid-column:1">Password (leave blank to keep current)</label>
                            <input type="password" id="edit-instructor_password" name="password">
                        </div>
                        <div class="form-style">
                            <label for="edit-instructor_address" style="grid-row:6;grid-column:1">Address*</label>
                            <input type="text" id="edit-instructor_address" name="address" value="${data.address}" required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-style">Save Changes</button>
                            <button type="button" class="btn-style" id="cancel-edit-instructor">Cancel</button>
                        </div>
                    </form>
                `;
                document.getElementById('close-edit-instructor').addEventListener('click', () => {
                    closeModal('edit-instructor-modal');
                });
                document.getElementById('cancel-edit-instructor').addEventListener('click', () => {
                    closeModal('edit-instructor-modal');
                });
                document.getElementById('edit-instructor-form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitEditInstructorForm(this);
                });
            }

            function submitEditInstructorForm(form) {
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';
                fetch('instructor_mngmnt.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    alert('Instructor updated successfully');
                    location.reload();
                })
                .catch(error => {
                    alert('Error: ' + error.message);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            }

            window.addEventListener('click', function(event) {
                const editModal = document.getElementById('edit-instructor-modal');
                if (event.target === editModal) {
                    closeModal('edit-instructor-modal');
                }
            });

            // Delete Instructor Modal
            document.addEventListener('click', function(e) {
                if (e.target && e.target.id === 'delete-instructor') {
                    const row = e.target.closest('tr');
                    const instructorId = row.cells[0].textContent;
                    const instructorName = row.cells[1].textContent;
                    document.getElementById('confirm-delete-instructor').dataset.instructorId = instructorId;
                    document.querySelector('#delete-instructor-modal p').textContent =
                        `Are you sure you want to delete ${instructorName} (ID: ${instructorId})?`;
                    document.getElementById('delete-instructor-modal').style.display = 'block';
                }
            });

            document.getElementById('confirm-delete-instructor').addEventListener('click', function() {
                const instructorId = this.dataset.instructorId;
                fetch('instructor_mngmnt.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=delete&instructor_id=${instructorId}`
                })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                })
                .catch(error => {
                    alert('Error deleting instructor');
                });
            });

            document.getElementById('cancel-delete-instructor').addEventListener('click', function() {
                document.getElementById('delete-instructor-modal').style.display = 'none';
            });

        // subject Management
        // Instructor Cards Management System
            document.addEventListener('DOMContentLoaded', function() {
                // DOM Elements
                const assignModal = document.getElementById('assign-subjects-modal');
                const closeAssignModal = document.getElementById('close-assign-modal');
                const assignForm = document.getElementById('assign-subjects-form');
                const currentAssignments = document.getElementById('current-assignments');
                const modalInstructorName = document.getElementById('modal-instructor-name');
                const modalInstructorId = document.getElementById('modal-instructor-id');

                // Event Listeners
                document.addEventListener('click', function(e) {
                    // Manage button click
                    if (e.target.classList.contains('manage-btn')) {
                        const card = e.target.closest('.instructor-card');
                        const instructorId = e.target.dataset.instructorId;
                        const instructorName = card.querySelector('h3').textContent;

                        // Set modal content
                        modalInstructorId.value = instructorId;
                        modalInstructorName.textContent = instructorName;

                        // Load current assignments
                        loadCurrentAssignments(instructorId);

                        // Show modal
                        assignModal.style.display = 'block';
                    }
                    
                    // Remove assignment button click
                    if (e.target.classList.contains('remove-assignment-btn')) {
                        const sisId = e.target.dataset.sisId;
                        const instructorId = modalInstructorId.value;
                        
                        if (confirm('Are you sure you want to remove this assignment?')) {
                            removeAssignment(sisId, instructorId);
                        }
                    }
                });

                // Close modal
                closeAssignModal.addEventListener('click', function() {
                    assignModal.style.display = 'none';
                });

                // Close modal when clicking outside
                window.addEventListener('click', function(e) {
                    if (e.target === assignModal) {
                        assignModal.style.display = 'none';
                    }
                });

                // Form submission
                assignForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const instructorId = modalInstructorId.value;
                    const subjectId = document.getElementById('subject-select').value;
                    const sectionId = document.getElementById('section-select').value;
                    const dayOfWeek = document.getElementById('day_of_week').value;
                    const startTime = document.getElementById('start_time').value;
                    const endTime = document.getElementById('end_time').value;
                    const roomNumber = document.getElementById('room_number').value;
                    const submitBtn = this.querySelector('button[type="submit"]');

                    if (!subjectId || !sectionId || !dayOfWeek || !startTime || !endTime || !roomNumber) {
                        alert('Please fill in all fields');
                        return;
                    }

                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Assigning...';

                    fetch('assign_subject.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `instructor_id=${instructorId}&subject_id=${subjectId}&section_id=${sectionId}` +
                            `&day_of_week=${encodeURIComponent(dayOfWeek)}` +
                            `&start_time=${encodeURIComponent(startTime)}` +
                            `&end_time=${encodeURIComponent(endTime)}` +
                            `&room_number=${encodeURIComponent(roomNumber)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            loadCurrentAssignments(instructorId);
                            assignForm.reset();
                            alert('Subject and schedule assigned successfully');
                        } else {
                            throw new Error(data.message || 'Failed to assign subject');
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Assign Subject';
                    });
                });
                // Load current assignments
                function loadCurrentAssignments(instructorId) {
                    currentAssignments.innerHTML = '<div class="loading-message">Loading current assignments...</div>';

                    fetch(`get_instructor_assignments.php?instructor_id=${instructorId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.assignments.length > 0) {
                                renderAssignments(data.assignments);
                            } else {
                                currentAssignments.innerHTML = '<div class="no-assignments">No current assignments</div>';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            currentAssignments.innerHTML = '<div class="error-message">Error loading assignments</div>';
                        });
                }

                // Render assignments list
                function renderAssignments(assignments) {
                    let html = '<h3>Current Assignments</h3><ul class="assignments-list">';
                    
                    assignments.forEach(assignment => {
                        // Format time (remove seconds)
                        const start = assignment.start_time ? assignment.start_time.substring(0,5) : '';
                        const end = assignment.end_time ? assignment.end_time.substring(0,5) : '';
                        const day = assignment.day_of_week ? assignment.day_of_week.charAt(0).toUpperCase() + assignment.day_of_week.slice(1) : '';
                        const room = assignment.room_number ? `Room: ${assignment.room_number}` : '';
                        let schedule = '';
                        if (day && start && end) {
                            schedule = `<div class="assignment-schedule">
                                <span>${day}</span> 
                                <span>${start} - ${end}</span> 
                                <span>${room}</span>
                            </div>`;
                        }

                        html += `
                            <li>
                                <span class="assignment-subject">${assignment.subject_name}</span>
                                <span class="assignment-section">${assignment.section_name}</span>
                                ${schedule}
                                <button class="btn-delete remove-assignment-btn" 
                                        data-sis-id="${assignment.sis_id}">
                                    Remove
                                </button>
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
                    currentAssignments.innerHTML = html;
                }

                // Remove assignment
                function removeAssignment(sisId, instructorId) {
                    fetch('remove_subject.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: `sis_id=${sisId}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Refresh assignments list
                            loadCurrentAssignments(instructorId);
                            // Show success
                            alert('Assignment removed successfully');
                        } else {
                            throw new Error(data.message || 'Failed to remove assignment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error: ' + error.message);
                    });
                }
            });

        // default page
        function showPage(id) {
            // Set URL hash
            window.location.hash = id;
            
            // Hide all pages
            pages.forEach(page => page.style.display = 'none');
            
            // Show selected page
            const target = document.getElementById(id);
            if (target) target.style.display = 'flex';
            
            // Highlight active nav
            navLinks.forEach(link => link.classList.remove('active'));
            const activeLink = document.querySelector(`#navbar a[href="#${id}"]`);
            if (activeLink) activeLink.classList.add('active');
        }

        // On page load, check for hash and show appropriate page
        window.addEventListener('DOMContentLoaded', () => {
            const hash = window.location.hash.substring(1);
            const validPages = ['homepage', 'school-calendar', 'student-management', 
                            'instructor-management', 'subject-management'];
            
            if (hash && validPages.includes(hash)) {
                showPage(hash);
            } else {
                showPage('student-management'); // Default page
            }
        });

        </script>
</body>
</html>