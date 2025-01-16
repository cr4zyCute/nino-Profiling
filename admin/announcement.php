<?php
include '../database/db.php';

// Handle form submission for adding announcements
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_announcement'])) {
    $announcement_text = trim($_POST['announcement_text']);

    if (!empty($announcement_text)) {
        $stmt = $conn->prepare("INSERT INTO announcements (content) VALUES (?)");
        $stmt->bind_param("s", $announcement_text);

        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Ensure no further code is executed
        } else {
            echo "<p style='color: red;'>Error posting announcement: " . $stmt->error . "</p>";
        }

        $stmt->close();
    } else {
        echo "<p style='color: red;'>Announcement text is required.</p>";
    }
}

// Handle announcement deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_announcement'])) {
    $announcement_id = intval($_POST['announcement_id']);

    if ($announcement_id > 0) {
        $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
        $stmt->bind_param("i", $announcement_id);

        if ($stmt->execute()) {
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit(); // Ensure no further code is executed
        } else {
            echo "<p style='color: red;'>Error deleting announcement: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Announcements</title>
    <link rel="stylesheet" href="../css/announcement.css">
</head>

<body>

    <a href="admin.php">
        <button class="back">Back</button>
    </a>
    <div class="create-announcement">
        <center>
            <h1>Make an Announcement</h1>
        </center>

        <form method="post">
            <label for="announcement_text">Announcement Text:</label>
            <textarea name="announcement_text" id="announcement_text" rows="4" required></textarea><br>
            <button type="submit" name="submit_announcement">Post Announcement</button>
        </form>
    </div>
    <div class="Announcements">
        <center>
            <h2>All Announcements</h2>
        </center>
        <?php
        // Fetch all announcements
        $sql = "SELECT id, content, created_at FROM announcements ORDER BY created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($announcement = $result->fetch_assoc()) {
                echo "<div class='announcement'>";
                echo "<p>" . htmlspecialchars($announcement['content']) . "</p>";
                echo "<small>Posted on: " . htmlspecialchars($announcement['created_at']) . "</small>";

                // Delete form
                echo "<form method='post' class='delete-form'>";
                echo "<input type='hidden' name='announcement_id' value='" . htmlspecialchars($announcement['id']) . "'>";
                echo "<button type='submit' name='delete_announcement'>Delete</button>";
                echo "</form>";

                echo "</div>";
            }
        } else {
            echo "<p>No announcements available.</p>";
        }
        ?>
    </div>
</body>

</html>