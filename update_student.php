<?php
session_start();
include './database/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $status = $_POST['status'];
    $year_level = $_POST['year_level'];
    $section = $_POST['section'];

    // Update student table
    $stmt = $conn->prepare("UPDATE student SET status = ?, year_level = ?, section = ? WHERE id = ?");
    $stmt->bind_param("sssi", $status, $year_level, $section, $_SESSION['student_id']);
    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    $stmt->close();


foreach ($_POST as $key => $value) {
    if (!in_array($key, ['status', 'year_level', 'section'])) {
        // Check if the field already exists in the additional fields table
        $stmt = $conn->prepare("SELECT id FROM student_additional_fields WHERE student_id = ? AND field_name = ?");
        $stmt->bind_param("is", $_SESSION['student_id'], $key);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update existing field value
            $stmt = $conn->prepare("UPDATE student_additional_fields SET field_value = ? WHERE student_id = ? AND field_name = ?");
            $stmt->bind_param("sis", $value, $_SESSION['student_id'], $key);
        } else {
            // Insert new field value
            $stmt = $conn->prepare("INSERT INTO student_additional_fields (student_id, field_name, field_value) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $_SESSION['student_id'], $key, $value);
        }
        if (!$stmt->execute()) {
            echo "Error updating additional field: " . $stmt->error;
        }
        $stmt->close();
    }
}

    $conn->close();
    header("Location: student.php");
    exit();
}
