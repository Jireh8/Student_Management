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
                    <div class="form-style" style="grid-row:5;grid-column:1">
                        <label for="edit-address">Address*</label>
                        <input type="text" id="edit-address" name="address" required>
                    </div>
                    <div class="form-style" style="grid-row:6;grid-column:1">
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
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
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
        async function refreshStudentTable() {
            try {
                const response = await fetch('get_students.php');
                const html = await response.text();
                document.querySelector('#student-management table tbody').innerHTML = html;
            } catch (error) {
                console.error('Failed to refresh table:', error);
            }
        }
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
                    refreshStudentTable();
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
        // edit student modal - updated to populate form with student data
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const studentId = row.cells[0].textContent;
                
                // Show loading state
                const modal = document.getElementById('edit-student-modal');
                modal.style.display = 'block';
                modal.querySelector('.modal-content').innerHTML = '<p>Loading student data...</p>';
                
                // Fetch student data via AJAX
                fetch(`get_student.php?student_id=${studentId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if(data.success) {
                            // Restore the modal content
                            modal.querySelector('.modal-content').innerHTML = `
                                <span class="close" id="close-edit-student">&times;</span>
                                <h2>Edit Student</h2>
                                <form id="edit-student-form" action="student_mngmnt.php" method="POST">
                                    <input type="hidden" name="action" value="edit">
                                    <input type="hidden" id="edit-student-id" name="student_id" value="${data.student_id}">
                                    <input type="hidden" id="edit-contact-id" name="contact_id" value="${data.contact_id}">
                                    <!-- Rest of your form fields populated with data -->
                                    <div class="form-style">
                                        <label for="edit-lastname" style="grid-row:1;grid-column:1">Last Name*</label>
                                        <input type="text" id="edit-lastname" name="lastname" value="${data.lastname}" required>
                                    </div>
                                    <!-- Include all other form fields similarly -->
                                </form>
                            `;
                            
                            // Reattach close event
                            document.getElementById('close-edit-student').addEventListener('click', () => {
                                modal.style.display = 'none';
                            });
                        } else {
                            modal.querySelector('.modal-content').innerHTML = `
                                <span class="close" id="close-edit-student">&times;</span>
                                <h2>Error</h2>
                                <p>${data.message || 'Failed to load student data'}</p>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modal.querySelector('.modal-content').innerHTML = `
                            <span class="close" id="close-edit-student">&times;</span>
                            <h2>Error</h2>
                            <p>Failed to load student data. Please try again.</p>
                        `;
                    });
            });
        });
        // edit student close
        const closeEditStudent = document.getElementById('close-edit-student');
        closeEditStudent.addEventListener('click', () => {
            document.getElementById('edit-student-modal').style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target == document.getElementById('edit-student-modal')) {
                document.getElementById('edit-student-modal').style.display = 'none';
            }
        });

        // delete student modal - updated to handle confirmation
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const studentId = row.cells[0].textContent;
                const studentName = row.cells[1].textContent;
                
                // Set student ID for deletion
                document.getElementById('confirm-delete').dataset.studentId = studentId;
                
                // Update confirmation message
                document.querySelector('#delete-student-modal p').textContent = 
                    `Are you sure you want to delete ${studentName} (ID: ${studentId})?`;
                    
                // Show modal
                document.getElementById('delete-student-modal').style.display = 'block';
            });
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
        // default page
        showPage('student-management');

        function showToast(message, isSuccess = true) {
            const toast = document.createElement('div');
            toast.className = `toast ${isSuccess ? 'success' : 'error'}`;
            toast.textContent = message;
            document.body.appendChild(toast);
            
            setTimeout(() => toast.remove(), 3000);
        }

        showToast(result); // For success
        showToast('Error: ' + error.message, false); // For errors
        </script>
</body>
</html>