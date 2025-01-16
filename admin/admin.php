<?php
session_start(); // Ensure session is started
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_student'])) {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];

    // Insert only the static fields into the database
    $stmt = $conn->prepare("INSERT INTO student (first_name, middle_name, last_name, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first_name, $middle_name, $last_name, $email);

    if ($stmt->execute()) {
        echo "Student added successfully.<br>";
    } else {
        echo "Error adding student: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch all students
$query = "SELECT * FROM student";
$result = mysqli_query($conn, $query);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="sidebar">

        <center>
            <img style="width: 45%;" src="../pictures/bsitlogo.png" alt="">
        </center>
        <h2> Admin Panel</h2>
        <a href="#" id="dashboard-link" data-section="dashboard" onclick="highlightActive(this); loadSection('dashboard');">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        <a href="#" id="student-list-link" data-section="studentList" onclick="highlightActive(this); loadSection('studentList');">
            <i class="bi bi-people-fill"></i> Student List
        </a>
        <a href="#" id="report-link" data-section="report" onclick="highlightActive(this); loadSection('report');">
            <i class="bi bi-file-earmark-text"></i> Generate Reports
        </a>
        <a href="announcement.php" id="announcement-link" onclick="highlightActive(this);">
            <i class="bi bi-megaphone-fill"></i> Announcement
        </a>
        <a href="../form.php" id="form-link" onclick="highlightActive(this);">
            <i class="bi bi-ui-checks-grid"></i> Form
        </a>
        <a href="adminlogout.php" id="logout-link" onclick="highlightActive(this);">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>



    <?php
    $firstYearQuery = "SELECT COUNT(*) AS first_year FROM student WHERE year_level = '1st year' OR year_level = 'First Year'";
    $secondYearQuery = "SELECT COUNT(*) AS second_year FROM student WHERE year_level = '2nd year' OR year_level = 'Second Year'";
    $thirdYearQuery = "SELECT COUNT(*) AS third_year FROM student WHERE year_level = '3rd year' OR year_level = 'Third Year'";
    $fourthYearQuery = "SELECT COUNT(*) AS fourth_year FROM student WHERE year_level = '4th year' OR year_level = 'Fourth Year'";

    $totalStudentsQuery = "SELECT COUNT(*) AS total_students FROM student";
    $firstYearResult = mysqli_query($conn, $firstYearQuery);
    $secondYearResult = mysqli_query($conn, $secondYearQuery);
    $thirdYearResult = mysqli_query($conn, $thirdYearQuery);
    $fourthYearResult = mysqli_query($conn, $fourthYearQuery);
    $totalStudentsResult = mysqli_query($conn, $totalStudentsQuery);

    $firstYearCount = mysqli_fetch_assoc($firstYearResult)['first_year'];
    $secondYearCount = mysqli_fetch_assoc($secondYearResult)['second_year'];
    $thirdYearCount = mysqli_fetch_assoc($thirdYearResult)['third_year'];
    $fourthYearCount = mysqli_fetch_assoc($fourthYearResult)['fourth_year'];

    $totalStudents = mysqli_fetch_assoc($totalStudentsResult)['total_students'];
    ?>
    <div class="main-content">

        <div id="dashboardSection" class="section">
            <h1>Welcome to the Dashboard</h1>

            <div class="boxes">
                <div class="box">
                    <h3><i class="bi bi-people-fill"></i> Total Students</h3>
                    <p><?= $totalStudents; ?></p>
                </div>
                <div class="box">
                    <h3><i class="bi bi-person-fill"></i> 1st Year Students</h3>
                    <p><?= $firstYearCount; ?></p>
                </div>
                <div class="box">
                    <h3><i class="bi bi-person-fill"></i> 2nd Year Students</h3>
                    <p><?= $secondYearCount; ?></p>
                </div>
                <div class="box">
                    <h3><i class="bi bi-person-fill"></i> 3rd Year Students</h3>
                    <p><?= $thirdYearCount; ?></p>
                </div>
                <div class="box">
                    <h3><i class="bi bi-person-fill"></i> 4th Year Students</h3>
                    <p><?= $fourthYearCount; ?></p>
                </div>
            </div>
        </div>




        <div id="studentListSection" class="section" style="display:none;">
            <div class="header">
                <h2>Registered Students</h2>
                <a href="AddStudent.php">
                    <button class="btn-add">Add a Student</button>
                </a>
            </div>
            <div class="search-container">
                <input
                    type="text"
                    id="searchInput"
                    class="search-box"
                    placeholder="Search by name or email"
                    onkeyup="liveSearch()" />
            </div>
            <div class="table-container">
                <table id="studentTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Profile Image</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr onclick="redirectToUpdate(<?= $row['id']; ?>)">
                                <td><?= $row['id']; ?></td>
                                <td>
                                    <?php if (!empty($row['profile_image'])): ?>
                                        <img src="<?= htmlspecialchars('../' . $row['profile_image']); ?>" alt="Profile Image" width="50" height="50">
                                    <?php else: ?>
                                        <img src="default-profile.png" alt="Default Profile" width="50" height="50">
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['first_name']); ?></td>
                                <td><?= htmlspecialchars($row['middle_name']); ?></td>
                                <td><?= htmlspecialchars($row['last_name']); ?></td>
                                <td><?= htmlspecialchars($row['email']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="reportSection" class="section" style="display:none;">
            <h2>Student Report</h2>

            <!-- Search Input -->
            <input
                type="text"
                id="reportSearchInput"
                placeholder="Search by Status, Section, or Year Level"
                onkeyup="searchReportTable()" />

            <button onclick="resetSearch()"><i class="bi bi-x-lg"></i></button>
            <!-- Print Button -->
            <button onclick="printTable()">Print Table</button>
            <button onclick="downloadTableAsCSV()">Download</button>

            <table id="reportTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Profile Image</th>
                        <th>First Name</th>
                        <th>Middle Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Year Level</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $studentQuery = "SELECT * FROM student";
                    $studentResult = mysqli_query($conn, $studentQuery);

                    if ($studentResult && mysqli_num_rows($studentResult) > 0) {
                        while ($row = mysqli_fetch_assoc($studentResult)) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>";
                            if (!empty($row['profile_image'])) {
                                echo "<img src='../" . htmlspecialchars($row['profile_image']) . "' alt='Profile Image' width='50' height='50'>";
                            } else {
                                echo "<img src='default-profile.png' alt='Default Profile' width='50' height='50'>";
                            }
                            echo "</td>";
                            echo "<td>" . htmlspecialchars($row['first_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['middle_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['last_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
                            echo "<td>" . " Section " . htmlspecialchars($row['section']) . "</td>"; // Append " Section"
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9'>No data found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <script>
            function highlightActive(element) {
                // Remove 'active' class from all sidebar links
                const sidebarLinks = document.querySelectorAll('.sidebar a');
                sidebarLinks.forEach(link => link.classList.remove('active'));

                // Add 'active' class to the clicked link
                element.classList.add('active');
            }

            function downloadTableAsCSV() {
                const table = document.getElementById("reportTable");
                const rows = Array.from(table.querySelectorAll("tr"));
                const visibleRows = rows.filter(row => row.style.display !== "none"); // Get only visible rows
                let csvContent = "";

                // Loop through visible rows and construct the CSV content
                visibleRows.forEach(row => {
                    const cells = Array.from(row.querySelectorAll("th, td"));
                    const rowContent = cells
                        .map(cell => {
                            // Clean up content and escape double quotes
                            return `"${cell.innerText.replace(/"/g, '""')}"`;
                        })
                        .join(",");
                    csvContent += rowContent + "\n";
                });

                // Create a Blob with the CSV content and trigger a download
                const blob = new Blob([csvContent], {
                    type: "text/csv"
                });
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = "BSIT Report.csv";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
        </script>


        <script>
            function searchReportTable() {
                const query = document.getElementById('reportSearchInput').value.toLowerCase().trim();
                const rows = document.querySelectorAll('#reportTable tbody tr');

                rows.forEach(row => {
                    const firstName = row.cells[2]?.textContent.trim().toLowerCase() || '';
                    const middleName = row.cells[3]?.textContent.trim().toLowerCase() || '';
                    const lastName = row.cells[4]?.textContent.trim().toLowerCase() || '';
                    const email = row.cells[5]?.textContent.trim().toLowerCase() || '';
                    const status = row.cells[6]?.textContent.trim().toLowerCase() || '';
                    const yearLevel = row.cells[7]?.textContent.trim().toLowerCase() || '';
                    const section = row.cells[8]?.textContent.trim().toLowerCase() || '';

                    // Check if query matches any column or matches the full "Section" phrase
                    const matchesOtherColumns =
                        firstName.includes(query) ||
                        middleName.includes(query) ||
                        lastName.includes(query) ||
                        email.includes(query) ||
                        status === query || // Exact match for "status" (e.g., "Regular" or "Irregular")
                        yearLevel.includes(query);

                    const matchesSection = section.includes(query); // Match full section (e.g., "Section B")

                    // Display the row if it matches
                    if (matchesSection || matchesOtherColumns) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            function resetSearch() {
                document.getElementById('reportSearchInput').value = '';
                const rows = document.querySelectorAll('#reportTable tbody tr');

                rows.forEach(row => {
                    row.style.display = '';
                });
            }

            function printTable() {
                const originalContent = document.body.innerHTML;
                const tableContent = document.getElementById('reportTable').outerHTML;

                document.body.innerHTML = `
            <html>
                <head>
                    <title>Print Student Report</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        table, th, td {
                            border: 1px solid black;
                        }
                        th{
                             background-color: #0056b3;
                        }
                        th, td {
                            padding: 8px;
                            text-align: left;
                        }
                    </style>
                </head>
                <body>
                    <h1>Student Report</h1>
                    ${tableContent}
                </body>
            </html>
        `;

                // Trigger print
                window.print();

                // Restore original content after printing
                document.body.innerHTML = originalContent;
                window.location.reload(); // Reload the page to restore functionality
            }
        </script>
        <script>
            function liveSearch() {
                const query = document.getElementById('searchInput').value.toLowerCase();
                const rows = document.querySelectorAll('#studentTable tbody tr');
                rows.forEach(row => {
                    const cells = row.getElementsByTagName('td');
                    const rowText = Array.from(cells).map(cell => cell.textContent.toLowerCase()).join(' ');
                    row.style.display = rowText.includes(query) ? '' : 'none';
                });
            }

            function loadSection(section) {
                const sections = document.querySelectorAll('.section');
                sections.forEach(sec => (sec.style.display = 'none'));
                document.getElementById(section + 'Section').style.display = 'block';
            }

            function redirectToUpdate(studentId) {
                window.location.href = `studentUpdateForm.php?id=${studentId}`;
            }
        </script>
</body>

</html>