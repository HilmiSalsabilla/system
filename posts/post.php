<?php
    require '../includes/header.php';
    require '../config.php';

    // Flash message
    $message = "";
    $message_type = "";
    if (!empty($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $message_type = $_SESSION['message_type'] ?? "info";
        unset($_SESSION['message'], $_SESSION['message_type']);
    }

    // Ambil id dari URL
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['message'] = "Post ID is missing.";
        $_SESSION['message_type'] = "danger";
        header("Location: ../index.php");
        exit();
    }

    $id = (int) $_GET['id'];

    // Ambil post berdasarkan ID
    try {
        $select = $conn->prepare("SELECT * FROM posts WHERE id = :id LIMIT 1");
        $select->execute(['id' => $id]);
        $post = $select->fetch();
    } catch (PDOException $e) {
        die("Error fetching post: " . $e->getMessage());
    }

    // Jika tidak ada post
    if (!$post) {
        $_SESSION['message'] = "Post not found.";
        $_SESSION['message_type'] = "warning";
        header("Location: ../index.php");
        exit();
    }

    // Ambil semua komentar
    try {
        $select = $conn->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY id DESC");
        $select->execute(['post_id' => $id]);
        $comments = $select->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching comments: " . $e->getMessage());
    }

    // Ambil rata-rata rating
    try {
        $select = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total 
                                FROM ratings WHERE post_id = :post_id");
        $select->execute(['post_id' => $id]);
        $ratingData = $select->fetch(PDO::FETCH_ASSOC);
        $avgRating = $ratingData['avg_rating'] ?? 0;
        $totalRating = $ratingData['total'] ?? 0;
    } catch (PDOException $e) {
        die("Error fetching rating: " . $e->getMessage());
    }

?>

<div class="container mt-5 mb-5">
    <!-- Alert -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Post Detail -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-4">
            <h2 class="fw-bold mb-3"><?= htmlspecialchars($post['title']); ?></h2>
            <p class="card-text fs-6 lh-lg"><?= nl2br(htmlspecialchars($post['body'])); ?></p>

            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <small class="text-muted">
                    Posted on <?= date("d M Y H:i", strtotime($post['created_at'])); ?>
                    by <span class="fw-semibold text-primary"><?= htmlspecialchars($post['username']); ?></span>
                </small>
                <a href="../index.php" class="btn btn-sm btn-outline-secondary">← Back to Posts</a>
            </div>
        </div>
    </div>

    <!-- Rating -->
    <div class="card mb-4 border-0">
        <div class="card-body">
            <div class="my-rating mb-2"></div>
            <small class="text-muted">
                ⭐ Average: <?= number_format($avgRating, 1); ?>/5 (<?= $totalRating; ?> votes)
            </small>
        </div>
    </div>

    <!-- Comment Form -->
    <?php if (!empty($_SESSION['username'])): ?>
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3 fw-semibold">💬 Post a Comment</h5>
                <form method="post" id="comment_data">
                    <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                    <div class="form-floating mb-3">
                        <textarea 
                            name="comment" 
                            class="form-control" 
                            id="comment" 
                            placeholder="Write your comment..." 
                            style="height: 120px;" 
                            required
                        ></textarea>
                        <label for="comment">Your Comment</label>
                    </div>
                    <button class="btn btn-sm btn-primary">
                        <i class="bi bi-send"></i> Post Comment
                    </button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">Please <a href="../auth/login.php">login</a> to post a comment.</div>
    <?php endif; ?>

    <!-- Display Comments -->
    <h5 class="fw-bold mb-3">Comments (<?= count($comments); ?>)</h5>
    <?php if (empty($comments)): ?>
        <p class="text-muted">No comments yet. Be the first to comment!</p>
    <?php else: ?>
        <?php foreach ($comments as $comment): ?>
            <div class="card mb-3 shadow-sm">
                <div class="card-body">
                    <p class="card-text mb-3"><?= nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            By <b><?= htmlspecialchars($comment['username']); ?></b> • 
                            <?= date("d M Y H:i", strtotime($comment['created_at'])); ?>
                        </small>
                        <?php if (!empty($_SESSION['username']) && ($_SESSION['username'] === $comment['username'])): ?>
                            <button type="button" 
                                class="btn btn-sm btn-outline-danger delete-comment"
                                data-id="<?= $comment['id']; ?>"
                                data-post="<?= $post['id']; ?>">
                                <i class="bi bi-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require '../includes/footer.php'; ?>

<!-- Script -->
<script>
    // Submit Comment via AJAX
    $(document).ready(function() {
        $('#comment_data').on('submit', function(e) {
            e.preventDefault();
            var formdata = $(this).serialize();

            $.ajax({
                type: "POST",
                url: "../comment/insert.php",
                data: formdata,
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        Swal.fire("Success", response.message, "success");
                        $('#comment_data')[0].reset();
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        Swal.fire("Error", response.message, "error");
                    }
                },
                error: function() {
                    Swal.fire("Error", "Something went wrong. Please try again.", "error");
                }
            });
        });
    });

    // Delete Comment via AJAX + SweetAlert
    $(document).on('click', '.delete-comment', function(e) {
        e.preventDefault();
        let commentId = $(this).data('id');
        let postId = $(this).data('post');

        Swal.fire({
            title: "Are you sure?",
            text: "This comment will be deleted permanently.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "POST",
                    url: "../comment/delete.php",
                    data: { id: commentId, post_id: postId },
                    dataType: "json",
                    success: function(response) {
                        Swal.fire(response.status, response.message, response.status);
                        if (response.status === "success") {
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function() {
                        Swal.fire("Error", "Something went wrong.", "error");
                    }
                });
            }
        });
    });

    // Star Rating
    $(document).ready(function() {
        $(".my-rating").starRating({
            initialRating: <?= $avgRating ?: 0 ?>,
            starSize: 30,
            disableAfterRate: <?= empty($_SESSION['username']) ? 'true' : 'false' ?>,
            callback: function(currentRating, $el){
                <?php if (!empty($_SESSION['username'])): ?>
                $.ajax({
                    type: "POST",
                    url: "../ratings/save.php",
                    data: {
                        post_id: <?= $post['id']; ?>,
                        rating: currentRating
                    },
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            Swal.fire("Thank you!", "Your rating has been saved.", "success");
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            Swal.fire("Error", response.message, "error");
                        }
                    }
                });
                <?php endif; ?>
            }
        });
    });
</script>
