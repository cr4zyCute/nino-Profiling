<?php
function renderStudentFormFields($status = '', $year_level = '', $section = '', $additionalFields = [])
{
?>
    <label for="status">Status</label>
    <select id="status" name="status" required>
        <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
        <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
    </select>

    <label for="year_level">Year Level</label>
    <input type="text" id="year_level" name="year_level" value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., 1st Year">

    <label for="section">Section</label>
    <input type="text" id="section" name="section" value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">

    <h3>Additional Information</h3>
    <div id="additionalFields">
        <?php
        if (!empty($additionalFields)) {
            foreach ($additionalFields as $index => $field) {
                // Replace underscores with spaces for user-friendly display
                $fieldDisplayName = str_replace('_', ' ', $field['name']);
                // Use underscores for the input `name` attribute
                $fieldName = str_replace(' ', '_', $field['name']);
        ?>
                <div>
                    <label for="customField<?php echo $index + 1; ?>">
                        <?php echo htmlspecialchars($fieldDisplayName); ?>
                    </label>
                    <input type="text" name="<?php echo htmlspecialchars($fieldName); ?>"
                        value="<?php echo htmlspecialchars($field['value']); ?>"
                        placeholder="Enter <?php echo htmlspecialchars($fieldDisplayName); ?>">
                </div>
            <?php
            }
        } else {
            ?>
            <div>
                <label for="customField1Name">Field 1 Name</label>
                <input type="text" name="customField1Name" placeholder="Field 1 Name">
                <input type="text" name="customField1Value" placeholder="Field 1 Value">
            </div>
        <?php
        }
        ?>
    </div>
    <button type="button" onclick="addField()">Add More Information</button>
    <script>
        // Dynamically add new fields
        function addField() {
            const additionalFieldsDiv = document.getElementById('additionalFields');
            const fieldCount = additionalFieldsDiv.children.length + 1;
            const newFieldHTML = `
                <div>
                    <label for="customField${fieldCount}Name">Field ${fieldCount} Name</label>
                    <input type="text" name="customField${fieldCount}Name" placeholder="Field ${fieldCount} Name">
                    <input type="text" name="customField${fieldCount}Value" placeholder="Field ${fieldCount} Value">
                </div>`;
            additionalFieldsDiv.insertAdjacentHTML('beforeend', newFieldHTML);
        }
    </script>
<?php
}
?>