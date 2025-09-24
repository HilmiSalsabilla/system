<?php
    require '../includes/header.php';
    require '../config.php';

    // Cegah akses create jika belum login
    if (empty($_SESSION['username'])) {
        header('Location: ' . $baseUrl . 'auth/login.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
        $title = htmlspecialchars(trim($_POST['title']));
        $body = htmlspecialchars(trim($_POST['body']));
        $username = $_SESSION['username'];

        if (!empty($title) && !empty($body)) {
            try {
                $select = $conn->prepare("
                    INSERT INTO posts (title, body, username) 
                    VALUES (:title, :body, :username)
                ");
                $select->execute([
                    'title'    => $title,
                    'body'     => $body,
                    'username' => $username
                ]);

                $_SESSION['message'] = "Post created successfully!";
                $_SESSION['message_type'] = "success";
                header('Location: ' . $baseUrl . 'index.php');
                exit();
            } catch (PDOException $e) {
                $_SESSION['message'] = "Error: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
                header('Location: ' . $baseUrl . 'index.php');
                exit();
            }
        } else {
            $_SESSION['message'] = "Title and Body cannot be empty.";
            $_SESSION['message_type'] = "danger";
            header('Location: ' . $baseUrl . 'index.php');
            exit();
        }
    }
?>

<main class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0 rounded-3">
                <div class="card-header bg-light text-white text-center py-3 rounded-top">
                    <h4 class="mb-0 text-black">Create New Post</h4>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="<?= $baseUrl ?>posts/create.php">

                        <!-- Title -->
                        <div class="form-floating mb-3">
                            <input 
                                name="title" 
                                type="text" 
                                class="form-control rounded-3" 
                                id="title" 
                                placeholder="Title" 
                                required
                            >
                            <label for="title">Post Title</label>
                        </div>

                        <!-- Body -->
                        <div class="form-floating mb-4">
                            <textarea 
                                name="body" 
                                class="form-control rounded-3" 
                                id="body" 
                                style="height: 200px;" 
                                placeholder="Write your post..." 
                                required
                            ></textarea>
                            <label for="body">Post Content</label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="<?= $baseUrl ?>index.php" class="btn btn-sm btn-outline-secondary px-4">
                                Cancel
                            </a>
                            <button 
                                type="submit" 
                                name="create" 
                                class="btn btn-sm btn-primary px-4"
                            >
                                Create Post
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require '../includes/footer.php'; ?>
