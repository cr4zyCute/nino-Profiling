<?php
session_start();
include './database/db.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    die('You are not logged in.');
}

// Retrieve the student ID from the session if not passed via GET
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
} else {
    $id = $_SESSION['student_id'];
}

// Fetch the student data from the database
$query = "SELECT first_name, middle_name, last_name, profile_image, email, status, year_level, section FROM student WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($first_name, $middle_name, $last_name, $profile_image, $email, $status, $year_level, $section);
if (!$stmt->fetch()) {
    die('Student not found.');
}
$stmt->close();

// Fetch additional dynamic fields (form fields added by admin)
$formFields = [];
$stmt = $conn->prepare("SELECT id, field_name FROM form_fields");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $formFields[] = $row;
}
$stmt->close();

// Fetch values for these dynamic fields for the logged-in student
$additionalFields = [];
foreach ($formFields as $field) {
    $stmt = $conn->prepare("SELECT field_value FROM student_additional_fields WHERE student_id = ? AND field_name = ?");
    $stmt->bind_param("is", $id, $field['field_name']);
    $stmt->execute();
    $stmt->bind_result($field_value);
    if ($stmt->fetch()) {
        $additionalFields[$field['field_name']] = $field_value;
    } else {
        $additionalFields[$field['field_name']] = ''; // If no value exists, set as empty
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">

    <title>profile with data and skills - Bootdey.com</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/profile.css">

</head>

<body>
    <header>
        <div class="header-container">
            <div class="header-wrapper">
                <div class="logoBox">
                    <img src="./pictures/bsitlogo.png" alt="logo">
                </div>
                <div class="searchBox">
                </div>
                <div class="iconBox1">
                </div>
                <div class="iconBox2">
                    <a href="samplehome.php">
                        <i class="fa-solid fa-house"></i>
                    </a>

                    <div class="profile-dropdown">
                        <label>
                            <img src="<?= htmlspecialchars(string: './' . $profile_image); ?>" alt="Profile Image" class="profile-img">
                        </label>
                        <i class="fa-solid fa-caret-down dropdown-toggle"></i>
                        <div class="dropdown-menu">
                            <a href="student.php"><i class="bi bi-person-square"></i>View Profile</a>
                            <a href="./includes/logout.php"><i class="bi bi-box-arrow-left"></i>Logout</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </header>

    <div class="container">
        <div class="main-body">



            <div class="row gutters-sm">
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center text-center">
                                <?php if ($profile_image): ?>
                                    <img src="<?= htmlspecialchars('./' . $profile_image); ?>" alt="Profile Image" style="width:150px;height:150px;">
                                <?php else: ?>
                                    <p>No profile image available.</p>
                                <?php endif; ?>
                                <div class="mt-3">
                                    <h4><?php echo htmlspecialchars($first_name . " " . $middle_name . " " . $last_name); ?></h4>
                                    <p class="text-secondary mb-1"><i class="bi bi-mortarboard-fill"></i>Student</p>
                                    <p class="text-muted font-size-sm">ID: <?= htmlspecialchars($id); ?></p>

                                    <button class="btn btn-outline-primary" onclick="openPopup()">Message</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-3" style="border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); padding: 20px; background-color: #f9f9f9;">
                        <center>
                            <h4 style="font-family: 'Arial', sans-serif; font-size: 24px; color: #333; font-weight: 600;">Additional Information</h4>
                        </center>
                        <ul class="list-group list-group-flush" style="padding-left: 20px;">
                            <?php foreach ($formFields as $field): ?>
                                <li style="margin-bottom: 15px;">
                                    <label style="color: #007bff; font-weight: bold; font-size: 16px;" for="<?php echo htmlspecialchars($field['field_name']); ?>">
                                        <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
                                    </label>
                                    <span style="display: block; color: #555; font-size: 14px; margin-left: 20px; padding: 5px 10px; background-color: #e7f5ff; border-radius: 5px;" id="<?php echo htmlspecialchars($field['field_name']); ?>">
                                        <?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?>
                                    </span>
                                    <hr style="border: 1px solid #ddd; margin-top: 10px;">
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>

                </div>
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Full Name</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?php echo htmlspecialchars(string: $first_name . " " . $middle_name . " " . $last_name); ?>

                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Email</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?php echo htmlspecialchars(string: $email); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Status</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?php echo htmlspecialchars(!empty($status) ? $status : 'N/A'); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Section</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?php echo htmlspecialchars(!empty($section) ? $section : 'N/A'); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-3">
                                    <h6 class="mb-0">Year Level</h6>
                                </div>
                                <div class="col-sm-9 text-secondary">
                                    <?php echo htmlspecialchars(!empty($year_level) ? $year_level : 'N/A'); ?>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <a href="./studentProfileUpdate.php">Update Profile</a>
                                </div>
                            </div>
                        </div>
                    </div>



                    <div class="row gutters-sm">
                        <div class="yourpost">
                            <?php
                            include './database/db.php';

                            if (!isset($_SESSION['student_id'])) {
                                header("Location: login.php");
                                exit();
                            }

                            $student_id = $_SESSION['student_id'];

                            // Fetch posts
                            $sql = "SELECT posts.post_id, posts.content AS post_content, posts.media_path, posts.created_at AS post_date, 
                            student.first_name, student.last_name,
                            (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count
                            FROM posts
                            JOIN student ON posts.student_id = student.id
                            WHERE posts.student_id = ?
                            ORDER BY posts.created_at DESC";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("i", $student_id);
                            $stmt->execute();
                            $posts_result = $stmt->get_result();

                            // Handle new post creation
                            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_post'])) {
                                $content = isset($_POST['content']) && trim($_POST['content']) !== '' ? trim($_POST['content']) : null;
                                $media_path = null;

                                // Handle file upload
                                if (isset($_FILES['media']) && $_FILES['media']['error'] == UPLOAD_ERR_OK) {
                                    $upload_dir = "uploads/";
                                    $media_name = basename($_FILES['media']['name']);
                                    $media_path = $upload_dir . uniqid() . "_" . $media_name;

                                    if (!move_uploaded_file($_FILES['media']['tmp_name'], $media_path)) {
                                        echo "Failed to upload media.";
                                        exit();
                                    }
                                }

                                // Ensure content or media is provided
                                if (empty($content) && empty($media_path)) {
                                    echo "You must provide text or media to create a post.";
                                    exit();
                                }

                                // Insert post into database
                                $stmt = $conn->prepare("INSERT INTO posts (student_id, content, media_path) VALUES (?, ?, ?)");
                                $stmt->bind_param("iss", $student_id, $content, $media_path);
                                $stmt->execute();
                                $stmt->close();

                                header("Location: studenthomepage.php");
                                exit();
                            }
                            ?>

                            <style>
                                .post-container {
                                    background-color: #fff;
                                    border: 1px solid #ddd;
                                    border-radius: 8px;
                                    margin-bottom: 20px;
                                    padding: 15px;
                                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                                }

                                .post-header {
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 15px;
                                }

                                .post-header img {
                                    margin-right: 10px;
                                    border: 2px solid #ddd;
                                }

                                .post-header strong {
                                    font-size: 16px;
                                    color: #333;
                                }

                                .post-header small {
                                    margin-left: 10px;
                                    font-size: 12px;
                                    color: #999;
                                }

                                .post-content {
                                    margin-bottom: 10px;
                                }

                                .post-content p {
                                    font-size: 14px;
                                    color: #555;
                                    line-height: 1.5;
                                }

                                .post-media img {
                                    max-width: 100%;
                                    border-radius: 5px;
                                    margin-top: 10px;
                                }

                                .post-container a {
                                    text-decoration: none;
                                    color: #007bff;
                                    font-size: 14px;
                                    margin-right: 15px;
                                }

                                .post-container a:hover {
                                    text-decoration: underline;
                                }

                                .post-container button {
                                    background-color: #f44336;
                                    color: #fff;
                                    border: none;
                                    padding: 5px 10px;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    font-size: 14px;
                                }

                                .post-container button:hover {
                                    background-color: #d32f2f;
                                }

                                .comments-section {
                                    margin-top: 15px;
                                    border-top: 1px solid #ddd;
                                    padding-top: 10px;
                                }

                                .comments-section form textarea {
                                    width: 100%;
                                    height: 60px;
                                    padding: 8px;
                                    border: 1px solid #ddd;
                                    border-radius: 5px;
                                    resize: none;
                                    font-family: Arial, sans-serif;
                                    font-size: 14px;
                                    margin-bottom: 10px;
                                }

                                .comments-section form button {
                                    background-color: #4CAF50;
                                    color: #fff;
                                    border: none;
                                    padding: 5px 10px;
                                    border-radius: 5px;
                                    cursor: pointer;
                                    font-size: 14px;
                                }

                                .comments-section form button:hover {
                                    background-color: #45a049;
                                }
                            </style>
                            <script>
                                function toggleComments(postId) {
                                    const commentsSection = document.getElementById(`comments-${postId}`);
                                    const button = document.getElementById(`toggle-btn-${postId}`);
                                    if (commentsSection.style.display === "none") {
                                        commentsSection.style.display = "block";
                                        button.textContent = "Hide Comments";
                                    } else {
                                        commentsSection.style.display = "none";
                                        button.textContent = "Show Comments";
                                    }
                                }
                            </script>


                            <div class="container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
                                <h1 style="font-family: 'Arial', sans-serif; font-size: 28px; font-weight: 700; color: #333;">My Posts</h1>

                                <hr style="border: 1px solid #ddd;">

                                <?php while ($post = $posts_result->fetch_assoc()): ?>
                                    <div class="post-container" style="background-color: #fff; border-radius: 10px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 20px; margin-bottom: 20px;">
                                        <form method="post" action="delete_post.php" style="text-align: right;">
                                            <input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>">
                                            <button type="submit" name="delete_post" style="background-color: #f44336; color: white; border: none; padding: 6px 12px; border-radius: 5px; cursor: pointer;" onclick="return confirm('Are you sure you want to delete this post?');">Delete Post</button>
                                        </form>

                                        <div class="post-header" style="display: flex; align-items: center; margin-bottom: 15px;">
                                            <?php if ($profile_image): ?>
                                                <img src="<?= htmlspecialchars('./' . $profile_image); ?>" alt="Profile Image" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 10px;">
                                            <?php else: ?>
                                                <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #ddd; margin-right: 10px;"></div>
                                            <?php endif; ?>

                                            <div style="flex-grow: 1;">
                                                <strong style="font-size: 16px; color: #333;"><?php echo htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></strong>
                                                <small style="font-size: 12px; color: #888; display: block;"><?php echo htmlspecialchars($post['post_date']); ?></small>
                                            </div>
                                        </div>

                                        <div class="post-content" style="margin-bottom: 15px;">
                                            <?php if (!empty($post['post_content'])): ?>
                                                <p style="font-size: 14px; color: #333;"><?php echo htmlspecialchars($post['post_content']); ?></p>
                                            <?php endif; ?>

                                            <?php if (!empty($post['media_path'])): ?>
                                                <div class="post-media" style="max-width: 100%; margin-top: 15px;">
                                                    <img src="<?php echo htmlspecialchars($post['media_path']); ?>" alt="Media" style="width: 100%; border-radius: 8px;">
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; color: #555;">
                                            <a href="like.php?post_id=<?php echo $post['post_id']; ?>" style="color: #007bff; text-decoration: none; cursor: pointer;">Like</a>
                                            <span>(<?php echo $post['like_count']; ?>)</span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>

                        </div>

                    </div>
                </div>
                <div class="col-sm-6 mb-3">


                </div>
            </div>

            <div class="popup-overlay" id="popupOverlay">
                <div class="popup-content">
                    <button class="close-popup" onclick="closePopup()">&times;</button>
                    <h3>Form</h3>
                    <form method="POST" action="update_student.php" class="fillup-form">
                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Regular" <?php echo $status === 'Regular' ? 'selected' : ''; ?>>Regular</option>
                                <option value="Irregular" <?php echo $status === 'Irregular' ? 'selected' : ''; ?>>Irregular</option>
                            </select>
                        </div>

                        <!-- Year Level -->
                        <div class="form-group">
                            <label for="year_level">Year Level</label>
                            <input type="text" id="year_level" name="year_level"
                                value="<?php echo htmlspecialchars($year_level); ?>" placeholder="e.g., first year">
                        </div>
                        <!-- Section -->
                        <div class="form-group">
                            <label for="section">Section</label>
                            <input type="text" id="section" name="section"
                                value="<?php echo htmlspecialchars($section); ?>" placeholder="e.g., Section A">
                        </div>

                        <!-- Dynamic Fields (e.g., father's name, mother's name, etc.) -->
                        <?php foreach ($formFields as $field): ?>
                            <div class="form-group">
                                <label for="<?php echo htmlspecialchars($field['field_name']); ?>">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $field['field_name'])); ?>
                                </label>
                                <input type="text" id="<?php echo htmlspecialchars($field['field_name']); ?>"
                                    name="<?php echo htmlspecialchars($field['field_name']); ?>"
                                    value="<?php echo htmlspecialchars($additionalFields[$field['field_name']]); ?>">
                            </div>
                        <?php endforeach; ?>

                        <!-- Submit Button -->
                        <div class="form-group">
                            <button type="submit">Update</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    </div>
    </div>
    <script>
        function openPopup() {
            document.getElementById('popupOverlay').style.display = 'flex';
        }

        function closePopup() {
            document.getElementById('popupOverlay').style.display = 'none';
        }
        document.querySelector('form').addEventListener('submit', function(event) {
            const yearLevel = document.getElementById('year_level').value.trim().toLowerCase();

            // Valid year levels
            const validYearLevels = ['first year', 'second year', 'third year', 'fourth year'];

            if (!validYearLevels.includes(yearLevel)) {
                alert('Please enter a valid year level (e.g., first year, second year, third year, or fourth year).');
                event.preventDefault(); // Prevent form submission
            }
        });
    </script>
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">

    </script>
</body>

</html>