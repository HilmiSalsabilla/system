<?php require "../includes/header.php"; ?>
<?php 
    require '../config.php'; 

    // Kalau sudah login, redirect ke index
    if(isset($_SESSION['username'])) {
        header("Location: ../index.php");
        exit();
    }

    $message = "";
    $message_type = "";

    if (isset($_POST['register'])) {
        $email = trim($_POST['email']);
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($email) || empty($username) || empty($password)) {
            $message = "Please fill all the fields.";
            $message_type = "danger";
        } else {
            $check = $conn->prepare("SELECT * FROM users WHERE email = :email OR username = :username");
            $check->execute([':email' => $email, ':username' => $username]);

            if ($check->rowCount() > 0) {
                $message = "Email or Username already exists!";
                $message_type = "danger";
            } else {
                $insert = $conn->prepare("INSERT INTO users (email, username, password) VALUES (:email, :username, :password)");
                $insert->execute([
                    ':email' => $email,
                    ':username' => $username,
                    ':password' => password_hash($password, PASSWORD_DEFAULT)
                ]);

                $message = "Registration successful! Please login.";
                $message_type = "success";
                header("refresh:2;url=login.php");
            }
        }
    }
?>

<main class="form-signin w-50 m-auto">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="register.php">
        <h1 class="h3 mt-5 mb-5 fw-normal text-center">Please Register</h1>

        <div class="form-floating mb-3">
            <input name="email" type="email" class="form-control" id="email" placeholder="name@example.com" required>
            <label for="email">Email address</label>
        </div>

        <div class="form-floating mb-3">
            <input name="username" type="text" class="form-control" id="username" placeholder="username" required>
            <label for="username">Username</label>
        </div>

        <div class="form-floating mb-3">
            <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>

        <button name="register" class="w-100 btn btn-lg btn-primary" type="submit">Register</button>

        <h6 class="mt-3 text-center">
            Already have an account? <a href="login.php">Login</a>
        </h6>
    </form>
</main>

<?php require "../includes/footer.php"; ?>
