<?php 
    include '../config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

    <style>
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

        /*Header - univ*/
        #univ-header {
            position: fixed;
            width: 100%;
            background-color: #12181e;
            color: white;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 10px 0;
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


        /*Navbar*/
        #navbar {
            position: fixed;
            top: 80px;
            left: 0;
            height: 90vh;
            width: 60px;
            background-color: #12181e;
            overflow-x: hidden;
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            z-index: 1000;
        }
        #navbar li button {
            background: transparent;
            color: white;
            border: none;
            width: 100%;
            padding: 10px 20px 20px 0;
            display: flex;
            align-items: center;
            font-size: 16px;
            cursor: pointer;
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
        }

        .material-icons { font-size: 25px;}

        .nav-label {
            margin-left: 10px;
        }

        #navbar.collapsed .nav-label {
            display: none;
        }
        /*Subpages*/
        .display-page {
            display: none;
            margin-top: 85px;
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
        #navbar.expanded ~ .display-page {
            margin-left: 220px;
        }

        /* Toggle button */
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
        /* header container */
            #homepage {
                    height: 60vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    /*container*/
                    margin-left: 60px;
                    padding: 20px;
                    flex-grow: 1;
                    transition: margin-left 0.3s ease;
            }
            #school-calendar {
                    height: 60vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    /*container*/
                    margin-left: 60px;
                    padding: 20px;
                    flex-grow: 1;
                    transition: margin-left 0.3s ease;
            }
            #student-management {
                    height: 60vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    /*container*/
                    margin-left: 60px;
                    padding: 20px;
                    flex-grow: 1;
                    transition: margin-left 0.3s ease;
            }
            #instructor-management {
                    height: 60vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    /*container*/
                    margin-left: 60px;
                    padding: 20px;
                    flex-grow: 1;
                    transition: margin-left 0.3s ease;
            }
            #subject-management {
                    height: 60vh;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    /*container*/
                    margin-left: 60px;
                    padding: 20px;
                    flex-grow: 1;
                    transition: margin-left 0.3s ease;
            }
        /* Shared Section Title Style and container */
            .title-style {
                font-size: 28px;
                font-weight: 700;
                margin: 0;
            }
            .title-container {
                width: 90%;
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 20px;
                border-bottom: 2px solid #12181e;
            }
        /* Shared Table Style */
            .table-style {
                width: 90%;
                margin: 20px auto;
                border-collapse: collapse;
                font-size: 14px;
                text-align: center;
            }

            .table-style th {
                background-color: #27ac1f;
                color: white;
                padding: 10px 20px;
                font-weight: bold;
                border: 1px solid #f9f9f9;
            }

            .table-style th:first-child {
                border-radius: 15px 0 0 0;
            }

            .table-style th:last-child {
                border-radius: 0 15px 0 0;
            }

            .table-style td {
                background-color: white;
                padding: 10px;
                border: 1px solid #ddd;
            }

            .table-style tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .table-style tr:hover {
                background-color: #f1f1f1;
            }
        /* Button Style */
            .btn-style {
                background-color: #27ac1f;
                color: white;
                border: none;
                padding: 10px 20px;
                font-size: 16px;
                cursor: pointer;
                border-radius: 5px;
                transition: all 0.3s ease;
            }
            .btn-style:hover {
                background-color: #1f8c18;
                transform: translateY(-1px);
            }

            .btn-style:active {
                transform: translateY(0);
            }
            
    </style>
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
                                        echo "<button id='edit-student'>Edit</button>";
                                        echo "<button id ='delete-student'>Delete</button>";
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
        
    
    <script>
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

        // default page
        showPage('student-management');
    </script>
</body>
</html>