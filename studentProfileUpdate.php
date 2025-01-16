<?php
session_start();
include './database/db.php';

// Ensure the student is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit();
}

// Retrieve student ID from session
$student_id = $_SESSION['student_id'];
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $status = trim($_POST['status']);
    $year_level = trim($_POST['year_level']);
    $section = trim($_POST['section']);
    $dynamic_fields = [];
    $profile_image = $_POST['current_profile_image']; // Default to current profile image

    // Handle image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/profile_images/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $image_file = basename($_FILES['profile_image']['name']);
        $target_file = $target_dir . uniqid() . "_" . $image_file;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($image_file_type, $allowed_types)) {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        } elseif (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Delete the old profile image if it exists
            if (!empty($profile_image) && file_exists($profile_image)) {
                unlink($profile_image);
            }
            $profile_image = $target_file;
        } else {
            $error_message = "Failed to upload the image.";
        }
    }

    if (empty($error_message)) {
        // Update main student details
        $update_query = "UPDATE student SET first_name = ?, middle_name = ?, last_name = ?, profile_image = ?, status = ?, year_level = ?, section = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sssssssi', $first_name, $middle_name, $last_name, $profile_image, $status, $year_level, $section, $student_id);

        if ($stmt->execute()) {
            // Update dynamic fields
            foreach ($_POST as $key => $value) {
                if (!in_array($key, ['first_name', 'middle_name', 'last_name', 'status', 'year_level', 'section', 'profile_image', 'current_profile_image'])) {
                    $dynamic_fields[$key] = trim($value);
                }
            }

            foreach ($dynamic_fields as $field_name => $field_value) {
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
                    // Insert new dynamic field
                    $insert_dynamic_query = "INSERT INTO student_additional_fields (student_id, field_name, field_value) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($insert_dynamic_query);
                    $stmt->bind_param('iss', $student_id, $field_name, $field_value);
                }
                $stmt->execute();
            }
            $success_message = "Profile updated successfully.";
        } else {
            $error_message = "Failed to update profile.";
        }
        $stmt->close();
    }
}

// Fetch current student details
$query = "SELECT first_name, middle_name, last_name, profile_image, email, password, status, year_level, section FROM student WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $profile_image, $email, $password, $status, $year_level, $section);
$stmt->fetch();
$stmt->close();

// Fetch additional dynamic fields
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $error_message = "";

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    }

    // Hash password if provided
    if (!empty($password)) {
        $hashed_password = $password;
    }

    if (empty($error_message)) {
        // Update email and password in the database
        $update_query = "UPDATE student SET email = ?" .
            (!empty($password) ? ", password = ?" : "") .
            " WHERE id = ?";
        $stmt = $conn->prepare($update_query);

        if (!empty($password)) {
            $stmt->bind_param('ssi', $email, $hashed_password, $student_id);
        } else {
            $stmt->bind_param('si', $email, $student_id);
        }

        if ($stmt->execute()) {
            $success_message = "Email and password updated successfully.";
        } else {
            $error_message = "Failed to update email and password.";
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
    <link rel="stylesheet" href="./css/studentUpdate.css">
</head>

<body>
    <a href="student.php" id="back-button">
        <button id="back">Back</button>
    </a>

    <div class="profile-update-container">
        <h2>Update Profile</h2>
        <?php if (!empty($success_message)): ?>
            <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form method="POST" action="studentProfileUpdate.php" enctype="multipart/form-data">

            <div class="form-group-profile">
                <?php if (!empty($profile_image)): ?>
                    <center>

                        <img src="<?php echo htmlspecialchars($profile_image); ?>" alt="Profile Image" class="profile-img-preview">
                    </center>
                <?php endif; ?>
                <input type="file" id="profile_image" name="profile_image">

                <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($profile_image); ?>">
            </div>
            <br>
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
            </div>
            <div class="form-group">
                <label for="middle_name">Middle Name</label>
                <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($middle_name); ?>">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
            </div>


            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div>
                    <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                    <button type="button" id="togglePassword">Hide</button>
                </div>
            </div>



            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                    <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
                </select>
            </div>
            <div class="form-group">
                <label for="year_level">Year Level</label>
                <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" required>
            </div>
            <div class="form-group">
                <label for="section">Section</label>
                <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" required>
            </div>
            <?php foreach ($formFields as $field): ?>
                <div class="form-group">
                    <label for="<?php echo htmlspecialchars($field['field_name']); ?>">
                        <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
                    </label>
                    <input type="text" id="<?php echo htmlspecialchars($field['field_name']); ?>" name="<?php echo htmlspecialchars($field['field_name']); ?>" value="<?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?>">
                </div>
            <?php endforeach; ?>
            <button type="submit">Update</button>
        </form>
    </div>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const toggleButton = this;

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleButton.textContent = 'Hide';
            } else {
                passwordField.type = 'password';
                toggleButton.textContent = 'Show';
            }
        });
    </script>

</body>

</html>