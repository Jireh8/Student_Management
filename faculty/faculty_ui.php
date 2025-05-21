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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Bootstrap CSS (optional, for modal and table styles) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
        :root {
            --primary-color: #12181e;
            --secondary-color: #27ac1f;
            --accent-color: #86fe78;
            --white: #FFFFFF;
        }
        body {
            margin: 0;
            font-family: Roboto, system-ui, sans-serif;
            background-color: #26ac1f10;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        #main-content {
            display: flex;
            flex-grow: 1;
        }
        /* Header */
        #univ-header {
            position: fixed;
            width: 100%;
            background-color: var(--primary-color);
            color: white;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 10px 0;
            z-index: 1001;
            top: 0;
            left: 0;
            height: 70px;
        }
        #univ-logo {
            margin-right: 15px;
            padding: 10px 0 0 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            font-size: 50px;
        }
        #univ-name {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        #header-right {
            margin-left: auto;
            margin-right: 30px;
            color: white;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        #header-right form {
            display: inline;
        }
        #header-right button {
            background: transparent;
            color: white;
            border: 1px solid white;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        #header-right button:hover {
            background: white;
            color: var(--primary-color);
        }
        /* Sidebar */
        #navbar {
            position: fixed;
            top: 70px;
            left: 0;
            height: 90vh;
            width: 60px;
            background-color: var(--primary-color);
            overflow-x: hidden;
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        #navbar.expanded { width: 220px;}
        #navbar ul {
            list-style-type: none;
            padding: 0;
            margin: 60px 0 0 0;
            width: 100%;
        }
        #navbar li {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 10px; 
        }
        #navbar a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            width: 100%;
            font-size: 16px;
        }
        .material-icons { font-size: 20px; }
        .nav-label { margin-left: 10px; }
        #navbar.collapsed .nav-label { display: none; }
        #toggle-nav {
            position: absolute;
            top: 25px;
            left: 15px;
            background-color: transparent;
            color: white;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        #navbar.expanded a.active {
            background-color: #1e2a38;
            border-radius: 8px;
            padding: 20px;
            margin: 0;
        }
        #navbar.collapsed a.active {
            color: #7fb3ee;
            background-color: transparent;
        }
        /* Main content pages */
        .faculty-page {
            display: none;
            margin-top: 85px;
            margin-left: 60px;
            width: 100%;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        #navbar.expanded ~ .faculty-page {
            margin-left: 220px;
        }
        /* Table and card styles (reuse from your previous CSS) */
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
        .section-card {
            cursor: pointer;
            border-left: 4px solid var(--secondary-color);
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .section-card:hover {
            background-color: rgba(134, 254, 120, 0.1);
        }
        /* Profile styles */
        #faculty-profile h2 {
            width: 90%;
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 20px 0;
            padding: 0 0 20px 0;
            border-bottom: 2px solid #12181e;
        }
        #student-gen-info {
            display: flex;
            width: 85%;
        }
        #user-profile {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-right: 30px;
        }
        #user-profile-icon {
            font-size: 150px;
        }
        #profile-upload { display: none; }
        .upload-btn {
            background-color: #27ac1f;
            color: white;
            padding: 8px 16px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }
        .upload-btn:hover { background-color: #1c8017; }
        #info-table table { width: 100%; }
        .firstcol { width: 200px; font-weight: bold;}
        .secondcol { width: 250px; color: #12181eb0; }
        .thirdcol { width: 200px; font-weight: bold;}
        .fourthcol { width: 300px; color: #12181eb0;}
        #faculty-profile td { padding: 10px 15px; font-size: 14px; }
</style>
</head>
<body>
    <!-- Header -->
    <header id="univ-header">
        <span id="univ-logo" class="material-icons">school</span>
        <h1 id="univ-name">Xydle University</h1>
        <div style="margin-left:40px; font-size:1.3rem; font-weight:500; letter-spacing:1px;">
            Faculty Dashboard
        </div>
        <div id="header-right">
            Welcome, <?= htmlspecialchars($_SESSION['username']) ?>
            <form action="logout.php" method="POST">
                <button type="submit"><span class="material-icons" style="vertical-align:middle;">logout</span> Logout</button>
            </form>
        </div>
    </header>
    <div id="main-content">
        <!-- Sidebar Navigation -->
        <nav id="navbar" class="collapsed">
            <button id="toggle-nav"><span class="material-icons">menu</span></button>
            <ul>
                <li><a href="#faculty-grades"><span class="material-icons">grading</span>
                    <span class="nav-label">Grades</span></a></li>
                <li><a href="#faculty-schedule"><span class="material-icons">calendar_today</span>
                    <span class="nav-label">Schedule</span></a></li>
                <li><a href="#faculty-calendar"><span class="material-icons">calendar_month</span>
                    <span class="nav-label">Calendar</span></a></li>
                <li><a href="#faculty-profile"><span class="material-icons">person</span>
                    <span class="nav-label">Profile</span></a></li>
            </ul>
        </nav>
        <!-- Grades Page -->
        <div id="faculty-grades" class="faculty-page">
            <h2 class="title-style">Sections & Grades</h2>
            <div class="row" id="sections-grid" style="width:100%;">
                <?php
                $stmt = $conn->prepare("SELECT 
                                        sec.section_id, 
                                        sec.section_name,
                                        COALESCE(p.program_name, 'No Program') as program_name,
                                        sub.subject_id,
                                        sub.subject_name,
                                        sub.subject_code,
                                        COUNT(DISTINCT si.student_id) as student_count,
                                        ps.semester_offered,
                                        ps.year_offered
                                    FROM Section sec
                                    INNER JOIN subject_instructor_section sis
                                        ON sec.section_id = sis.section_id
                                    INNER JOIN subject sub
                                        ON sis.subject_id = sub.subject_id
                                    INNER JOIN program_subject ps
                                        ON sub.subject_id = ps.subject_id
                                    LEFT JOIN student_information si
                                        ON sec.section_id = si.section_id
                                    LEFT JOIN program p
                                        ON si.program_id = p.program_id
                                    WHERE sis.instructor_id = ?
                                    GROUP BY sec.section_id, sec.section_name, sub.subject_id, sub.subject_name, sub.subject_code, ps.semester_offered, ps.year_offered, p.program_name");
                $stmt->bind_param("i", $_SESSION['instructor_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                // Group sections by section_id
                $sections = [];
                while($row = $result->fetch_assoc()) {
                    $section_year_key = $row['section_id'] . '_' . $row['year_offered'];
                    if (!isset($sections[$section_year_key])) {
                        $sections[$section_year_key] = [
                            'section_id' => $row['section_id'],
                            'section_name' => $row['section_name'],
                            'program_name' => $row['program_name'],
                            'year_offered' => $row['year_offered'],
                            'subjects' => []
                        ];
                    }
                    $sections[$section_year_key]['subjects'][] = [
                        'subject_id' => $row['subject_id'],
                        'subject_name' => $row['subject_name'],
                        'subject_code' => $row['subject_code'],
                        'student_count' => $row['student_count'],
                        'semester_offered' => $row['semester_offered'],
                        'year_offered' => $row['year_offered']
                    ];
                }

                foreach ($sections as $section) {
                    echo '<div class="col-md-4 mb-4">
                            <div class="card section-card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">
                                        <span>' . htmlspecialchars($section['year_offered']) . ' Year - </span>
                                        <i></i>' . htmlspecialchars($section['section_name']) . '
                                    </h5>
                                    <div class="mb-2">
                                        <span class="badge bg-primary">' . htmlspecialchars($section['program_name']) . '</span>
                                    </div>';

                    // Display subjects as clickable pills
                    echo '<div class="mb-2">';
                    foreach ($section['subjects'] as $subject) {
                        echo '<span class="badge bg-secondary me-1 mb-1 subject-pill"
                            onclick="showSectionStudents(' . $section['section_id'] . ', ' . $subject['subject_id'] . ', \'' . htmlspecialchars($subject['semester_offered']) . '\')"
                            style="cursor: pointer; font-size: 0.95rem; padding: 0.4em 0.9em; border-radius: 1.5em; transition: background 0.2s, color 0.2s;">
                            ' . htmlspecialchars($subject['subject_name']) . '
                        </span>';
                    }
                    echo '</div>';
                    // Add custom CSS for hover effect
                    echo '<style>
                        .subject-pill:hover {
                            background: var(--secondary-color) !important;
                            color: var(--white) !important;
                            box-shadow: 0 2px 8px rgba(39,172,31,0.15);
                        }
                    </style>';

                    echo '<div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">
                                <i class="fas fa-calendar-alt me-1"></i>' . htmlspecialchars($section['subjects'][0]['semester_offered']) . ' Semester
                            </span>
                        </div>
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
        <!-- Schedule Page -->
        <div id="faculty-schedule" class="faculty-page">
            <h2 class="title-style">Teaching Schedule</h2>
            <div class="table-container">
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
                                    $scheduleQuery->execute();
                                    $scheduleResult = $scheduleQuery->get_result();
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
                                    foreach ($timeSlots as $timeValue => $timeLabel) {
                                        echo '<tr>';
                                        echo '<td class="text-center font-weight-bold">' . $timeLabel . '</td>';
                                        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
                                            echo '<td>';
                                            $hasClass = false;
                                            foreach ($scheduleByDay[$day] as $class) {
                                                $startTime = substr($class['start_time'], 0, 5);
                                                $endTime = substr($class['end_time'], 0, 5);
                                                $slotTime = substr($timeValue, 0, 5);
                                                $slotEndTime = date('H:i', strtotime($timeValue . ' +1 hour'));
                                                if (($slotTime >= $startTime && $slotTime <= $endTime) ||
                                                    ($slotEndTime > $startTime && $slotEndTime <= $endTime) ||
                                                    ($slotTime <= $startTime && $slotEndTime >= $endTime)) {
                                                    if ($slotTime == $startTime) {
                                                        echo '<div class="p-1 bg-light border-start border-4 border-success rounded mb-1">';
                                                        echo '<small class="d-block fw-bold">' . htmlspecialchars($class['subject_code']) . '</small>';
                                                        echo '<small class="d-block">' . htmlspecialchars($class['section']) . '</small>';
                                                        echo '<small class="d-block text-muted">' . htmlspecialchars($class['subject_name']) . '</small>';
                                                        echo '<small class="d-block text-muted">Room: ' . htmlspecialchars($class['room']) . '</small>';
                                                        echo '</div>';
                                                    } else {
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
        <!-- Calendar Page -->
        <div id="faculty-calendar" class="faculty-page">
            <h2 class="title-style">Academic Calendar</h2>
            <div class="table-container">
                <div class="mb-3">
                    <label for="calendarYear">Year:</label>
                    <select id="calendarYear" class="form-select" style="width: auto; display: inline-block;"></select>
                    <label for="calendarMonth" class="ms-3">Month:</label>
                    <select id="calendarMonth" class="form-select" style="width: auto; display: inline-block;"></select>
                </div>
                <table class="table table-bordered text-center" id="calendarTable">
                    <thead class="table-dark">
                        <tr>
                            <th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Profile Page -->
        <div id="faculty-profile" class="faculty-page">
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
            </div>
        </div>
    </div>
    <!-- JS for sidebar and navigation -->
    <script>
        // Sidebar toggle
        const toggleBtn = document.getElementById('toggle-nav');
        const navbar = document.getElementById('navbar');
        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('expanded');
            navbar.classList.toggle('collapsed');
        });
        // Page navigation logic
        const navLinks = document.querySelectorAll('#navbar a');
        const pages = document.querySelectorAll('.faculty-page');
        function showPage(id) {
            pages.forEach(page => page.style.display = 'none');
            const target = document.getElementById(id);
            if (target) target.style.display = 'flex';
            navLinks.forEach(link => link.classList.remove('active'));
            const activeLink = document.querySelector(`#navbar a[href="#${id}"]`);
            if (activeLink) activeLink.classList.add('active');
        }
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = link.getAttribute('href').substring(1);
                showPage(targetId);
            });
        });
        // Show default page
        showPage('faculty-grades');
    </script>
    <!-- Calendar JS -->
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
    <!-- Bootstrap JS Bundle with Popper (for modal) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 (for alerts) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Grades tab JS (reuse your AJAX logic here) -->
    <script>
        function getCurrentSchoolYear() {
            return "2025-2026"; // Placeholder, replace with actual logic if needed
        }
        let currentSectionId = null;
        let currentSubjectId = null; 
        let currentSemester = null;
        let studentsData = [];
        let currentSort = { key: 'year_level', asc: true };
        function showSectionStudents(sectionId, subjectId, semester) {
            currentSectionId = sectionId;
            currentSubjectId = subjectId;
            currentSemester = semester;
            document.getElementById('sections-grid').style.display = 'none';
            document.getElementById('students-table').style.display = 'block';
            document.getElementById('currentSchoolYear').textContent = getCurrentSchoolYear();
            document.getElementById('currentSemester').textContent = currentSemester;
            fetchAndDisplayStudents();
            document.getElementById('studentSearch').oninput = filterAndRenderStudents;
        }
        function fetchAndDisplayStudents() {
            const studentsList = document.getElementById('students-list');
            studentsList.innerHTML = '<tr><td colspan="7" class="text-center">Loading students...</td></tr>';
            const schoolYear = getCurrentSchoolYear();
            fetch(`get_section_students.php?section_id=${currentSectionId}&subject_id=${currentSubjectId}&semester=${currentSemester}&school_year=${schoolYear}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.error || 'Failed to fetch students'); });
                    }
                    return response.json();
                })
                .then(data => {
                    const sectionTitle = document.getElementById('section-title');
                    const sectionMeta = document.getElementById('section-meta');
                    sectionTitle.innerHTML = `<i class="fas fa-users me-2"></i>${data.section_name}`;
                    sectionMeta.innerHTML = `${data.program_name} &bull; ${data.student_count} students &bull; Semester: ${currentSemester}`;
                    studentsData = data.students || [];
                    filterAndRenderStudents();
                })
                .catch(error => {
                    studentsList.innerHTML = `<tr><td colspan="7" class="text-center text-danger">${error.message}</td></tr>`;
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
            if (students.length === 0) {
                studentsList.innerHTML = '<tr><td colspan="7" class="text-center">No students found in this section/subject</td></tr>';
                return;
            }
            students.forEach(student => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${student.student_id}</td>
                    <td>${student.student_name}</td>
                    <td>${student.program_name}</td>
                    <td>${student.year_level}</td>
                    <td>${student.subject.subject_code} - ${student.subject.subject_name}</td>
                    <td>${student.subject.final_grade !== null ? student.subject.final_grade : 0}</td>
                    <td>${student.subject.scholastic_status || 'Regular'}</td>
                    <td>
                        <button class="btn btn-sm ${student.subject.final_grade == 0 ? 'btn-success' : 'btn-warning'}"
                            onclick="showUpdateGradeModal(${student.student_id}, '${student.student_name}', ${student.subject.subject_id}, '${student.subject.subject_name}', ${student.subject.final_grade})">
                            <i class="fas ${student.subject.final_grade == 0 ? 'fa-plus' : 'fa-edit'}"></i>
                            ${student.subject.final_grade == 0 ? 'Add' : 'Update'} Grade
                        </button>
                    </td>
                `;
                studentsList.appendChild(row);
            });
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