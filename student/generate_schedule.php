<?php
    include '../config.php';
    require_once('../libs/tcpdf/tcpdf.php');

    session_start();
    if (!isset($_SESSION['student_id'])) {
        header("Location: login.html?error");
        exit();
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $sql = $conn->prepare("SELECT subj.subject_code, subj.subject_name,
                            subj.units, ins.instructor_name, 
                                sec.section_name, sc.day_of_week, 
                                sc.start_time, sc.end_time, sc.room_number
                        FROM schedule AS sc
                        INNER JOIN subject_instructor_section AS sis
                                USING(sis_id)
                        INNER JOIN subject AS subj
                                ON sis.subject_id = subj.subject_id
                            INNER JOIN instructor AS ins
                                ON sis.instructor_id = ins.instructor_id
                            INNER JOIN Section AS sec
                                ON sis.section_id = sec.section_id;");
    $sql->execute();
    $result = $sql->get_result();
    }

    // Create PDF with enhanced styling
    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

    // Set document properties
    $pdf->SetCreator('Xydle University');
    $pdf->SetAuthor('Registrar');
    $pdf->SetTitle('Student Schedule Report');

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    $pdf->AddPage();

    // University brand color
    $primaryColor = array(18, 24, 30);

    // Custom header with university branding
    $pdf->SetFont('helvetica', 'B', 20);
    $pdf->SetTextColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->Cell(0, 10, 'XYDLE UNIVERSITY', 0, 1, 'C');

    $pdf->SetFont('helvetica', '', 14);
    $pdf->Cell(0, 8, 'Office of the University Registrar', 0, 1, 'C');

    // Add university logo placeholder
    $pdf->Ln(5);
    $pdf->SetDrawColor($primaryColor[0], $primaryColor[1], $primaryColor[2]);
    $pdf->SetLineWidth(0.5);
    $pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
    $pdf->Ln(10);

    // Name and Student ID on the same line 
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 7, 'Name:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $nameWidth = 100;
    $pdf->Cell($nameWidth, 7, $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . $_SESSION['middle_name'], 0, 0);

    // Right-aligned Student ID on the same line
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(25, 7, 'Student ID:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, $_SESSION['student_id'], 0, 1, 'R');

    // Semester and Year on the same line 
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 7, 'Sem:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell($nameWidth, 7, $_SESSION['sem'], 0, 0);

    // Right-aligned Year on the same line as Semester
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(15, 7, 'Year:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(25, 7, $_SESSION['school_year'], 0, 1, 'R');

    // Program on its own line
    $pdf->SetFont('helvetica', '', 11);
    $pdf->Cell(40, 7, 'Program:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->Cell(0, 7, $_SESSION['program'], 0, 1);

    $pdf->Ln(5);

    $html = '
    <style>
        h2 {
            font-family: helvetica;
            font-size: 14pt;
            color: #12181e;
            margin-bottom: 8px;
            border-bottom: 2px solid #12181e;
            padding-bottom: 5px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 9pt;
            font-family: helvetica;
            margin-top: 10px;
        }
        tr.heading {
            background-color: #12181e;
            color: white;
        }
        tr.heading th {
            font-weight: bold;
            padding: 6px 4px;
        }
        td {
            border: 1px solid #ddd;
            padding: 6px 4px;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #e8e8f7;
        }
        .footer-note {
            font-size: 8pt;
            color: #666;
            text-align: center;
            margin-top: 15px;
            font-style: italic;
        }
    </style>

    <h2 style="text-align: center;">CLASS SCHEDULE</h2>';

    // Create table using HTML writer with explicit column widths
    $html .= '<table border="1" cellpadding="4">
        <tr class="heading">
            <th width="9%" align="center">Code</th>
            <th width="20%" align="center">Subject</th>
            <th width="6%" align="center">Units</th>
            <th width="20%" align="center">Faculty</th>
            <th width="9%" align="center">Section</th>
            <th width="9%" align="center">Day</th>
            <th width="10%" align="center">Start</th>
            <th width="10%" align="center">End</th>
            <th width="7%" align="center">Room</th>
        </tr>';

    // Calculate total units
    $totalUnits = 0;

    while ($row = $result->fetch_assoc()) {
        $totalUnits += (int)$row['units'];
        $html .= '<tr>
            <td width="9%" align="center">' . htmlspecialchars($row['subject_code']) . '</td>
            <td width="20%">' . htmlspecialchars($row['subject_name']) . '</td>
            <td width="6%" align="center">' . htmlspecialchars($row['units']) . '</td>
            <td width="20%">' . htmlspecialchars($row['instructor_name']) . '</td>
            <td width="9%" align="center">' . htmlspecialchars($row['section_name']) . '</td>
            <td width="9%" align="center">' . htmlspecialchars($row['day_of_week']) . '</td>
            <td width="10%" align="center">' . htmlspecialchars($row['start_time']) . '</td>
            <td width="10%" align="center">' . htmlspecialchars($row['end_time']) . '</td>
            <td width="7%" align="center">' . htmlspecialchars($row['room_number']) . '</td>
        </tr>';
    }

    $html .= '</table>';

    // Add note at bottom
    $html .= '<p class="footer-note">This document is an official record from the Office of the University Registrar. 
            For questions or concerns, please contact registrar@xydle.edu</p>';

    // Output HTML to PDF
    $pdf->writeHTML($html, true, false, true, false, '');

    // Output the PDF to download
    $pdf->Output('Xydle_University_Schedule.pdf', 'I');
        exit;
?>