<?php
    require 'includes/header.php';
    require 'config.php';

    // Flash message
    $message = "";
    $message_type = "";
    if (!empty($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'] ?? "info";
        unset($_SESSION['message'], $_SESSION['message_type']); // hapus setelah ditampilkan
    }

    try {
        $select = $conn->query("SELECT * FROM posts ORDER BY created_at DESC");
        $posts = $select->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching posts: " . $e->getMessage());
    }
?>

<div class="container mt-5 mb-5">
    <!-- Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show mt-3" role="alert">
            <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Welcome Card -->
    <div class="card shadow-sm rounded-3 text-center mb-4">
        <div class="card-body p-5">
            <?php if (!empty($_SESSION['username'])): ?>
                <h3 class="mb-3">
                    Welcome <span class="text-primary">
                        <?= htmlspecialchars($_SESSION['username']); ?>
                    </span> to Blog System
                </h3>
                <p class="text-muted">Start creating and sharing your posts with others.</p>
                <a href="posts/create.php" class="btn btn-primary btn-sm mt-2">+ Create Post</a>
            <?php else: ?>
                <h3 class="mb-3">Welcome to Blog System</h3>
                <p class="text-muted">
                    Please <a href="auth/login.php">login</a> or <a href="auth/register.php">register</a> to continue.
                </p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Latest Posts -->
    <h3 class="p-3">Latest Posts</h3>

    <?php if (empty($posts)): ?>
        <div class="alert alert-info">No posts available. Be the first to create one!</div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($post['title']); ?></h5>
                    <p class="card-text">
                        <?= nl2br(htmlspecialchars(substr($post['body'], 0, 200))); ?>...
                    </p>
                    <small class="text-muted">
                        Posted on <?= date("d M Y H:i", strtotime($post['created_at'])); ?>
                        by <span class="text-primary"><?= htmlspecialchars($post['username']); ?></span>
                    </small><br>
                    <a href="posts/post.php?id=<?= $post['id']; ?>" class="btn btn-primary btn-sm mt-2">
                        Read More
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php'; ?>
