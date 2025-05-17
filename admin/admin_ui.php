<?php 
    include '../config.php';
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
                <li><a href="#homepage"><span class="material-icons">home</span>
                    <span class="nav-label"> Home</span></a></li>
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
        
        <div id="homepage" class="display-page">
            <h2 class = "title-style">Home Page</h2>
        </div>

        <div id="school-calendar" class="display-page">
            <h2 class = "title-style">School Calendar</h2>
        </div>

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
                            echo "<tr><td colspan='8'>No students found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>

        <div id="instructor-management" class="display-page">
            <h2 class = "title-style">Instructor Management</h2>
        </div>

        <div id="subject-management" class="display-page">
            <h2 class = "title-style">Subject Management</h2>
        </div>
    </div>
    <!-- MODALS SECTION -->
    <!-- Student Modals -->
        <!-- add student modal -->
        <div id="add-student-modal" class="modal" style="display:none;">
            <div class="modal-content">
                <span class="close" id="close-add-student">&times;</span>
                <h2>Add Student</h2>
                <form id="add-student-form" action="add_student.php" method="POST">
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
                    <div class="form-style" style="grid-row:5;grid-column:1">
                        <label for="address">Address*</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:1">
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
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
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

        // add student modal
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

        // default page
        showPage('student-management');
    </script>
</body>
</html>