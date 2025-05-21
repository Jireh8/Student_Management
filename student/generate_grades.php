<?php
require_once('../config.php');
require_once('../libs/tcpdf/tcpdf.php');
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.html?error");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['semester']) && isset($_POST['school_year'])) {
    $semester = $_POST['semester'];
    $yearLevel = $_POST['year_level'];
    $studentId = $_SESSION['student_id'];
    $studentName = $_SESSION['firstname'] . ' ' . $_SESSION['lastname'];

    //echo "<script>console.log('Semester: " . $semester . ", Year Level: " . $yearLevel . "');</script>";
    // Query the grades
    $termQuery = $conn->prepare("
        SELECT s.subject_code, s.subject_name, s.units, sg.final_grade, sg.scholastic_status
        FROM student_grades sg
        JOIN subject s ON sg.subject_id = s.subject_id
        WHERE sg.student_id = ?
        AND sg.semester = ?
        AND sg.grade_year_level = ?
        ORDER BY s.subject_code
    ");
    $termQuery->bind_param("iss", $studentId, $semester, $yearLevel);

    $termQuery->execute();
    $termResults = $termQuery->get_result();

    // Create custom PDF class with header and footer
    class MYPDF extends TCPDF {
        public function Header() {
            // Set font for the header
            $this->SetFont('helvetica', 'B', 16);
            
            // Reset text color
            $this->SetTextColor(0);
            
            // School name
            $this->SetFont('helvetica', 'B', 16);
            $this->SetY(10);
            $this->Cell(0, 8, 'Xydle University', 0, 1, 'C');

            // School address
            $this->SetFont('helvetica', '', 9);
            $this->SetY(18);
            $this->Cell(0, 5, '1092 Street, Dasma, NCR, 9012', 0, 1, 'C');
            $this->SetY(23);
            $this->Cell(0, 5, 'Phone: (123) 456-7890 | Email: info@xydleuniversity.edu', 0, 1, 'C');

            // Line separator
            $this->SetLineStyle(array('width' => 0.5, 'color' => array(0, 63, 127)));
            $this->Line(15, 32, 195, 32);
        }

        public function Footer() {
            // Position at 15 mm from bottom
            $this->SetY(-15);
            // Set font
            $this->SetFont('helvetica', 'I', 8);
            // Add centered text
            $this->Cell(0, 10, 'This is an official academic record. If found, please return to the Registrar\'s Office.', 0, false, 'C');
            $this->Ln(4);
            // Page number
            $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C');
        }
    }

    // Start TCPDF with custom class
    $pdf = new MYPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('XYDLE UNIVERSITY');
    $pdf->SetTitle('Student Grade Report');
    $pdf->SetSubject('Academic Record');
    $pdf->SetKeywords('grades, transcript, academic, record');
    
    // Set margins
    $pdf->SetMargins(15, 40, 15);
    $pdf->SetAutoPageBreak(TRUE, 25);
    
    // Add page
    $pdf->AddPage();
    
    // Student info section - Title first
    $pdf->SetFont('helvetica', 'B', 14);
    $pdf->Cell(0, 10, 'OFFICIAL GRADE REPORT', 0, 1, 'C');
    
    // Add spacing after title
    $pdf->Ln(5);
    
    // Student info in a styled box - moved below the title
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Rect(15, $pdf->GetY(), 180, 30, 'F');
    $pdf->SetLineStyle(array('width' => 0.3, 'color' => array(200, 200, 200)));
    $pdf->Rect(15, $pdf->GetY(), 180, 30);
    
    // Student info content - adjust Y position based on current position
    $currentY = $pdf->GetY() + 5;
    $pdf->SetXY(20, $currentY);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 7, 'Student Name:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, $_SESSION['lastname'] . ', ' . $_SESSION['firstname'] . ' ' . (isset($_SESSION['middle_name']) ? $_SESSION['middle_name'] : ''), 0, 0);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(25, 7, 'Student ID:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, $_SESSION['student_id'], 0, 1, 'L');
    
    $pdf->SetX(20);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 7, 'Semester:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, $semester, 0, 0);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(25, 7, 'Academic Year:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, $yearLevel, 0, 1, 'L'); //CHECK DIS AGAIN
    
    // Additional student info if available
    $pdf->SetX(20);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(30, 7, 'Program:', 0, 0);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(80, 7, isset($_SESSION['program']) ? $_SESSION['program'] : 'Not specified', 0, 0);
    
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(25, 7, 'Year Level:', 0, 0, 'R');
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(40, 7, isset($_SESSION['year_level']) ? $_SESSION['year_level'] : 'Not specified', 0, 1, 'L');
    
    // Add spacing - advance past the info box
    $pdf->Ln(10);
    
    // Grades table with better styling
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 10, $yearLevel .' Year, ' . $semester . " Semester", 0, 1, 'L'); //CHECK DIS AGAIN
    
    // Table header colors
    $pdf->SetFillColor(0, 63, 127); // Dark blue
    $pdf->SetTextColor(255);
    $pdf->SetDrawColor(200, 200, 200);
    $pdf->SetLineWidth(0.3);
    $pdf->SetFont('helvetica', 'B', 9);
    
    // Table Header
    $pdf->Cell(10, 10, '#', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Code', 1, 0, 'C', true);
    $pdf->Cell(80, 10, 'Subject Description', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Units', 1, 0, 'C', true);
    $pdf->Cell(20, 10, 'Final Grade', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Status', 1, 1, 'C', true);
    
    // Table content
    $pdf->SetFillColor(245, 245, 245); // Light gray for alternating rows
    $pdf->SetTextColor(0);
    $pdf->SetFont('helvetica', '', 9);
    
    $counter = 1;
    $totalUnits = 0;
    $weightedSum = 0;
    $rowColor = false;
    
    while ($row = $termResults->fetch_assoc()) {
        $pdf->Cell(10, 8, $counter++, 1, 0, 'C', $rowColor);
        $pdf->Cell(25, 8, $row['subject_code'], 1, 0, 'C', $rowColor);
        $pdf->Cell(80, 8, $row['subject_name'], 1, 0, 'L', $rowColor);
        $pdf->Cell(20, 8, $row['units'], 1, 0, 'C', $rowColor);
        $pdf->Cell(20, 8, ($row['final_grade'] == 0 ? 'N/A' : $row['final_grade']), 1, 0, 'C', $rowColor);
        
        // Status cell with color based on value
        if ($row['scholastic_status'] == 'Passed') {
            $pdf->SetTextColor(0, 128, 0); // Green for passed
        } elseif ($row['scholastic_status'] == 'Failed') {
            $pdf->SetTextColor(194, 0, 0); // Red for failed
        } elseif ($row['scholastic_status'] == 'Incomplete') {
            $pdf->SetTextColor(204, 102, 0); // Orange for incomplete
        } else {
            $pdf->SetTextColor(0); // Default black
        }
        
        $pdf->Cell(25, 8, $row['scholastic_status'], 1, 1, 'C', $rowColor);
        $pdf->SetTextColor(0); // Reset text color
        
        if ($row['final_grade'] != 0) {
            $totalUnits += $row['units'];
            $weightedSum += $row['units'] * $row['final_grade'];
        }
        
        $rowColor = !$rowColor; // Alternate row colors
    }
    
    // Summary section with styled box
    if ($counter > 1) {
        if ($totalUnits > 0) {
            $gwa = number_format($weightedSum / $totalUnits, 2);
            
            // Summary box - positioned at the right with left-aligned content
            $pdf->Ln(5);

            // Calculate right position (page width minus box width minus right margin)
            $pageWidth = $pdf->getPageWidth();
            $boxWidth = 90; // Width of summary box
            $rightMargin = 15; // Right margin
            $boxX = $pageWidth - $boxWidth - $rightMargin;

            // Draw the box at the far right
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Rect($boxX, $pdf->GetY(), $boxWidth, 16, 'F');
            $pdf->SetLineStyle(array('width' => 0.3, 'color' => array(200, 200, 200)));
            $pdf->Rect($boxX, $pdf->GetY(), $boxWidth, 16);

            // Set position for text - with a small left padding inside the box
            $textX = $boxX + 5;
            $pdf->SetXY($textX, $pdf->GetY() + 3);

            // Total Units - left aligned
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(60, 5, 'Total Units: ' . $totalUnits, 0, 1, 'L');

            // General Weighted Average - left aligned
            $pdf->SetX($textX);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(45, 5, 'General Weighted Average: ', 0, 0, 'L');

            // GWA with color based on value
            if ($gwa < 1.75) {
                $pdf->SetTextColor(0, 128, 0); // Green for excellent
            } elseif ($gwa < 2.25) {
                $pdf->SetTextColor(0, 102, 204); // Blue for good
            } elseif ($gwa < 3.0) {
                $pdf->SetTextColor(204, 102, 0); // Orange for satisfactory
            } else {
                $pdf->SetTextColor(194, 0, 0); // Red for needs improvement
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(20, 5, $gwa, 0, 1, 'L');

            $pdf->SetTextColor(0); // Reset text color
        } else {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 10, 'No graded courses available for this term.', 0, 1, 'C');
        }
    } else {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'No courses found for this term.', 1, 1, 'C');
    }
    
    // Remarks section
    $pdf->Ln(10);
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(0, 8, 'Remarks:', 0, 1);
    $pdf->SetFont('helvetica', 'I', 9);
    $pdf->MultiCell(0, 5, 'This report is an official academic record issued by the University. All grades are final unless otherwise noted. For any discrepancies, please contact the Registrar\'s Office within 30 days of issuance.', 0, 'L');
    
    // Signature section
    $pdf->Ln(15);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Line(25, $pdf->GetY(), 75, $pdf->GetY());
    $pdf->Line(120, $pdf->GetY(), 170, $pdf->GetY());
    $pdf->SetXY(25, $pdf->GetY() + 1);
    $pdf->Cell(50, 5, 'Registrar', 0, 0, 'C');
    $pdf->SetXY(120, $pdf->GetY());
    $pdf->Cell(50, 5, 'Date Issued', 0, 1, 'C');
    
    // Add an authenticity note
    $pdf->SetY($pdf->GetY() + 10);
    $pdf->SetFont('helvetica', 'I', 8);
    $pdf->Cell(0, 5, 'This document contains security features. A black line will appear when photocopied.', 0, 1, 'C');
    
    $pdf->Output('Grade_Report.pdf', 'I'); // 'I' to display in browser, use 'D' to force download
} else {
    echo "Invalid request. Please try again.";
}
?>