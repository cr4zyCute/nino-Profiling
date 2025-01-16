<?php
include './database/db.php';

// Function to render the student form fields
function renderStudentFormFields($status = '', $year_level = '', $section = '')
{
?>
    <div class="form-row">
        <label for="status">Status</label>
        <select id="status" name="status" required>
            <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
            <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
        </select>
    </div>
    <div class="form-row">
        <label for="year_level">Year Level</label>
        <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., 1st Year">
    </div>
    <div class="form-row">
        <label for="section">Section</label>
        <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">
    </div>
<?php
}

// Function to render and manage form fields
function renderFormFields($conn)
{
    // Fetch fields from the database
    $sql = "SELECT id, field_name FROM form_fields";
    $result = $conn->query($sql);
    $fields = $result->fetch_all(MYSQLI_ASSOC);
?>
    <div class="field-management">
        <h3>Manage Form Fields</h3>
        <h3>Add New Field</h3>
        <form method="POST" action="add_field.php" class="add-field-form">
            <input type="text" name="field_name" placeholder="Field Name" required>
            <button style="background-color: #007bff;" type="submit">Add Field</button>
        </form>
        <div id="fieldList">
            <?php foreach ($fields as $field) { ?>
                <div class="field-item" id="field-<?php echo $field['id']; ?>">
                    <form method="POST" action="update_field.php">
                        <input type="hidden" name="id" value="<?php echo $field['id']; ?>">
                        <input type="text" name="field_name" value="<?php echo htmlspecialchars($field['field_name']); ?>">
                        <button class="update" type="submit">Update</button>
                        <button class="delete" type="button" onclick="deleteField(<?php echo $field['id']; ?>)">Delete</button>
                    </form>
                </div>
            <?php } ?>
        </div>


    </div>

    <script>
        function deleteField(fieldId) {
            if (confirm('Are you sure you want to delete this field?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_field.php';
                form.innerHTML = `<input type="hidden" name="id" value="${fieldId}">`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
<?php
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information Form</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
    /* General Layout */
    body {
        font-family: 'Roboto', Arial, sans-serif;
        background-color: #f9f9f9;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        color: #333;
    }

    .container {
        width: 90%;
        max-width: 1200px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    /* Header Section */
    .form-header {
        background: linear-gradient(135deg, #0056b3, #003c7e);
        color: white;
        padding: 20px;
        font-size: 24px;
        font-weight: bold;
        text-align: center;
        text-transform: uppercase;
    }

    /* Back Button */
    .back {
        position: absolute;
        top: 20px;
        left: 20px;
        background: linear-gradient(135deg, #2196f3, #1e88e5);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        text-transform: uppercase;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease;
    }

    .back:hover {
        background: linear-gradient(135deg, #1976d2, #1565c0);
        transform: scale(1.05);
    }

    /* Form Fields */
    .student-form {
        padding: 20px;
    }

    .student-form .form-row {
        margin-bottom: 15px;
    }

    .student-form .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: #555;
    }

    .student-form .form-row input,
    .student-form .form-row select {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #fefefe;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: border-color 0.3s ease;
    }

    .student-form .form-row input:focus,
    .student-form .form-row select:focus {
        border-color: rgb(47, 70, 174);
        outline: none;
    }

    .student-form .form-submit button {
        display: inline-block;
        width: 100%;
        padding: 10px 20px;
        background: linear-gradient(135deg, #0056b3, #003c7e);
        color: white;
        font-size: 16px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-transform: uppercase;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: background 0.3s ease;
    }

    .student-form .form-submit button:hover {
        background: linear-gradient(135deg, #0056b3, #003c7e);
    }

    /* Fields Management */
    .field-management {
        padding: 20px;
        background: #f7f7f7;
    }

    .field-management h3 {
        margin-bottom: 20px;
        font-size: 20px;
        color: #333;
        text-transform: uppercase;
        text-align: center;
    }

    .add-field-form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        justify-content: center;
    }

    .add-field-form input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .add-field-form button {
        padding: 10px 20px;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        font-size: 14px;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .add-field-form button:hover {
        background: linear-gradient(135deg, #0056b3, #003c7e);
    }

    #fieldList {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .field-item {
        padding: 15px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .field-item button {
        margin-top: 10px;
        width: 100px;
        padding: 5px;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        color: white;
        cursor: pointer;
    }

    .field-item .update {
        background: linear-gradient(135deg, rgb(61, 76, 185), rgb(59, 89, 138));
    }

    .field-item .delete {
        background: linear-gradient(135deg, #f44336, #d32f2f);
    }

    .field-item button:hover {
        opacity: 0.9;
    }
</style>

<body>

    <a href="././admin/admin.php">
        <button class="back">Back</button>
    </a>
    <div class="container">
        <div class="form-header">
            Form

        </div>
        <div class="form-content">
            <form method="POST" action="save_student.php" class="student-form">
                <h3>Fill Up</h3>
                <?php renderStudentFormFields(); ?>
                <div class="form-submit">
                    <button type="submit">Submit</button>
                </div>
            </form>
            <div class="divider"></div>
            <?php renderFormFields($conn); ?>
        </div>
    </div>
</body>

</html>