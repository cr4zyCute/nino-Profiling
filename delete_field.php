<?php
include './database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM form_fields WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: form.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
