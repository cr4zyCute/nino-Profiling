<?php
session_start();
include './database/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['content'])) {
    $post_id = $_POST['post_id'];
    $student_id = $_SESSION['student_id'];
    $content = $_POST['content'];

    $stmt = $conn->prepare("INSERT INTO comments (post_id, student_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $student_id, $content);
    $stmt->execute();
    $stmt->close();

    header("Location: studenthomepage.php?post_id=$post_id");
    exit();
}

$post_id = $_GET['post_id'];
$sql = "SELECT comments.content, comments.created_at, student.first_name, student.last_name
        FROM comments
        JOIN student ON comments.student_id = student.id
        WHERE comments.post_id = $post_id
        ORDER BY comments.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>
</head>

<body>
    <h1>Comments</h1>
    <form method="post">
        <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
        <textarea name="content" placeholder="Write a comment..." required></textarea><br>
        <button type="submit">Post Comment</button>
    </form>
    <hr>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div>
            <h3><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></h3>
            <p><?php echo htmlspecialchars($row['content']); ?></p>
            <small>Commented on: <?php echo $row['created_at']; ?></small>
        </div>
        <hr>
    <?php endwhile; ?>
</body>

</html>