<?php
session_start();
include './database/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}
$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT profile_image FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($profile_image);
$stmt->fetch();
$stmt->close();
$stmt = $conn->prepare("SELECT first_name, last_name, profile_image FROM student WHERE id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $profile_image);
$stmt->fetch();
$stmt->close();

// Fetch posts, including profile_image
$sql = "SELECT posts.post_id, posts.content AS post_content, posts.media_path, posts.created_at AS post_date, 
        student.first_name, student.last_name, student.profile_image,
        (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.post_id) AS like_count
        FROM posts
        JOIN student ON posts.student_id = student.id
        ORDER BY posts.created_at DESC";
$posts_result = $conn->query($sql);

// Handle new post creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_post'])) {
    $content = isset($_POST['content']) && trim($_POST['content']) !== '' ? trim($_POST['content']) : null;
    $student_id = $_SESSION['student_id'];
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

    // Ensure at least one of content or media is provided
    if (empty($content) && empty($media_path)) {
        echo "You must provide text or media to create a post.";
        exit();
    }

    // Insert into the database
    $stmt = $conn->prepare("INSERT INTO posts (student_id, content, media_path) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $content, $media_path);
    $stmt->execute();
    $stmt->close();
    header("Location: studenthomepage.php");
    exit();
}

// Handle new comment or reply
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_comment'])) {
    $post_id = intval($_POST['post_id']);
    $parent_comment_id = isset($_POST['parent_comment_id']) ? intval($_POST['parent_comment_id']) : null;
    $student_id = $_SESSION['student_id'];
    $comment_content = trim($_POST['comment_content']);

    if (!empty($comment_content)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, student_id, content, parent_comment_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iisi", $post_id, $student_id, $comment_content, $parent_comment_id);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Comment content cannot be empty.";
    }

    // Refresh the page to display the new comment
    header("Location: studenthomepage.php");
    exit();
}

