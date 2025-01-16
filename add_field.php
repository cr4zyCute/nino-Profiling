<?php
include './database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $field_name = str_replace(' ', '_', $_POST['field_name']);
    $stmt = $conn->prepare("INSERT INTO form_fields (field_name) VALUES (?)");
    $stmt->bind_param("s", $field_name);

    if ($stmt->execute()) {
        header("Location: form.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
