<?php
    require '../config.php';

    if (isset($_POST['query'])) {
        $query = trim($_POST['query']);

        $stmt = $conn->prepare("SELECT * FROM posts 
                                WHERE title LIKE :query OR body LIKE :query 
                                ORDER BY created_at DESC");
        $stmt->execute(['query' => "%$query%"]);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($posts) {
            foreach ($posts as $post) {
                ?>
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($post['title']); ?></h5>
                        <p class="card-text">
                            <?= nl2br(htmlspecialchars(substr($post['body'], 0, 200))); ?>...
                        </p>
                        <small class="text-muted">
                            Posted on <?= date("d M Y H:i", strtotime($post['created_at'])); ?>
                            by <span class="text-primary"><?= htmlspecialchars($post['username']); ?></span>
                        </small><br>
                        <a href="posts/post.php?id=<?= $post['id']; ?>" class="btn btn-primary btn-sm mt-2">Read More</a>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="alert alert-info">No results found.</div>';
        }
    }
    exit;
?>