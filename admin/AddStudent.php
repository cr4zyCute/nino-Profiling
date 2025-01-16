<?php

include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {


    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $password = $_POST['password'];

    $target_dir = "../uploads/"; // Upload files outside the admin folder
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $profile_image = $_FILES['profile_image']['name'];
    $target_file = $target_dir . basename($profile_image);
    $upload_ok = move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file);

    if (!$upload_ok) {
        echo "File upload failed.";
        exit();
    }

    // Save a web-server-relative path in the database
    $db_image_path = "uploads/" . basename($profile_image);

    $stmt = $conn->prepare("INSERT INTO student (first_name, middle_name, last_name, email, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $email, $password, $db_image_path);

    if ($stmt->execute()) {
        echo "Registration successful. <a href='index.php'>Login</a>";
    } else {
        echo "Error: " . $stmt->error;
    }


    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="post" enctype="multipart/form-data">
        <h2>Add a student</h2>
        <label>First Name:</label><br>
        <input type="text" name="first_name" required><br>
        <label>Middle Name:</label><br>
        <input type="text" name="middle_name"><br>
        <label>Last Name:</label><br>
        <input type="text" name="last_name" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <label>Profile Image:</label><br>
        <input type="file" name="profile_image" accept="image/*" required><br>
        <button type="submit">Register</button>
    </form>
</body>

</html>