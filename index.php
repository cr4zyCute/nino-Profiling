<?php
session_start();
include './database/db.php';
$error_message = "";
$show_popup = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password FROM student WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        // Check if the password matches (without hashing)
        if ($password === $stored_password) {
            $_SESSION['student_id'] = $id;
            header("Location: student.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No student found with this email.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link
        href="https://cdn.jsdelivr.net/npm/remixicon@4.1.0/fonts/remixicon.css" rel="stylesheet" />
    <link rel="icon" href="./pictures/bsitlogo.png">
    <link rel="stylesheet" href="./css/index.css" />
    <title>CTU-BSIT-Home</title>
</head>

<body>

    <nav>
        <div class="nav__header">
            <div class="nav__logo">
                <a href="#">
                    <img src="./pictures/bsitlogo.png" alt="Logo" class="logo">
                    BSIT
                </a>
            </div>
            <div class="nav__menu__btn" id="menu-btn">
                <i class="ri-menu-line"></i>
            </div>
        </div>
        <ul class="nav__links" id="nav-links">
            <li><a href="#home">HOME</a></li>
            <li><a href="#about">ABOUT US</a></li>
            <li><a href="#footer">CONTACT</a></li>

            <li class="nav__login"><button onclick="openPopup()" style="color: black;" id="login-btn" class="btn">Log In</button></li>
        </ul>
    </nav>

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup" id="loginPopup">
        <div class="login-container">
            <form action="" method="post">
                <span class="close">&times;</span>
                <div class="profile-icon">
                    <img src="./pictures/bsitlogo.png" alt="User Icon">
                </div>
                <div class="title">Log in</div>
                <p id="error-message" style="color:red"><?php echo $error_message; ?></p>

                <input type="text" name="email" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>

                <div class="register__here">
                    <p>Don't have an account?</p>
                    <a href="studentRegistration.php">Register Here!</a>
                </div>


                <button type="submit" name="login" class="submit button">Login Now</button>

            </form>
        </div>
    </div>
    </div>

    <div id="home" class="container">

        <div class="container__right">
            <div class="images">
                <img src="./pictures/bsitlogo.png" alt="image" class="tent-1" />
            </div>
            <div class="content">
                <h2>Bachelor of Science <br> in Information Technology</h2>
                <h3>Your Future Starts Here!!</h3>
                <p>
                    The Bachelor of Science in Information Technology (BSIT) equips students with cutting-edge skills in programming, networking, preparing them to thrive in the rapidly evolving tech landscape.
                </p>
            </div>
        </div>
    </div>

    <section id="about">
        <div class="intro-text">
            <h2>Explore BSIT Programs</h2>
            <p>
                Discover the wide-ranging opportunities offered by our Bachelor of Science in Information Technology program. Whether you’re passionate about innovation, technology, or academic excellence, our BSIT program equips you with the tools for success in the ever-evolving IT industry.
            </p>
        </div>

        <div class="container">
            <div class="cards reveal">
                <div class="text-card">
                    <img src="./images/bg.jpg" alt="Description of Image" class="card-image">
                    <h3>BSIT</h3>
                    <p>Celebrate academic excellence with the Bachelor of Science in Information Technology program at Cebu Technological University – Consolacion Campus. Whether you're gearing up for the future or proudly donning your graduation attire, this program paves the way for success in the dynamic IT field.</p>
                </div>
            </div>
            <div class="cards reveal">
                <div class="text-card">
                    <img src="./images/bg.jpg" alt="Description of Image" class="card-image">
                    <h3>BSIT</h3>
                    <p>Celebrate academic excellence with the Bachelor of Science in Information Technology program at Cebu Technological University – Consolacion Campus. Whether you're gearing up for the future or proudly donning your graduation attire, this program paves the way for success in the dynamic IT field.</p>
                </div>
            </div>
            <div class="cards reveal">
                <div class="text-card">
                    <img src="./images/bg.jpg" alt="Description of Image" class="card-image">
                    <h3>BSIT</h3>
                    <p>Celebrate academic excellence with the Bachelor of Science in Information Technology program at Cebu Technological University – Consolacion Campus. Whether you're gearing up for the future or proudly donning your graduation attire, this program paves the way for success in the dynamic IT field.</p>
                </div>
            </div>
        </div>
    </section>

    <div id="footer">

        <?php
        include './includes/footer.php'
        ?>
    </div>

    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="./js/index.js"></script>
    <script>
        const popup = document.getElementById('loginPopup');
        const overlay = document.getElementById('popupOverlay');
        const closeBtn = document.querySelector('.close'); // Select the close button

        function openPopup() {
            popup.classList.add('active');
            overlay.classList.add('active');
        }

        function closePopup() {
            popup.classList.remove('active');
            overlay.classList.remove('active');
        }

        // Keep popup open if there's an error message
        <?php if ($show_popup): ?>
            openPopup();
        <?php endif; ?>

        // Add an event listener to the close button to close the popup
        closeBtn.addEventListener('click', closePopup);

        overlay.addEventListener('click', closePopup); // Also close popup when clicking on the overlay
        document.addEventListener('DOMContentLoaded', reveal);

        document.addEventListener("DOMContentLoaded", function() {
            const errorMessage = document.getElementById("error-message");

            if (errorMessage) {
                // Set a timeout to hide the error message after 5 seconds
                setTimeout(() => {
                    errorMessage.style.display = "none";
                }, 3000);
            }
        });
    </script>

</body>

</html>