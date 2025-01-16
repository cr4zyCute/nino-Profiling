<?php
include '../database/db.php';

// Ensure `id` is provided in the query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid student ID.');
}

$student_id = (int)$_GET['id'];

// Fetch student data
$query = "SELECT * FROM student WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
if (!$student) {
    die('Student not found.');
}

// Fetch dynamic fields
$formFields = [];
$stmt = $conn->prepare("SELECT id, field_name FROM form_fields");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $formFields[] = $row;
}
$stmt->close();

// Fetch values for dynamic fields
$additionalFields = [];
foreach ($formFields as $field) {
    $stmt = $conn->prepare("SELECT field_value FROM student_additional_fields WHERE student_id = ? AND field_name = ?");
    $stmt->bind_param("is", $student_id, $field['field_name']);
    $stmt->execute();
    $stmt->bind_result($field_value);
    if ($stmt->fetch()) {
        $additionalFields[$field['field_name']] = $field_value;
    } else {
        $additionalFields[$field['field_name']] = ''; // Default empty if no value exists
    }
    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $status = trim($_POST['status']);
    $year_level = trim($_POST['year_level']);
    $section = trim($_POST['section']);
    $dynamic_fields = [];
    $profile_image = $student['profile_image']; // Retain current profile image by default

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create upload directory if not exists
        }

        $image_file = basename($_FILES['profile_image']['name']);
        $unique_image_name = uniqid() . "_" . $image_file;
        $target_file = $upload_dir . $unique_image_name;

        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($image_file_type, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        }

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            if (!empty($student['profile_image']) && file_exists("../" . $student['profile_image'])) {
                unlink("../" . $student['profile_image']); // Delete old profile image
            }
            $profile_image = "uploads/" . $unique_image_name;
        } else {
            die("Failed to upload profile image.");
        }
    }

    // Update main student details
    $update_query = "UPDATE student SET first_name = ?, middle_name = ?, last_name = ?, email = ?, profile_image = ?, status = ?, year_level = ?, section = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param('ssssssssi', $first_name, $middle_name, $last_name, $email, $profile_image, $status, $year_level, $section, $student_id);
    if (!$stmt->execute()) {
        die("Failed to update student details: " . $stmt->error);
    }

    // Process dynamic fields
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['first_name', 'middle_name', 'last_name', 'email', 'profile_image', 'status', 'year_level', 'section', 'id'])) {
            $dynamic_fields[$key] = trim($value);
        }
    }

    foreach ($dynamic_fields as $field_name => $field_value) {
        // Check if the field already exists
        $check_query = "SELECT id FROM student_additional_fields WHERE student_id = ? AND field_name = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param('is', $student_id, $field_name);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update existing field
            $update_dynamic_query = "UPDATE student_additional_fields SET field_value = ? WHERE student_id = ? AND field_name = ?";
            $stmt = $conn->prepare($update_dynamic_query);
            $stmt->bind_param('sis', $field_value, $student_id, $field_name);
        } else {
            // Insert new field
            $insert_dynamic_query = "INSERT INTO student_additional_fields (student_id, field_name, field_value) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_dynamic_query);
            $stmt->bind_param('iss', $student_id, $field_name, $field_value);
        }
        $stmt->execute();
    }

    echo "Profile updated successfully!";
    // Reload the page to show the updated details
    header("Location: studentUpdateForm.php?id=" . $student_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 20px;
            background: linear-gradient(to right, skyblue, blue);

            color: #333;
        }

        h2 {
            text-align: center;
            color: rgb(247, 247, 247);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            justify-content: space-between;
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        .info,
        .additional-info {
            width: 48%;
            margin: 20px;
        }

        .additional-info {
            margin-top: 14%;
        }

        .info label,
        .additional-info label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            color: #555;
        }

        input[type="text"],
        input[type="email"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="file"]:focus,
        select:focus {
            border-color: #007BFF;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            background-color: #fff;
        }

        img {
            display: block;
            margin: 10px 0;
            width: 150px;
            height: 150px;
            border-radius: 8px;
            border: 1px solid #ddd;
            object-fit: cover;
        }

        .form-buttons {
            display: flex;
            justify-content: flex-start;
            /* Align buttons in a row starting from the left */
            gap: 10px;
            /* Adds space between the buttons */
            margin-top: 0;
            /* Removes extra top margin */
            margin-bottom: 20px;
            /* Adds some spacing below the buttons */
            position: relative;
            height: 45%;
            /* Ensures it stays at the top */
        }

        /* Individual button styles */
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(1px);
        }

        /* Style for "Delete" button */
        .form-buttons a button {
            background-color: #dc3545;
        }

        .form-buttons a button:hover {
            background-color: #a71d2a;
        }

        /* Add a small margin between "Update" and "Print" buttons if needed */
        button:nth-child(2) {
            margin-left: 10px;
        }

        /* Link styling */
        a {
            text-decoration: none;
            margin: 0;
            /* Removes unnecessary margin for row layout */
            display: inline-block;
            /* Keeps links aligned */
        }

        /* Responsive Design */


        @media print {
            body {
                background-color: white;
                margin: 0;
            }

            form {
                box-shadow: none;
            }

            .form-buttons,
            input[type="file"] {
                display: none;
            }

            h2::after {
                content: attr(data-print-title);
                display: block;
            }

            button,
            a {
                display: none !important;
            }
        }
    </style>

