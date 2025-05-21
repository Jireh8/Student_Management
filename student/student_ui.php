<?php 
    include '../config.php';
    session_start();
    if (!isset($_SESSION['student_id'])) {
        header("Location: login.html?error");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="student_ui.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

    <header id="univ-header">
        <!--<img src="university-logo.png" alt="University Logo" id="univ-logo"><-->
        <span id="univ-logo" class="material-icons">school</span>
        <h1 id="univ-name">Xydle University</h1>
    </header>

    <div id="main-content">
        <nav id="navbar" class="collapsed">
            <button id="toggle-nav"><span class="material-icons">menu</span></button>
            <ul>
                <li id="sched"><a href="#student-schedule"><span class="material-icons">calendar_today</span>
                    <span class="nav-label"> Schedule</span></a></li>
                <li><a href="#student-grades"><span class="material-icons">grading</span>
                    <span class="nav-label"> Grades</span></a></li>
                <li><a href="#student-program"><span class="material-icons">book</span>
                    <span class="nav-label"> Program</span></a></li>
                <li><a href="#student-calendar"><span class="material-icons">calendar_month</span>
                    <span class="nav-label"> Calendar</span></a></li>
                <li><a href="#student-profile"><span class="material-icons">person</span>
                    <span class="nav-label"> Profile</span></a></li>
                <li>
                    <form action="logout.php" method="POST" id="logout-form">
                        <button type="submit"><span class="material-icons">logout</span>
                        <span class="nav-label"> Logout</span></button>
                    </form>
                    
                </li>
            </ul>
        </nav>

        <!-- student schedule tab -->
        <div id="student-schedule" class="student-page">
            <header>
                <h2 class = "title-style sched-head">Class Schedule</h2>
                <form method="POST" action="generate_schedule.php">
                    <button type="submit"><i class="material-icons">download</i>
                    Download Copy</button>
                </form>
            </header>
            <section class="user-info">
                <span>Welcome back, <strong><?= htmlspecialchars($_SESSION['firstname'])?></strong>!</span>
                <span><strong>Student ID: <?= htmlspecialchars($_SESSION['student_id'])?></strong></span>
            </section>
            <section class="current-info">
                <span><?= htmlspecialchars($_SESSION['program'])?></span>
                <span>Term/Sem: <?= htmlspecialchars($_SESSION['sem'])?></span>
                <span>School Year: <?= htmlspecialchars($_SESSION['school_year'])?></span>
            </section>
            <table class="table-style tr:hover">
                <thead>
                    <tr>
                        <th id="subj-code">Subject Code</th>
                        <th id="desc">Description</th>
                        <th id="units">Units</th>
                        <th id="faculty">Faculty</th>
                        <th id="section">Section</th>
                        <th id="day">Day</th>
                        <th id="start-time">Start Time</th>
                        <th id="end-time">End Time</th>
                        <th id="room">Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $tableQuery = $conn->prepare("
                            SELECT subj.subject_code, subj.subject_name, subj.units, ins.instructor_name, 
                                sec.section_name, sc.day_of_week, sc.start_time, sc.end_time, sc.room_number
                            FROM schedule AS sc
                            INNER JOIN subject_instructor_section AS sis USING(sis_id)
                            INNER JOIN subject AS subj ON sis.subject_id = subj.subject_id
                            INNER JOIN instructor AS ins ON sis.instructor_id = ins.instructor_id
                            INNER JOIN Section AS sec ON sis.section_id = sec.section_id
                            INNER JOIN program_subject AS ps ON ps.subject_id = subj.subject_id
                            WHERE ps.program_id = ?
                            AND ps.year_offered = ?
                            AND ps.semester_offered = ?
                        ");
                        $tableQuery->bind_param(
                            "iis",
                            $_SESSION['program_id'],
                            $_SESSION['year_level'],
                            $_SESSION['sem']
                        );
                        $tableQuery->execute();
                        $result = $tableQuery->get_result();

                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['subject_code']) . "</td>
                                    <td>" . htmlspecialchars($row['subject_name']) . "</td>
                                    <td>" . htmlspecialchars($row['units']) . "</td>
                                    <td>" . htmlspecialchars($row['instructor_name']) . "</td>
                                    <td>" . htmlspecialchars($row['section_name']) . "</td>
                                    <td>" . htmlspecialchars($row['day_of_week']) . "</td>
                                    <td>" . htmlspecialchars($row['start_time']) . "</td>
                                    <td>" . htmlspecialchars($row['end_time']) . "</td>
                                    <td>" . htmlspecialchars($row['room_number']) . "</td>
                                </tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- student grades tab -->
        <div id="student-grades" class="student-page">
            <h2 class="title-style">Grades</h2>
            <section class="user-info">
                <span>Welcome, <strong><?= htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']) ?></strong>!</span>
                <span><strong>Student ID: <?= htmlspecialchars($_SESSION['student_id']) ?></strong></span>
            </section>
            <section class="current-info">
                <span><?= htmlspecialchars($_SESSION['program']) ?></span>
                <span>Term/Sem: <?= htmlspecialchars($_SESSION['sem']) ?></span>
                <span>School Year: <?= htmlspecialchars($_SESSION['school_year']) ?></span>
            </section>
            
            <?php
            // Get all year levels and semesters up to the student's current year level and semester
            $currentYearLevel = (int)$_SESSION['year_level'];
            $currentSem = $_SESSION['sem'];
            $semOrder = ['1st' => 1, '2nd' => 2];

            // Get all year/sem combinations up to current year/sem
            $yearSemList = [];
            for ($year = 1; $year <= $currentYearLevel; $year++) {
                foreach ($semOrder as $semName => $semNum) {
                    // If last year, only include up to current sem
                    if ($year == $currentYearLevel && $semNum > $semOrder[$currentSem]) {
                        break;
                    }
                    $yearSemList[] = ['year_level' => $year, 'semester' => $semName];
                }
            }
            $yearSemList = array_reverse($yearSemList);
            if (!empty($yearSemList)) {
                foreach ($yearSemList as $term) {
                    $termYearLevel = $term['year_level'];
                    $termSem = $term['semester'];

                    // Get all subjects for this year_level and semester_offered in the student's program
                    $subjStmt = $conn->prepare("
                        SELECT subj.subject_id, subj.subject_code, subj.subject_name, subj.units
                        FROM program_subject ps
                        JOIN subject subj ON ps.subject_id = subj.subject_id
                        WHERE ps.program_id = ?
                        AND ps.year_offered = ?
                        AND ps.semester_offered = ?
                        ORDER BY subj.subject_code
                    ");
                    $subjStmt->bind_param("iis", $_SESSION['program_id'], $termYearLevel, $termSem);
                    $subjStmt->execute();
                    $subjResult = $subjStmt->get_result();

                    if ($subjResult->num_rows > 0) {
                        echo '<div class="term-section">';
                        echo "<h3>{$termYearLevel} Year, {$termSem} Semester</h3>";
                        // Download grades form
                        echo '<form method="POST" action="generate_grades.php">';
                        echo '<input type="hidden" name="semester" value="' . htmlspecialchars($termSem) . '">';
                        echo '<input type="hidden" name="school_year" value="' . htmlspecialchars($_SESSION['school_year']) . '">';
                        echo '<button type="submit"><i class="material-icons">download</i> Download Copy</button>';
                        echo '</form>';
                        echo '<table class="table-style tr:hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subject Code</th>
                                        <th>Description</th>
                                        <th>Units</th>
                                        <th>Final Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>';
                        $counter = 1;
                        $totalUnits = 0;
                        $weightedSum = 0;

                        while ($subject = $subjResult->fetch_assoc()) {
                            // Get grade for this subject and student
                            $gradeStmt = $conn->prepare("
                                SELECT final_grade, scholastic_status
                                FROM student_grades
                                WHERE student_id = ? AND subject_id = ?
                            ");
                            $gradeStmt->bind_param("ii", $_SESSION['student_id'], $subject['subject_id']);
                            $gradeStmt->execute();
                            $gradeResult = $gradeStmt->get_result();
                            $grade = $gradeResult->fetch_assoc();

                            $finalGrade = $grade && $grade['final_grade'] != 0 ? htmlspecialchars($grade['final_grade']) : '';
                            $status = $grade ? htmlspecialchars($grade['scholastic_status']) : '';

                            echo "<tr>
                                    <td>" . $counter++ . "</td>
                                    <td>" . htmlspecialchars($subject['subject_code']) . "</td>
                                    <td>" . htmlspecialchars($subject['subject_name']) . "</td>
                                    <td>" . htmlspecialchars($subject['units']) . "</td>
                                    <td>" . $finalGrade . "</td>
                                    <td>" . $status . "</td>
                                </tr>";

                            if ($grade && $grade['final_grade'] != 0) {
                                $totalUnits += $subject['units'];
                                $weightedSum += $subject['units'] * $grade['final_grade'];
                            }
                        }

                        // calculate gwa and display
                        if ($counter > 1 && $totalUnits > 0) {
                            $gwa = $weightedSum / $totalUnits;
                            echo "<tr class='gwa-row'>
                                    <td colspan='3'><strong>Total Units: $totalUnits</strong></td>
                                    <td colspan='3'><strong>GWA: " . number_format($gwa, 2) . "</strong></td>
                                </tr>";

                            // check if existing
                            $checkGWA = $conn->prepare("
                                SELECT gwa FROM academic_records 
                                WHERE student_id = ? AND year_level = ? AND semester = ?
                            ");
                            $checkGWA->bind_param("iis", $_SESSION['student_id'], $termYearLevel, $termSem);
                            $checkGWA->execute();
                            $checkGWAResult = $checkGWA->get_result();

                            if ($checkGWAResult->num_rows > 0) {
                                // update if existing
                                $updateStudentGWA = $conn->prepare("
                                    UPDATE academic_records 
                                    SET gwa = ? 
                                    WHERE student_id = ? AND year_level = ? AND semester = ?
                                ");
                                $updateStudentGWA->bind_param("diis", $gwa, $_SESSION['student_id'], $termYearLevel, $termSem);
                                $updateStudentGWA->execute();
                            } else {
                                // add if not existing
                                $insertStudentGWA = $conn->prepare("
                                    INSERT INTO academic_records (gwa, student_id, year_level, semester) 
                                    VALUES (?, ?, ?, ?)
                                ");
                                $insertStudentGWA->bind_param("diis", $gwa, $_SESSION['student_id'], $termYearLevel, $termSem);
                                $insertStudentGWA->execute();
                            }
                        } else {
                            echo "<tr><td colspan='6'>No grades available for this term</td></tr>";
                        }
                        echo '</tbody></table></div>';
                    }
                }
            } else {
                echo "<p>No grade records found.</p>";
            }
            ?>
                    <div class="term-section">
                        <table class="table-style tr:hover">
                            <tbody>
                                <?php
                                // get grades for term
                                $termQuery = $conn->prepare("SELECT s.subject_code, s.subject_name, 
                                                            s.units, sg.final_grade, sg.scholastic_status
                                                        FROM student_grades sg
                                                        JOIN subject s ON sg.subject_id = s.subject_id
                                                        WHERE sg.student_id = ? 
                                                        AND sg.semester = ? 
                                                        AND sg.school_year = ?
                                                        ORDER BY s.subject_code");
                                $termQuery->bind_param("iss", $_SESSION['student_id'], $term['semester'], $term['school_year']);
                                $termQuery->execute();
                                $termResults = $termQuery->get_result();
                                
                                $counter = 1;
                                $totalUnits = 0;
                                $weightedSum = 0;
                                
                                while ($grade = $termResults->fetch_assoc()) {
                                    echo "<tr>
                                            <td>" . $counter++ . "</td>
                                            <td>" . htmlspecialchars($grade['subject_code']) . "</td>
                                            <td>" . htmlspecialchars($grade['subject_name']) . "</td>
                                            <td>" . htmlspecialchars($grade['units']) . "</td>
                                            <td>" . ($grade['final_grade'] == 0 ? '' : htmlspecialchars($grade['final_grade'])) . "</td>
                                            <td>" . htmlspecialchars($grade['scholastic_status']) . "</td>
                                        </tr>";
                                    // calculate GWA only if grade is not 0
                                    if ($grade['final_grade'] != 0) {
                                        $totalUnits += $grade['units'];
                                        $weightedSum += $grade['units'] * $grade['final_grade'];
                                    }
                                }
                                
                                // calculate gwa and display
                                if ($counter > 1 && $totalUnits > 0) {
                                    $gwa = $weightedSum / $totalUnits;
                                    echo "<tr class='gwa-row'>
                                            <td colspan='3'><strong>Total Units: $totalUnits</strong></td>
                                            <td colspan='3'><strong>GWA: " . number_format($gwa, 2) . "</strong></td>
                                        </tr>";

                                    // check if existing
                                    $checkGWA = $conn->prepare("
                                        SELECT gwa FROM academic_records 
                                        WHERE student_id = ? AND school_year = ? AND semester = ?
                                    ");
                                    $checkGWA->bind_param("iss", $_SESSION['student_id'], $term['school_year'], $term['semester']);
                                    $checkGWA->execute();
                                    $checkGWAResult = $checkGWA->get_result();

                                    if ($checkGWAResult->num_rows > 0) {
                                        // update if existing
                                        $updateStudentGWA = $conn->prepare("
                                            UPDATE academic_records 
                                            SET gwa = ? 
                                            WHERE student_id = ? AND school_year = ? AND semester = ?
                                        ");
                                        $updateStudentGWA->bind_param("diss", $gwa, $_SESSION['student_id'], $term['school_year'], $term['semester']);
                                        $updateStudentGWA->execute();
                                    } else {
                                        // add if existing
                                        $insertStudentGWA = $conn->prepare("
                                            INSERT INTO academic_records (gwa, student_id, school_year, semester) 
                                            VALUES (?, ?, ?, ?)
                                        ");
                                        $insertStudentGWA->bind_param("diss", $gwa, $_SESSION['student_id'], $term['school_year'], $term['semester']);
                                        $insertStudentGWA->execute();
                                    }
                                } 
                                ?>
                            </tbody>
                        </table>
                    </div>
            
        </div>

        <!-- student program tab -->
        <div id="student-program" class="student-page">
            <h2>Program</h2>
            <h4><?= htmlspecialchars($_SESSION['program'])?></h4>
            <section class="prog-current-info">
                <span>Term/Sem: <?= htmlspecialchars($_SESSION['sem'])?></span>
                <span>School Year: <?= htmlspecialchars($_SESSION['school_year'])?></span>
            </section>

            <section id="table-section">
                <?php
                    $stmt = $conn->prepare("SELECT subj.subject_code, subj.subject_name, subj.units,
                                                    ps.year_offered, ps.semester_offered,
                                                    COUNT(*) OVER(PARTITION BY ps.year_offered, ps.semester_offered)
                                                        AS total_subj_year_sem
                                            FROM program_subject AS ps
                                            INNER JOIN subject AS subj
                                                ON ps.subject_id = subj.subject_id
                                            WHERE ps.program_id=?
                                            ORDER BY ps.year_offered, ps.semester_offered, subj.subject_name;");
                    $stmt->bind_param("i", $_SESSION['program_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    // Group subjects by year_offered only
                    $grouped = [];

                    while ($row = $result->fetch_assoc()) {
                        $year = $row['year_offered'];
                        $sem = $row['semester_offered'];
                        $grouped[$year][$sem][] = $row;
                    }                    

                    // Now render one table per year
                    foreach ($grouped as $year => $semesters) {
                        echo "<h3 class='year-header'>$year Year</h3>";
                        foreach ($semesters as $semester => $subjects) {
                            echo "<h4 class='sem-header'>$semester Semester</h4>";
                            echo "<table class='table-style'>
                                    <thead>
                                        <tr>
                                            <th class='col-subj-code'>Subject Code</th>
                                            <th class='col-desc'>Description</th>
                                            <th class='col-units'>Units</th>
                                            <th class='col-sem'>Semester</th>
                                        </tr>
                                    </thead>
                                    <tbody>";
                    
                            foreach ($subjects as $subject) {
                                echo "<tr>
                                        <td class='row-subj-code'>" . htmlspecialchars($subject['subject_code']) . "</td>
                                        <td class='row-desc'>" . htmlspecialchars($subject['subject_name']) . "</td>
                                        <td class='row-units'>" . htmlspecialchars($subject['units']) . "</td>
                                        <td class='row-sem'>" . htmlspecialchars($subject['semester_offered']) . " Semester</td>
                                      </tr>";
                            }
                    
                            echo "</tbody></table>";
                        }
                    }
                    ?>
            </section>
        </div>

        <!-- student calendar tab -->
        <div id="student-calendar" class="student-page"></div>

        <!-- student profile tab -->
        <div id="student-profile" class="student-page">
            <h2>Student Profile</h2>

            <div id="student-gen-info">
                <section id="user-profile"> <!--Pang ano to change Picture kung pwede-->
                    <span class="material-icons" id="user-profile-icon">account_circle</span>
                    <label for="profile-upload" class="upload-btn">Change Profile</label>
                    <input type="file" id="profile-upload" accept="image/*">
                </section>
                
                <section id="info-table">
                    <table>
                        <tbody>
                            <tr>
                                <td class="firstcol">Firstname</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['firstname'])?></td>

                                <td class="thirdcol">Student ID</td>
                                <td class="fourthcol"><?= htmlspecialchars($_SESSION['student_id'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Middle name</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['middle_name'])?></td>

                                <td class="thirdcol">Year Level</td>
                                <td class="fourthcol"><?= htmlspecialchars($_SESSION['year_level'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">lastname</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['lastname'])?></td>

                                <td class="thirdcol">Department</td>
                                <td class="fourthcol"><?= htmlspecialchars($_SESSION['department'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Date of Birth</td>
                                    <td class="secondcol"><?= htmlspecialchars($_SESSION['birthdate'])?></td>
                                
                                <td class="thirdcol">Program</td>
                                    <td class="fourthcol"><?= htmlspecialchars($_SESSION['program'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Sex</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['sex'])?></td>
                                
                                <td class="thirdcol">Scholastic Status</td>
                                <td class="fourthcol"><?= htmlspecialchars($_SESSION['scho_status'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Address</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['address'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Phone Number</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['phone_number'])?></td>
                            </tr>
                            <tr>
                                <td class="firstcol">Email Address</td>
                                <td class="secondcol"><?= htmlspecialchars($_SESSION['email'])?></td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <section id="">

                </section>
            </div>
        </div>
    </div>
    
    <script>
        const toggleBtn = document.getElementById('toggle-nav');
        const navbar = document.getElementById('navbar');

        toggleBtn.addEventListener('click', () => {
            navbar.classList.toggle('expanded');
            navbar.classList.toggle('collapsed');
        });

        // page navigation logic
        const navLinks = document.querySelectorAll('#navbar a');
        const pages = document.querySelectorAll('.student-page');

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

        // default page
        showPage('student-schedule');
    </script>
</body>
</html>