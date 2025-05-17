<?php 
    include '../config.php';
    session_start();
    if (!isset($_SESSION['instructor_id'])) {
        header("Location: faculty_login.php?error");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="faculty_profile.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        :root {
            --primary-color: #12181e;
            --secondary-color: #27ac1f;
            --accent-color: #86fe78;
            --white: #FFFFFF;
        }
        
        body {
            background-color: var(--white);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            color: var(--white) !important;
            font-weight: bold;
        }

        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            background-color: var(--white);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .section-card {
            cursor: pointer;
            border-left: 4px solid var(--secondary-color);
        }

        .section-card:hover {
            background-color: rgba(134, 254, 120, 0.1);
        }

        .table-container {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }

        .table thead th {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            font-weight: 500;
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border: none;
            padding: 8px 16px;
            font-weight: 500;
        }

        .btn-primary:hover {
            background-color: var(--accent-color);
            color: var(--primary-color);
        }

        .welcome-section {
            background: var(--primary-color); /* Solid color */
            color: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(134, 254, 120, 0.1);
        }

        .btn-outline-light {
            border-color: var(--white);
            color: var(--white);
        }

        .btn-outline-light:hover {
            background-color: var(--white);
            color: var(--primary-color);
        }

        .text-white {
            color: var(--white) !important;
        }
        
        /* Calendar styles */
        .calendar-day {
            height: 80px;
            vertical-align: top;
        }
        
        .calendar-day-number {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .calendar-today {
            background-color: rgba(134, 254, 120, 0.2);
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-university me-2"></i>
                Xydle University
            </a>
            <div class="d-flex align-items-center">
                <span class="text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <form action="logout.php" method="POST">
                    <button type="submit" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h2><i class="fas fa-chalkboard-teacher me-2"></i>Faculty Dashboard</h2>
            <p class="mb-0">Manage your sections and student grades</p>
        </div>

        <ul class="nav nav-tabs mb-4" id="dashboardTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="grades-tab" data-bs-toggle="tab" data-bs-target="#grades" type="button" role="tab">Grades</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="schedule-tab" data-bs-toggle="tab" data-bs-target="#schedule" type="button" role="tab">Schedule</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="calendar-tab" data-bs-toggle="tab" data-bs-target="#calendar" type="button" role="tab">Calendar</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="faculty-profile-tab" data-bs-toggle="tab" data-bs-target="#faculty-profile" type="button" role="tab">
                    <span class="material-icons"></span> Profile
                </button>
            </li>
        </ul>

        <div class="tab-content" id="dashboardTabsContent">
            <!-- Grades Tab -->
            <div class="tab-pane fade show active" id="grades" role="tabpanel">
                <!-- Sections Grid -->
                <div class="row" id="sections-grid">
                    <?php
                        $stmt = $conn->prepare("SELECT sec.section_id, sec.section_name,
                                            COALESCE(p.program_name, 'No Program') as program_name,
                                            GROUP_CONCAT(DISTINCT sub.subject_name) as subject_names,
                                            COUNT(DISTINCT si.student_id) as student_count,
                                            GROUP_CONCAT(DISTINCT sg.semester) as semesters
                                            FROM Section sec
                                            INNER JOIN subject_instructor_section sis
                                                ON sec.section_id = sis.section_id
                                            INNER JOIN subject sub
                                                ON sis.subject_id = sub.subject_id
                                            LEFT JOIN student_information si
                                                ON sec.section_id = si.section_id
                                            LEFT JOIN program p
                                                ON si.program_id = p.program_id
                                            LEFT JOIN student_grades sg
                                                ON si.student_id = sg.student_id
                                            WHERE sis.instructor_id = ?
                                            GROUP BY sec.section_id, sec.section_name");
                        $stmt->bind_param("i", $_SESSION['instructor_id']);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        while($row = $result->fetch_assoc()) {
                            echo '<div class="col-md-4 mb-4">
                                    <div class="card section-card" onclick="showSectionStudents(' . $row['section_id'] . ', \'' . htmlspecialchars($row['semesters']) . '\')">
                                        <div class="card-body">
                                            <h5 class="card-title">
                                                <i class="fas fa-users me-2"></i>' . htmlspecialchars($row['section_id'] . $row['section_name']) . '
                                            </h5>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-graduation-cap me-2"></i>' . htmlspecialchars($row['program_name']) . '
                                            </p>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-book me-2"></i>' . htmlspecialchars($row['subject_names']) . '
                                            </p>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-calendar-alt me-2"></i>Semester: ' . htmlspecialchars($row['semesters']) . '
                                            </p>
                                            <p class="card-text mb-1">
                                                <i class="fas fa-user-graduate me-2"></i>' . $row['student_count'] . ' Students
                                            </p>
                                        </div>
                                    </div>
                                </div>';
                        }
                    ?>
                </div>

                <!-- Students Table (Hidden by default) -->
                <div id="students-table" class="table-container" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h3 id="section-title"></h3>
                            <div id="section-meta" class="text-muted small"></div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span id="currentSchoolYear" class="me-2 fw-bold"></span>
                            <label class="me-2 mb-0">Semester:</label>
                            <span id="currentSemester" class="fw-bold me-4"></span>
                            <button class="btn btn-primary" onclick="showSectionsGrid()">
                                <i class="fas fa-arrow-left me-2"></i>Back to Sections
                            </button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mb-2">
                        <input type="text" id="studentSearch" class="form-control form-control-sm" style="width: 250px;" placeholder="Search students...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th onclick="sortTable('student_id')" style="cursor:pointer;">Student ID <span id="sort_student_id"></span></th>
                                    <th onclick="sortTable('student_name')" style="cursor:pointer;">Name <span id="sort_student_name"></span></th>
                                    <th onclick="sortTable('program_name')" style="cursor:pointer;">Program <span id="sort_program_name"></span></th>
                                    <th onclick="sortTable('year_level')" style="cursor:pointer;">Year Level <span id="sort_year_level"></span></th>
                                    <th>Subject</th>
                                    <th>Grade</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="students-list">
                                <!-- Students and grades will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Update Grade Modal -->
                <div class="modal fade" id="updateGradeModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalTitle">Add/Update Grade</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <form id="updateGradeForm" action="update_grades.php" method="POST">
                                    <input type="hidden" name="student_id" id="updateStudentId">
                                    <input type="hidden" name="subject_id" id="updateSubjectId">
                                    <input type="hidden" name="action" id="actionType">
                                    <input type="hidden" name="semester" id="updateSemester">
                                    <input type="hidden" name="school_year" id="updateSchoolYear">
                                    <div class="mb-3">
                                        <label class="form-label">Student Name</label>
                                        <input type="text" class="form-control" id="updateStudentName" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Subject</label>
                                        <input type="text" class="form-control" id="updateSubjectName" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Grade</label>
                                        <input type="number" class="form-control" name="grade" id="updateGrade" min="0" max="100" step="0.01" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary" id="submitButton">Save Grade</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Tab -->
            <div class="tab-pane fade" id="schedule" role="tabpanel">
                <div class="table-container">
                    <h4><i class="fas fa-calendar-alt me-2"></i>Teaching Schedule</h4>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <p class="mb-0">Current academic schedule for <?= htmlspecialchars($_SESSION['username']) ?></p>
                        <div>
                            <input type="text" id="scheduleSearch" class="form-control form-control-sm" style="width: 250px;" placeholder="Search schedule...">
                        </div>
                    </div>
                   
                    <div class="table-responsive">
                        <table class="table table-hover" id="scheduleTable">
                            <thead>
                                <tr>
                                    <th onclick="sortSchedule('day_of_week')" style="cursor:pointer;">Day <span id="sort_day_of_week"></span></th>
                                    <th onclick="sortSchedule('time')" style="cursor:pointer;">Time <span id="sort_time"></span></th>
                                    <th onclick="sortSchedule('section')" style="cursor:pointer;">Section <span id="sort_section"></span></th>
                                    <th onclick="sortSchedule('subject')" style="cursor:pointer;">Subject <span id="sort_subject"></span></th>
                                    <th onclick="sortSchedule('room')" style="cursor:pointer;">Room <span id="sort_room"></span></th>
                                </tr>
                            </thead>
                            <tbody id="scheduleList">
                                <?php
                                    // Fetch instructor's schedule
                                    $scheduleQuery = $conn->prepare("
                                        SELECT
                                            s.day_of_week,
                                            s.start_time,
                                            s.end_time,
                                            s.room_number,
                                            sec.section_id,
                                            sec.section_name,
                                            sub.subject_name,
                                            sub.subject_code
                                        FROM schedule s
                                        INNER JOIN subject_instructor_section sis ON s.sis_id = sis.sis_id
                                        INNER JOIN section sec ON sis.section_id = sec.section_id
                                        INNER JOIN subject sub ON sis.subject_id = sub.subject_id
                                        WHERE sis.instructor_id = ?
                                        ORDER BY
                                            CASE
                                                WHEN s.day_of_week = 'Monday' THEN 1
                                                WHEN s.day_of_week = 'Tuesday' THEN 2
                                                WHEN s.day_of_week = 'Wednesday' THEN 3
                                                WHEN s.day_of_week = 'Thursday' THEN 4
                                                WHEN s.day_of_week = 'Friday' THEN 5
                                                WHEN s.day_of_week = 'Saturday' THEN 6
                                                WHEN s.day_of_week = 'Sunday' THEN 7
                                                ELSE 8
                                            END,
                                            s.start_time
                                    ");
                                   
                                    $scheduleQuery->bind_param("i", $_SESSION['instructor_id']);
                                    $scheduleQuery->execute();
                                    $scheduleResult = $scheduleQuery->get_result();
                                   
                                    if ($scheduleResult->num_rows > 0) {
                                        while($schedule = $scheduleResult->fetch_assoc()) {
                                            // Format the time for display
                                            $startTime = date("h:i A", strtotime($schedule['start_time']));
                                            $endTime = date("h:i A", strtotime($schedule['end_time']));
                                           
                                            echo '<tr>
                                                <td><span class="badge bg-primary">' . htmlspecialchars($schedule['day_of_week']) . '</span></td>
                                                <td>' . $startTime . ' - ' . $endTime . '</td>
                                                <td>' . htmlspecialchars($schedule['section_id'] . $schedule['section_name']) . '</td>
                                                <td>
                                                    <strong>' . htmlspecialchars($schedule['subject_code']) . '</strong><br>
                                                    <small>' . htmlspecialchars($schedule['subject_name']) . '</small>
                                                </td>
                                                <td>' . htmlspecialchars($schedule['room_number']) . '</td>
                                            </tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="5" class="text-center">No schedule found</td></tr>';
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Weekly Calendar View -->
                    <div class="mt-5">
                        <h5><i class="fas fa-calendar-week me-2"></i>Weekly Schedule View</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered" id="weeklyScheduleTable">
                                <thead>
                                    <tr class="text-center">
                                        <th style="width: 12%">Time</th>
                                        <th style="width: 12%">Monday</th>
                                        <th style="width: 12%">Tuesday</th>
                                        <th style="width: 12%">Wednesday</th>
                                        <th style="width: 12%">Thursday</th>
                                        <th style="width: 12%">Friday</th>
                                        <th style="width: 12%">Saturday</th>
                                        <th style="width: 12%">Sunday</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        // Define time slots (adjust as needed)
                                        $timeSlots = [
                                            '07:00:00' => '07:00 AM',
                                            '08:00:00' => '08:00 AM',
                                            '09:00:00' => '09:00 AM',
                                            '10:00:00' => '10:00 AM',
                                            '11:00:00' => '11:00 AM',
                                            '12:00:00' => '12:00 PM',
                                            '13:00:00' => '01:00 PM',
                                            '14:00:00' => '02:00 PM',
                                            '15:00:00' => '03:00 PM',
                                            '16:00:00' => '04:00 PM',
                                            '17:00:00' => '05:00 PM',
                                            '18:00:00' => '06:00 PM',
                                            '19:00:00' => '07:00 PM',
                                        ];


                                        // Reset the result pointer
                                        $scheduleQuery->execute();
                                        $scheduleResult = $scheduleQuery->get_result();
                                    
                                        // Organize schedule by day and time
                                        $scheduleByDay = [
                                            'monday' => [],
                                            'tuesday' => [],
                                            'wednesday' => [],
                                            'thursday' => [],
                                            'friday' => [],
                                            'saturday' => [],
                                            'sunday' => []
                                        ];
                                    
                                        while($schedule = $scheduleResult->fetch_assoc()) {
                                            $day = strtolower($schedule['day_of_week']);
                                            $scheduleByDay[$day][] = [
                                                'start_time' => $schedule['start_time'],
                                                'end_time' => $schedule['end_time'],
                                                'subject_code' => $schedule['subject_code'],
                                                'section' => $schedule['section_id'] . $schedule['section_name'],
                                                'room' => $schedule['room_number'],
                                                'subject_name' => $schedule['subject_name']
                                            ];
                                        }
                                    
                                        // Generate time slots
                                        foreach ($timeSlots as $timeValue => $timeLabel) {
                                            echo '<tr>';
                                            echo '<td class="text-center font-weight-bold">' . $timeLabel . '</td>';
                                        
                                            // For each day, check if there's a class at this time
                                            foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                                                echo '<td>';
                                                $hasClass = false;
                                            
                                                foreach ($scheduleByDay[$day] as $class) {
                                                    $startTime = substr($class['start_time'], 0, 5);
                                                    $endTime = substr($class['end_time'], 0, 5);
                                                    $slotTime = substr($timeValue, 0, 5);
                                                    $slotEndTime = date('H:i', strtotime($timeValue . ' +1 hour'));
                                                
                                                    // Check if this time slot overlaps with class hours
                                                    if (($slotTime >= $startTime && $slotTime <= $endTime) ||
                                                        ($slotEndTime > $startTime && $slotEndTime <= $endTime) ||
                                                        ($slotTime <= $startTime && $slotEndTime >= $endTime)) {
                                                    
                                                        // Show full details only at class start time
                                                        if ($slotTime == $startTime) {
                                                            echo '<div class="p-1 bg-light border-start border-4 border-success rounded mb-1">';
                                                            echo '<small class="d-block fw-bold">' . htmlspecialchars($class['subject_code']) . '</small>';
                                                            echo '<small class="d-block">' . htmlspecialchars($class['section']) . '</small>';
                                                            echo '<small class="d-block text-muted">' . htmlspecialchars($class['subject_name']) . '</small>';
                                                            echo '<small class="d-block text-muted">Room: ' . htmlspecialchars($class['room']) . '</small>';
                                                            echo '</div>';
                                                        } else {
                                                            // Show continuation marker for other hours
                                                            echo '<div class="p-1 bg-light border-start border-4 border-success rounded mb-1 text-center">';
                                                            echo '<small class="text-muted">⋮</small>';
                                                            echo '</div>';
                                                        }
                                                        $hasClass = true;
                                                        break;
                                                    }
                                                }
                                            
                                                if (!$hasClass) {
                                                    echo '<div class="p-1 bg-light bg-opacity-10 text-center"><small class="text-muted">-</small></div>';
                                                }
                                                echo '</td>';
                                            }
                                            echo '</tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Calendar Tab -->
            <div class="tab-pane fade" id="calendar" role="tabpanel">
                <div class="table-container">
                    <h4>Academic Calendar</h4>
                    <p>Embed a calendar or show important academic dates/events.</p>
                    <!-- You could use a calendar plugin or static content -->
                    <!-- BEGIN: Calendar Inserted Here -->
                <div class="mb-3">
                <label for="calendarYear">Year:</label>
                <select id="calendarYear" class="form-select" style="width: auto; display: inline-block;">
                    <!-- Populated by JavaScript -->
                </select>

                <label for="calendarMonth" class="ms-3">Month:</label> <select id="calendarMonth" class="form-select" style="width: auto; display: inline-block;"> <!-- Populated by JavaScript --> </select>

                </div>

                <table class="table table-bordered text-center" id="calendarTable">
                    <thead class="table-dark">
                        <tr>
                        <th>Sun</th><th>Mon</th><th>Tue</th><Wed><th>Thu</th><Fri><th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>

            
                    </div>
                </div>
            </div>

            <!-- Faculty Profile Tab -->
            <div class="tab-pane fade" id="faculty-profile" role="tabpanel">
                <div id="faculty-profile" class="student-page">
                    <h2>Faculty Profile</h2>
                    <div id="student-gen-info">
                        <section id="user-profile">
                            <span class="material-icons" id="user-profile-icon">account_circle</span>
                            <label for="profile-upload" class="upload-btn">Change Profile</label>
                            <input type="file" id="profile-upload" accept="image/*">
                        </section>
                        <section id="info-table">
                            <table>
                                <tbody>
                                    <?php
                                    // Fetch faculty info from instructor and contact_information tables
                                    $stmt_fac = $conn->prepare("
                                        SELECT 
                                            i.instructor_id,
                                            i.instructor_name,
                                            i.department_id,
                                            c.address,
                                            c.phone_number,
                                            c.email
                                        FROM instructor i
                                        INNER JOIN contact_information c ON i.contact_id = c.contact_id
                                        WHERE i.instructor_id = ?
                                    ");
                                    $stmt_fac->bind_param("i", $_SESSION['instructor_id']);
                                    $stmt_fac->execute();
                                    $faculty = $stmt_fac->get_result()->fetch_assoc();

                                    // Optionally fetch department name if you want to display it
                                    $department_name = '';
                                    if ($faculty && isset($faculty['department_id'])) {
                                        $dept_stmt = $conn->prepare("SELECT department_name FROM department WHERE department_id = ?");
                                        $dept_stmt->bind_param("i", $faculty['department_id']);
                                        $dept_stmt->execute();
                                        $dept_result = $dept_stmt->get_result()->fetch_assoc();
                                        $department_name = $dept_result ? $dept_result['department_name'] : '';
                                    }
                                    ?>
                                    <tr>
                                        <td class="firstcol">Faculty Name</td>
                                        <td class="secondcol"><?= htmlspecialchars($faculty['instructor_name']) ?></td>
                                        <td class="thirdcol">Faculty ID</td>
                                        <td class="fourthcol"><?= htmlspecialchars($faculty['instructor_id']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="firstcol">Department</td>
                                        <td class="secondcol"><?= htmlspecialchars($department_name) ?></td>
                                        <td class="thirdcol">Email Address</td>
                                        <td class="fourthcol"><?= htmlspecialchars($faculty['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <td class="firstcol">Phone Number</td>
                                        <td class="secondcol"><?= htmlspecialchars($faculty['phone_number']) ?></td>
                                        <td class="thirdcol">Address</td>
                                        <td class="fourthcol"><?= htmlspecialchars($faculty['address']) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </section>

            <script>
            const monthSelect = document.getElementById('calendarMonth');
            const yearSelect = document.getElementById('calendarYear');
            const calendarBody = document.querySelector('#calendarTable tbody');
            const months = ["January", "February", "March", "April", "May", "June", 
                            "July", "August", "September", "October", "November", "December"];

            // Populate years from current to 2050
            const currentYear = new Date().getFullYear();
            for (let y = currentYear; y <= 2050; y++) {
                let option = document.createElement('option');
                option.value = y;
                option.text = y;
                yearSelect.appendChild(option);
            }

            // Populate months
            months.forEach((month, index) => {
                let option = document.createElement('option');
                option.value = index;
                option.text = month;
                monthSelect.appendChild(option);
            });

            // Draw calendar
            function generateCalendar(year, month) {
                calendarBody.innerHTML = '';
                const firstDay = new Date(year, month, 1).getDay();
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                let row = document.createElement('tr');
                for (let i = 0; i < firstDay; i++) {
                row.appendChild(document.createElement('td'));
                }

                for (let day = 1; day <= daysInMonth; day++) {
                let cell = document.createElement('td');
                cell.textContent = day;
                row.appendChild(cell);
                if ((firstDay + day) % 7 === 0 || day === daysInMonth) {
                    calendarBody.appendChild(row);
                    row = document.createElement('tr');
                }
                }
            }

            // Initial render
            const now = new Date();
            yearSelect.value = now.getFullYear();
            monthSelect.value = now.getMonth();
            generateCalendar(now.getFullYear(), now.getMonth());

            // Change handlers
            yearSelect.addEventListener('change', () => {
                generateCalendar(+yearSelect.value, +monthSelect.value);
            });

            monthSelect.addEventListener('change', () => {
                generateCalendar(+yearSelect.value, +monthSelect.value);
            });
            </script>

            <!-- END: Calendar Inserted Here -->

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Initialize Bootstrap components
        document.addEventListener('DOMContentLoaded', function() {
            // Handle success/error messages
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            const error = urlParams.get('error');
            
            if (success === 'grade_updated') {
                alert('GWA updated successfully!');
                // Remove the success parameter from URL
                window.history.replaceState({}, document.title, window.location.pathname);
            } else if (error) {
                alert('Error: ' + error);
                // Remove the error parameter from URL
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        // Helper to get the current school year (static)
        function getCurrentSchoolYear() {
            return '2024-2025';
        }

        let currentSectionId = null;
        let currentSemester = null;
        let studentsData = [];
        let currentSort = { key: 'year_level', asc: true };

        function showSectionStudents(sectionId, semester) {
            currentSectionId = sectionId;
            currentSemester = semester;
            document.getElementById('sections-grid').style.display = 'none';
            document.getElementById('students-table').style.display = 'block';
            document.getElementById('currentSchoolYear').textContent = getCurrentSchoolYear();
            document.getElementById('currentSemester').textContent = semester;
            fetchAndDisplayStudents();
            document.getElementById('studentSearch').oninput = filterAndRenderStudents;
        }

        function fetchAndDisplayStudents() {
            const studentsList = document.getElementById('students-list');
            studentsList.innerHTML = '<tr><td colspan="7" class="text-center">Loading students...</td></tr>';
            const schoolYear = getCurrentSchoolYear();
            fetch(`get_section_students.php?section_id=${currentSectionId}&semester=${currentSemester}&school_year=${schoolYear}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'Failed to fetch students'); });
                    }
                    return response.json();
                })
                .then(data => {
                    const sectionTitle = document.getElementById('section-title');
                    const sectionMeta = document.getElementById('section-meta');
                    sectionTitle.innerHTML = `<i class=\"fas fa-users me-2\"></i>${data.section_name}`;
                    sectionMeta.innerHTML = `${data.program_name} &bull; ${data.student_count} students`;
                    studentsData = data.students || [];
                    console.log('Fetched studentsData:', studentsData); // Debug log
                    filterAndRenderStudents();
                })
                .catch(error => {
                    studentsList.innerHTML = `<tr><td colspan=\"7\" class=\"text-center text-danger\">${error.message}</td></tr>`;
                });
        }

        function filterAndRenderStudents() {
            const search = document.getElementById('studentSearch').value.toLowerCase();
            let filtered = studentsData.filter(s =>
                s.student_id.toString().includes(search) ||
                (s.student_name && s.student_name.toLowerCase().includes(search)) ||
                (s.program_name && s.program_name.toLowerCase().includes(search))
            );
            filtered = sortStudents(filtered);
            renderStudents(filtered);
        }

        function sortTable(key) {
            if (currentSort.key === key) {
                currentSort.asc = !currentSort.asc;
            } else {
                currentSort.key = key;
                currentSort.asc = true;
            }
            filterAndRenderStudents();
            updateSortIcons();
        }

        function sortStudents(arr) {
            return arr.slice().sort((a, b) => {
                let v1 = a[currentSort.key];
                let v2 = b[currentSort.key];
                if (typeof v1 === 'string') v1 = v1.toLowerCase();
                if (typeof v2 === 'string') v2 = v2.toLowerCase();
                if (v1 < v2) return currentSort.asc ? -1 : 1;
                if (v1 > v2) return currentSort.asc ? 1 : -1;
                return 0;
            });
        }

        function updateSortIcons() {
            ['student_id','student_name','program_name','year_level'].forEach(key => {
                document.getElementById('sort_' + key).textContent = '';
            });
            const icon = currentSort.asc ? '▲' : '▼';
            if (document.getElementById('sort_' + currentSort.key)) {
                document.getElementById('sort_' + currentSort.key).textContent = icon;
            }
        }

        function renderStudents(students) {
            const studentsList = document.getElementById('students-list');
            studentsList.innerHTML = '';
            console.log('Students data:', students); // Debug log

            if (students.length === 0) {
                studentsList.innerHTML = '<tr><td colspan="7" class="text-center">No students found in this section</td></tr>';
                return;
            }
            let anyRow = false;
            students.forEach(student => {
                if (!student.subjects || student.subjects.length === 0) {
                    // Show student with no subjects
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${student.student_id}</td>
                        <td>${student.student_name}</td>
                        <td>${student.program_name}</td>
                        <td>${student.year_level}</td>
                        <td colspan="4" class="text-center text-muted">No subjects assigned for this year/semester</td>
                    `;
                    studentsList.appendChild(row);
                    anyRow = true;
                } else {
                    student.subjects.forEach(subject => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${student.student_id}</td>
                            <td>${student.student_name}</td>
                            <td>${student.program_name}</td>
                            <td>${student.year_level}</td>
                            <td>${subject.subject_code} - ${subject.subject_name}</td>
                            <td>${subject.final_grade !== null ? subject.final_grade : 0}</td>
                            <td>${subject.scholastic_status || 'Regular'}</td>
                            <td>
                                <button class="btn btn-sm ${subject.final_grade == 0 ? 'btn-success' : 'btn-warning'}"
                                    onclick="showUpdateGradeModal(${student.student_id}, '${student.student_name}', ${subject.subject_id}, '${subject.subject_name}', ${subject.final_grade})">
                                    <i class="fas ${subject.final_grade == 0 ? 'fa-plus' : 'fa-edit'}"></i>
                                    ${subject.final_grade == 0 ? 'Add' : 'Update'} Grade
                                </button>
                            </td>
                        `;
                        studentsList.appendChild(row);
                        anyRow = true;
                    });
                }
            });
            if (!anyRow) {
                studentsList.innerHTML = '<tr><td colspan="7" class="text-center">No students with subjects found in this section</td></tr>';
            }
        }

        function showSectionsGrid() {
            document.getElementById('sections-grid').style.display = 'flex';
            document.getElementById('students-table').style.display = 'none';
        }

        function showUpdateGradeModal(studentId, studentName, subjectId, subjectName, grade) {
            document.getElementById('updateStudentId').value = studentId;
            document.getElementById('updateStudentName').value = studentName;
            document.getElementById('updateSubjectId').value = subjectId;
            document.getElementById('updateSubjectName').value = subjectName;
            document.getElementById('updateSemester').value = currentSemester;
            document.getElementById('updateSchoolYear').value = getCurrentSchoolYear();
            document.getElementById('updateGrade').value = grade && grade != 0 ? grade : '';
            document.getElementById('actionType').value = grade == 0 ? 'add' : 'update';
            const modal = new bootstrap.Modal(document.getElementById('updateGradeModal'));
            modal.show();
        }

        // SweetAlert2 AJAX form submission for grade
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('updateGradeForm');
            if (form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    const formData = new FormData(form);
                    fetch('update_grades.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        if (data.includes('success=grade_updated')) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Grade updated successfully!'
                            }).then(() => {
                                // Close modal and refresh students
                                bootstrap.Modal.getInstance(document.getElementById('updateGradeModal')).hide();
                                fetchAndDisplayStudents();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to update grade.'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to update grade.'
                        });
                    });
                };
            }
        });
    </script>
</body>
</html>

