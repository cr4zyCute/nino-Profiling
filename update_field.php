<?php
include './database/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $field_name = str_replace(' ', '_', $_POST['field_name']);

    $stmt = $conn->prepare("UPDATE form_fields SET field_name = ? WHERE id = ?");
    $stmt->bind_param("si", $field_name, $id);

    if ($stmt->execute()) {
        header("Location: form.php");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
