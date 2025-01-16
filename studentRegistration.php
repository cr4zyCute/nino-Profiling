<?php
include './database/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = $_POST['password']; // No hashing here, use plain text

    $target_dir = "uploads/";
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

    // Insert the plain text password into the database
    $stmt = $conn->prepare("INSERT INTO student (first_name, middle_name, last_name, email, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $email, $password, $target_file);



    if ($stmt->execute()) {
        echo "
    <div id='popup' style='display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.7); text-align: center; padding-top: 100px; z-index: 9999;'>
        <div style='background: white; padding: 40px 60px; border-radius: 10px; max-width: 400px; margin: 0 auto; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);'>
            <h2 style='color: #333; font-family: Arial, sans-serif;'>Registration Successful</h2>
            <p style='color: #555; font-family: Arial, sans-serif; font-size: 16px;'>Your registration was successful. You can now log in.</p>
            <a href='index.php' style='display: inline-block; margin-top: 20px; padding: 12px 25px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-size: 16px; transition: background-color 0.3s;'>Login</a>
            <br>
        </div>
    </div>
    <script>
        function closePopup() {
            document.getElementById('popup').style.display = 'none';
            window.location.href = 'index.php';
        }
    </script>
    ";
    } else {
        echo "Error: " . $stmt->error;
    }




    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<style>
    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: #333;
    }

    /* Form Container */
    .form-container {
        background: rgba(255, 255, 255, 0.9);
        padding: 25px 35px;
        border-radius: 15px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        display: flex;
        align-items: center;
        gap: 20px;
        width: 90%;
        max-width: 900px;
    }

    /* Form */
    form {
        flex: 2;
        position: relative;
        overflow: hidden;
    }

    /* Form Header */
    form h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333;
        font-weight: 700;
        letter-spacing: 1px;
        text-align: center;
    }

    /* Field Container */
    .fields-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    /* Field Styles */
    .field {
        flex: 1 1 45%;
        display: flex;
        flex-direction: column;
    }

    .field label {
        font-weight: 500;
        color: #555;
        margin-bottom: 5px;
    }

    .field input {
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 14px;
        color: #333;
        background-color: #f9f9f9;
        transition: all 0.3s ease;
    }

    /* Input Focus State */
    .field input:focus {
        border-color: #6a11cb;
        outline: none;
        box-shadow: 0 0 8px rgba(106, 17, 203, 0.3);
    }

    /* Profile Image */
    .profile-image {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #ddd;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .edit-icon {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: white;
        border-radius: 50%;
        padding: 5px;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .profile-image p {
        margin-top: 10px;
        font-size: 14px;
        font-weight: 500;
    }

    /* Submit Button */
    form button {
        width: 100%;
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: white;
        padding: 12px;
        font-size: 16px;
        font-weight: 600;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 20px;
    }

    form button:hover {
        background: linear-gradient(135deg, #5c0bb5, #1e60cf);
        transform: scale(1.02);
        box-shadow: 0 4px 10px rgba(34, 74, 190, 0.3);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .form-container {
            flex-direction: column;
            align-items: stretch;
        }
    }

    /* Profile Image */
    .profile-image {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .profile-picture {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: #ddd;
        position: relative;
        overflow: hidden;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .profile-picture img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        display: none;
        /* Initially hidden until an image is selected */
    }

    .edit-icon {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: white;
        border-radius: 50%;
        padding: 5px;
        font-size: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .profile-image p {
        margin-top: 10px;
        font-size: 14px;
        font-weight: 500;
    }

    a button {
        position: absolute;
        top: 20px;
        /* Adjust the vertical spacing */
        left: 20px;
        /* Adjust the horizontal spacing */
        background: linear-gradient(135deg, #6a11cb, #2575fc);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
    }

    a button:hover {
        background: linear-gradient(135deg, #5c0bb5, #1e60cf);
        transform: scale(1.05);
        box-shadow: 0 4px 10px rgba(34, 74, 190, 0.3);
    }

    a {
        text-decoration: none;
    }
</style>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <a href="index.php">
        <button>back</button>
    </a>
    <div class="form-container">
        <form method="post" enctype="multipart/form-data">
            <h2>Register</h2>

            <div class="profile-image">
                <div class="profile-picture">
                    <img id="profile-preview" src="" alt="Profile Preview">
                    <span class="edit-icon">✎</span>
                </div>
                <div class="fields-container">
                    <div class="field">
                        <label for="profile-image">Profile Image:</label>
                        <input type="file" id="profile-image" name="profile_image" accept="image/*" onchange="previewImage(event)">
                    </div>
                </div>
            </div>
            <div class="field">
                <label for="first-name">First Name:</label>
                <input type="text" id="first-name" name="first_name" placeholder="Enter your first name" required>
            </div>
            <div class="field">
                <label for="middle-name">Middle Name:</label>
                <input type="text" id="middle-name" name="middle_name" placeholder="Enter your middle name">
            </div>
            <div class="field">
                <label for="last-name">Last Name:</label>
                <input type="text" id="last-name" name="last_name" placeholder="Enter your last name" required>
            </div>
            <div class="field">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="field">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button type="submit">Register</button>
        </form>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('profile-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>

</html>