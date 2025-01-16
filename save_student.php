<?php
include './database/db.php';

// Handle the additional fields dynamically
foreach ($_POST as $key => $value) {
    if (str_contains($key, 'customField') && str_ends_with($key, 'Name')) {
        $fieldIndex = preg_replace('/[^0-9]/', '', $key); // Extract field index
        $fieldName = $value;
        $fieldValue = $_POST['customField' . $fieldIndex . 'Value'] ?? '';

        // Save to database
        $sql = "INSERT INTO form_fields (field_name, field_value) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $fieldName, $fieldValue);
        $stmt->execute();
    }
}

header("Location: form.php");
exit;
