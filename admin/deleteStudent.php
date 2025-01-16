<?php
include '../database/db.php';

// Check if the `id` parameter is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid student ID.');
}

// Sanitize the student ID
$id = (int)$_GET['id'];

// Prepare the DELETE query for the student
$stmt = $conn->prepare("DELETE FROM student WHERE id = ?");
$stmt->bind_param("i", $id);

// Execute the query and handle the result
if ($stmt->execute()) {
    header("Location: admin.php"); // Redirect to admin page after successful deletion
    exit;
} else {
    echo "Error deleting student: " . $stmt->error;
}

// Close the statement
$stmt->close();
    