</head>

<body>
    <a href="admin.php">
        <button>Back</button>
    </a>
    <h2>Edit Student</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $student_id; ?>">
        <div class="info">
            <center>
                <label>Profile Image:</label>
                <?php if (!empty($student['profile_image'])): ?>
                    <img src="<?= '../' . htmlspecialchars($student['profile_image']); ?>" alt="Profile Image">
                <?php endif; ?>
                <input type="file" name="profile_image" accept="image/*">
            </center>

            <label>First Name:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']); ?>">

            <label>Middle Name:</label>
            <input type="text" name="middle_name" value="<?= htmlspecialchars($student['middle_name']); ?>">

            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']); ?>">

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($student['email']); ?>">

            <label>Status:</label>
            <select name="status">
                <option value="Regular" <?= $student['status'] === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                <option value="Irregular" <?= $student['status'] === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
            </select>

            <label>Year Level:</label>
            <input type="text" name="year_level" value="<?= htmlspecialchars($student['year_level']); ?>">

            <label>Section:</label>
            <input type="text" name="section" value="<?= htmlspecialchars($student['section']); ?>">
        </div>

        <div class="additional-info">
            <h3>Additional Fields</h3>
            <?php foreach ($formFields as $field): ?>
                <label for="<?= htmlspecialchars($field['field_name']); ?>"><?= htmlspecialchars($field['field_name']); ?>:</label>
                <input type="text" name="<?= htmlspecialchars($field['field_name']); ?>" value="<?= htmlspecialchars($additionalFields[$field['field_name']]); ?>">
            <?php endforeach; ?>
        </div>

        <div class="form-buttons">
            <button style="margin-bottom: 10px;" type="submit"><i class="bi bi-pen-fill"></i>Update</button>
            <a href="deleteStudent.php?id=<?= $student_id; ?>" onclick="return confirm('Are you sure you want to delete this student?')" style="text-decoration: none; color: white;">
                <button type="button" style="background-color: red; color: white; border: none; padding: 10px 20px; cursor: pointer;">
                    <i class="bi bi-trash-fill"></i>Delete
                </button>
            </a>
            <button style="background-color:green;" type="button" onclick="printProfile()"><i class="bi bi-printer-fill"></i>Print</button>
        </div>
    </form>
</body>
<script>
    function printProfile() {
        const header = document.querySelector('h2');
        const originalText = header.innerText;
        const studentName = "<?= htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']); ?>";

        // Update the header dynamically before printing
        header.innerText = studentName + " Information";
        window.print();

        // Restore original header text after printing
        header.innerText = originalText;
    }
</script>



</html>