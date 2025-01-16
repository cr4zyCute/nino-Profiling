<?php
session_start();
include './database/db.php'; // Ensure this path is correct and $conn is initialized in db.php

// Ensure the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the request is valid
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['post_id'])) {
    $post_id = intval($_POST['post_id']);
    $student_id = $_SESSION['student_id'];

    // Check if the post belongs to the logged-in student
    $stmt = $conn->prepare("SELECT media_path FROM posts WHERE post_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $post_id, $student_id);
    $stmt->execute();
    $stmt->bind_result($media_path);
    $stmt->fetch();
    $stmt->close();

    if ($media_path) {
        // Delete the media file if it exists
        if (file_exists($media_path)) {
            unlink($media_path);
        }
    }

    // Delete the post
    $stmt = $conn->prepare("DELETE FROM posts WHERE post_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $post_id, $student_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the student's homepage
    header("Location: student.php");
    exit();
} else {
    // Redirect back if the request is invalid
    header("Location: student.php");
    exit();
}