function displayComments($post_id, $parent_comment_id = null, $conn, $level = 0)
{
    $sql = "SELECT comments.comment_id, comments.content AS comment_content, comments.created_at AS comment_date, 
            student.first_name, student.last_name, comments.parent_comment_id 
            FROM comments 
            JOIN student ON comments.student_id = student.id 
            WHERE comments.post_id = ? AND comments.parent_comment_id " . ($parent_comment_id ? "= ?" : "IS NULL") . " 
            ORDER BY comments.created_at ASC";

    $stmt = $conn->prepare($sql);
    if ($parent_comment_id) {
        $stmt->bind_param("ii", $post_id, $parent_comment_id);
    } else {
        $stmt->bind_param("i", $post_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($comment = $result->fetch_assoc()) {
        echo "<div class='comment' style='margin-left: " . ($level * 20) . "px;'>";
        echo "<strong>{$comment['first_name']} {$comment['last_name']}</strong>: ";
        echo htmlspecialchars($comment['comment_content']);
        echo "<small> ({$comment['comment_date']})</small>";

        // If this is a reply, show the parent comment indicator
        if ($parent_comment_id) {
            echo "<div class='reply-indicator'><em>Replying to comment ID: {$comment['first_name']} {$comment['last_name']}</em></div>";
        }

        echo "<form method='post' class='reply-form' style='margin-top: 10px;'>
                <input type='hidden' name='post_id' value='{$post_id}'>
                <input type='hidden' name='parent_comment_id' value='{$comment['comment_id']}'>
                <textarea name='comment_content' placeholder='Write a reply...' required></textarea><br>
                <button type='submit' name='new_comment'>Reply</button>
              </form>";

        echo "</div>";

        // Recursive call to display replies of this comment
        displayComments($post_id, $comment['comment_id'], $conn, $level + 1);
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <title>BSIT | Home</title>
    <link rel="stylesheet" href="./css/homepage.css">
    <link rel="icon" href="./images/bsitlogo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />

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
                    <a href="studenthomepage.php">
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

    <div class="home">
        <div class="container">
            <div class="home-weapper">
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const modal = document.getElementById("form-modal");
                        const modalTitle = document.getElementById("form-modal-title");
                        const formIdInput = document.getElementById("form-id-input");
                        const formFieldsContainer = document.getElementById("form-fields-container");
                        const closeModal = document.querySelector(".close-modal");

                        // Function to open modal and populate form fields
                        function openModal(formId, formName) {
                            modalTitle.textContent = `Fill Out Form: ${formName}`;
                            formIdInput.value = formId;
                            formFieldsContainer.innerHTML = ""; // Clear previous fields

                            // Fetch form fields via AJAX
                            fetch(`fetch_form_fields.php?form_id=${formId}`)
                                .then(response => response.json())
                                .then(data => {
                                    data.fields.forEach(field => {
                                        const fieldElement = `
                            <div>
                                <label for="field_${field.id}">${field.name}:</label>
                                <input type="text" id="field_${field.id}" name="responses[${field.id}]" required>
                            </div>
                        `;
                                        formFieldsContainer.insertAdjacentHTML("beforeend", fieldElement);
                                    });
                                });

                            // Show modal
                            modal.style.display = "block";
                        }

                        // Open modal on button click
                        document.querySelectorAll(".open-form-btn").forEach(button => {
                            button.addEventListener("click", function() {
                                const formId = this.dataset.formId;
                                const formName = this.dataset.formName;

                                // Open modal and populate the form
                                openModal(formId, formName);
                            });
                        });

                        // Close modal on click
                        closeModal.addEventListener("click", function() {
                            modal.style.display = "none";
                        });

                        window.addEventListener("click", function(event) {
                            if (event.target === modal) {
                                modal.style.display = "none";
                            }
                        });

                        // Automatically open the first unfilled form
                        const unfilledFormButton = Array.from(document.querySelectorAll(".open-form-btn"))
                            .find(button => !button.disabled);
                        if (unfilledFormButton) {
                            unfilledFormButton.click();
                        }
                    });
                </script>
                <style>
                    .modal {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.5);
                        display: none;
                        justify-content: center;
                        align-items: center;
                        z-index: 1000;

                    }

                    .modal-content {
                        background: #fff;
                        position: relative;
                        top: 50%;
                        left: 35%;
                        padding: 20px;
                        border-radius: 8px;
                        width: 90%;
                        max-width: 500px;
                    }

                    .close-modal {
                        float: right;
                        cursor: pointer;
                        font-size: 20px;
                    }
                </style>

                <div class="home-center">
                    <div class="home-center-wrapper">


                        <div class="createPost">

                            <h3 class="mini-headign">Create Post</h3>
                            <div class="post-text" onclick="openPopup()">
                                <img style="height: 40%;" src="<?= htmlspecialchars('./' . $profile_image); ?>" alt="Profile Image" class="profile-img" />
                                <input type="text-area" placeholder="What's on your mind, <?= htmlspecialchars($first_name . ' ' . $last_name) ?>">
                            </div>

                            <div class="popup-overlay" id="post-popup" style="display: none;">
                                <div class="post-popup">
                                    <div class="popup-header">
                                        <span>Create post</span>
                                        <button onclick="closePopup()">×</button>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="postpopup-content">
                                            <div class="profile-container">
                                                <a href="studentProfile.php">
                                                    <img style="width: 45px; height:45px; border-radius:50%; margin:10px;"
                                                        src="<?= htmlspecialchars('./' . $profile_image); ?>"
                                                        alt="Profile Image" class="profile-img">
                                                </a>
                                                <p class="profile-name"><?= htmlspecialchars($first_name . ' ' . $last_name); ?></p>
                                            </div>
                                            <textarea name="content" placeholder="What's on your mind? <?= htmlspecialchars($first_name); ?>" required></textarea>
                                            <div class="add-photos" onclick="triggerFileUpload()">
                                                <input type="file" id="media-upload" name="media[]" multiple accept="image/*,video/*"
                                                    style="display: none;" onchange="previewFiles(event)">
                                                <p>Add photos</p>
                                            </div>
                                            <div id="media-preview" class="media-grid"></div>
                                        </div>
                                        <button id="delete-post" class="cancel-button" style="display: none;" onclick="clearFiles()">Cancel Post</button>
                                        <button class="post-btn" type="submit" name="new_post">Post</button>

                                    </form>
                                </div>
                            </div>
                            <?php while ($post = $posts_result->fetch_assoc()): ?>
                                <div class="post">
                                    <div class="profile-section">
                                        <img
                                            style="width: 45px; height: 45px; border-radius: 50%;"
                                            src="<?= htmlspecialchars($post['profile_image']); ?>"
                                            alt="Profile Image"
                                            class="profile-image">
                                        <div class="profile-info">
                                            <h3><?= htmlspecialchars($post['first_name'] . ' ' . $post['last_name']); ?></h3>
                                            <small>Posted on: <?= htmlspecialchars($post['post_date']); ?></small>
                                        </div>
                                    </div>
                                    <br>


                                    <?php if (!empty($post['post_content'])): ?>
                                        <p><?= htmlspecialchars($post['post_content']); ?></p>
                                    <?php endif; ?>
                                    <hr>
                                    <br>
                                    <?php if (!empty($post['media_path'])): ?>
                                        <img src="<?= htmlspecialchars($post['media_path']); ?>" alt="Media">
                                    <?php endif; ?>
                                    <hr>
                                    <a href="like.php?post_id=<?= $post['post_id']; ?>">Like</a> (<?= $post['like_count']; ?>)


                                    <button
                                        style="margin-left: 45px; background: none; border: none; color: #007BFF; text-decoration: underline; cursor: pointer;"
                                        id="toggle-comments-btn-<?= $post['post_id']; ?>"
                                        onclick="toggleComments(<?= $post['post_id']; ?>)">
                                        Comments
                                    </button><br>

                                    <div id="comments-<?= $post['post_id']; ?>" class="comments-section">
                                        <?php displayComments($post['post_id'], null, $conn); ?>
                                        <form method="post" style="margin-top: 10px;">
                                            <input type="hidden" name="post_id" value="<?= $post['post_id']; ?>">
                                            <textarea name="comment_content" placeholder="Write a comment..." required></textarea><br>
                                            <button type="submit" name="new_comment">Comment</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>


                        <script>
                            function toggleComments(postId) {
                                const commentsSection = document.getElementById(`comments-${postId}`);
                                if (commentsSection) {
                                    commentsSection.style.display = commentsSection.style.display === 'block' ? 'none' : 'block';
                                }
                            }
                        </script>
                    </div>
                </div>

                <div class="home-right">
                    <div class="home-right-wrapper">


                        <div class="event-friend">
                            <div class="header-announcement" style="
        background-color: #f8f9fa; 
        border: 1px solid #ddd; 
        border-radius: 8px; 
        padding: 20px; 
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    ">
                                <?php
                                // Fetch announcements
                                $announcement_sql = "SELECT content, created_at FROM announcements ORDER BY created_at DESC";
                                $announcement_result = $conn->query($announcement_sql);

                                if ($announcement_result->num_rows > 0): ?>
                                    <h2 style="
                font-size: 1.5em; 
                font-weight: bold; 
                margin-bottom: 15px; 
                border-bottom: 2px solid #007BFF; 
                padding-bottom: 5px;
                color: #333;
            ">Announcements</h2>
                                    <div class="announcements-section" style="
                max-height: 300px; 
                overflow-y: auto;
                padding-right: 10px;
            ">
                                        <?php while ($announcement = $announcement_result->fetch_assoc()): ?>
                                            <div class="announcement" style="
                        margin-bottom: 15px; 
                        padding: 10px; 
                        background-color: #fff; 
                        border: 1px solid #ddd; 
                        border-radius: 5px;
                        transition: all 0.3s ease;
                    "
                                                onmouseover="this.style.boxShadow='0 4px 8px rgba(0, 0, 0, 0.15)'"
                                                onmouseout="this.style.boxShadow='none'">
                                                <p style="
                            font-size: 1em; 
                            color: #555; 
                            margin-bottom: 8px;
                        "><?= htmlspecialchars($announcement['content']); ?></p>
                                                <small style="
                            font-size: 0.9em; 
                            color: #888;
                        ">Posted on: <?= htmlspecialchars($announcement['created_at']); ?></small>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                <?php else: ?>
                                    <p style="
                font-size: 1em; 
                color: #777; 
                text-align: center; 
                margin-top: 20px;
            ">No announcements available.</p>
                                <?php endif; ?>
                            </div>
                        </div>




                    </div>
                </div>


            </div>
        </div>
    </div>
    <script>
        function toggleComments(postId) {
            var commentsSection = document.getElementById('comments-' + postId);
            var button = document.getElementById('toggle-comments-btn-' + postId);

            if (commentsSection.style.display === "none") {
                commentsSection.style.display = "block";
                button.textContent = "Hide Comments";
            } else {
                commentsSection.style.display = "none";
                button.textContent = "Show Comments";
            }
        }
    </script>
    <script>
        var darkButton = document.querySelector(".darkTheme");

        darkButton.onclick = function() {
            darkButton.classList.toggle("button-Active");
            document.body.classList.toggle("dark-color")
        }

        function openPopup() {
            document.getElementById("post-popup").style.display = "flex";
        }

        function closePopup() {
            document.getElementById("post-popup").style.display = "none";
        }

        function triggerFileUpload() {
            document.getElementById("media-upload").click();
        }

        function previewFiles(event) {
            const files = event.target.files;
            const previewContainer = document.getElementById("media-preview");
            previewContainer.innerHTML = ""; // Clear previous previews

            for (const file of files) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const media = file.type.startsWith("image") ?
                        `<center>
                        <img style="width:45%;" src="${e.target.result}" alt="Preview">
                        </center>` :
                        `<video src="${e.target.result}" controls></video>`;
                    previewContainer.innerHTML += media;
                };
                reader.readAsDataURL(file);
            }
        }

        function clearFiles() {
            document.getElementById("media-upload").value = "";
            document.getElementById("media-preview").innerHTML = "";
        }
    </script>

</body>

</html>