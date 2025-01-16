<?php
session_start();
include './database/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];
    $student_id = $_SESSION['student_id'];

    $stmt = $conn->prepare("INSERT INTO likes (post_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $student_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: studenthomepage.php");
exit();
