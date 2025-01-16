<?php
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $profile_picture = $_FILES['profile_picture'];

    // Validation
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        die('All fields are required.');
    }

    if ($password !== $confirm_password) {
        die('Passwords do not match.');
    }

    if ($profile_picture['error'] !== UPLOAD_ERR_OK) {
        die('Error uploading profile picture.');
    }

    // Validate and upload the profile picture
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($profile_picture['type'], $allowed_types)) {
        die('Only JPEG, PNG, and GIF images are allowed.');
    }

    $upload_dir = './uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = uniqid() . '_' . basename($profile_picture['name']);
    $target_path = $upload_dir . $file_name;

    if (!move_uploaded_file($profile_picture['tmp_name'], $target_path)) {
        die('Failed to save profile picture.');
    }

    // Store plain text password (not recommended in production)
    $stmt = $conn->prepare("INSERT INTO admins (username, email, password, profile_picture) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $password, $file_name);

    if ($stmt->execute()) {
        echo "Registration successful. <a href='login_admin.php'>Login here</a>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Registration</title>
</head>

<body>
    <form method="POST" enctype="multipart/form-data">
        <h2>Register Admin</h2>
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
        <label for="profile_picture">Profile Picture:</label>
        <input type="file" name="profile_picture" id="profile_picture" accept="image/*" required>
        <button type="submit">Register</button>
    </form>
</body>

</html>