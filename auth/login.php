<?php 
    require "../includes/header.php"; 
    require '../config.php'; 

    // Kalau sudah login, langsung ke index
    if (isset($_SESSION['username'])) {
        header("Location: " . $baseUrl . "index.php");
        exit();
    }

    $message = "";
    $message_type = "";

    if (isset($_POST['login'])) {
        $loginId = trim($_POST['login_id']); // bisa email atau username
        $password = trim($_POST['password']);

        if (empty($loginId) || empty($password)) {
            $message = "Please fill all the fields.";
            $message_type = "danger";
        } else {
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :login OR username = :login LIMIT 1");
            $stmt->execute([':login' => $loginId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];

                $message = "Login successful! Redirecting...";
                $message_type = "success";
            } else {
                $message = "Invalid email/username or password!";
                $message_type = "danger";
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

    <form action="login.php" method="post">
        <h1 class="h3 mt-5 mb-5 fw-normal text-center">Please sign in</h1>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" id="login_id" name="login_id" placeholder="Email or Username" required>
            <label for="login_id">Email or Username</label>
        </div>

        <div class="form-floating mb-3">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            <label for="password">Password</label>
        </div>

        <button class="w-100 btn btn-lg btn-primary" type="submit" name="login">Sign in</button>
        <h6 class="mt-3 text-center">
            Don't have an account? <a href="register.php">Create your account</a>
        </h6>
    </form>
</main>

<?php if ($message_type === 'success'): ?>
    <script>
        setTimeout(() => {
            window.location.href = "<?= $baseUrl ?>index.php";
        }, 2000);
    </script>
<?php endif; ?>

<?php require "../includes/footer.php"; ?>
