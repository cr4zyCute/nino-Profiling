<?php
session_start();
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        die('Username and password are required.');
    }

    $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $stored_password);
        $stmt->fetch();

        if ($password === $stored_password) {
            // Set session variable for admin
            $_SESSION['admin_id'] = $id;
            header('Location: admin.php');
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "Admin not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
</head>
<style>
    @import url("https://fonts.googleapis.com/css2?family=Quicksand:wght@300&display=swap");

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: "Quicksand", sans-serif;
    }

    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #231942, #6a3e7a);
        width: 100%;
        overflow: hidden;
    }

    .ring {
        position: relative;
        width: 700px;
        height: 700px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .ring i {
        position: absolute;
        inset: 0;
        border: 2px solid #fff;
        transition: 0.5s;
    }

    .ring i:nth-child(1) {
        border-radius: 38% 62% 63% 37% / 41% 44% 56% 59%;
        animation: animate 6s linear infinite;
    }

    .ring i:nth-child(2) {
        border-radius: 41% 44% 56% 59%/38% 62% 63% 37%;
        animation: animate 4s linear infinite;
    }

    .ring i:nth-child(3) {
        border-radius: 41% 44% 56% 59%/38% 62% 63% 37%;
        animation: animate2 10s linear infinite;
    }

    .ring:hover i {
        border: 6px solid var(--clr);
        filter: drop-shadow(0 0 20px var(--clr));
    }

    @keyframes animate {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @keyframes animate2 {
        0% {
            transform: rotate(360deg);
        }

        100% {
            transform: rotate(0deg);
        }
    }

    .login {
        position: absolute;
        width: 300px;
        height: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 20px;
    }

    .login h2 {
        font-size: 2em;
        color: #fff;
    }

    .login .inputBx {
        position: relative;
        width: 100%;
    }

    .login .inputBx input {
        position: relative;
        width: 100%;
        padding: 12px 20px;
        background: transparent;
        border: 2px solid #fff;
        border-radius: 40px;
        font-size: 1.2em;
        color: #fff;
        box-shadow: none;
        outline: none;
    }

    .login .inputBx input[type="submit"] {
        width: 100%;
        background: #0078ff;
        background: linear-gradient(135deg, #231942, #E0B1CB);

        border: none;
        cursor: pointer;
    }

    .login .inputBx input::placeholder {
        color: rgba(255, 255, 255, 0.75);
    }

    .login .links {
        position: relative;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
    }

    .login .links a {
        color: #fff;
        text-decoration: none;
    }
</style>

<body>
    <form action="" method="post">
        <section id="formContainer" class="<?= !empty(trim($error_message)) ? 'show' : '' ?>">
            <div class="ring">
                <i style="--clr:#d7dbdd;"></i>
                <i style="--clr:#d7dbdd;"></i>
                <i style="--clr:#d7dbdd;"></i>
                <div class="login">
                    <h2>Welcome Admin</h2>
                    <div class="inputBx">
                        <?php if (!empty($error_message)) : ?>
                            <p id="errorMessage" style="color: white;"><?= $error_message; ?></p>
                        <?php endif; ?>
                        <input type="text" name="username" placeholder="Username" required>
                    </div>
                    <div class="inputBx">
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="inputBx">
                        <input type="submit" name="login" value="Log in">
                    </div>
                    <div class="links">
                    </div>
                </div>
            </div>
        </section>
    </form>
</body>

</html>