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

    // Hitung like & dislike
    $likeQuery = $conn->prepare("SELECT 
        SUM(action = 'like') as likes,
        SUM(action = 'dislike') as dislikes
        FROM likes WHERE post_id = :post_id");
    $likeQuery->execute(['post_id' => $id]);
    $likeData = $likeQuery->fetch(PDO::FETCH_ASSOC);

    $likeCount = $likeData['likes'] ?? 0;
    $dislikeCount = $likeData['dislikes'] ?? 0;

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

    <!-- Rating + Like & Dislike -->
    <div class="card mb-4 border-0">
        <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-center">
            
            <!-- Rating -->
            <div class="d-flex flex-column align-items-center mb-3">
                <div class="my-rating mb-1"></div>
                <small class="text-muted">
                    <!-- <i class="bi bi-star-fill"></i> -->
                    <?= number_format($avgRating, 1); ?>/5 (<?= $totalRating; ?> votes)
                </small>
            </div>

            <!-- Like & Dislike -->
            <div class="d-flex">
                <button 
                    class="btn btn-sm btn-outline-success like-btn d-flex align-items-center justify-content-center mr-2"
                    style="min-width: 50px;"
                    data-id="<?= $post['id']; ?>">
                    <i class="bi bi-hand-thumbs-up"></i>
                    <!-- <span>Like</span> -->
                    <span class="ml-1 like-count"><?= $likeCount; ?></span>
                </button>

                <button 
                    class="btn btn-sm btn-outline-danger dislike-btn d-flex align-items-center justify-content-center"
                    style="min-width: 50px;"
                    data-id="<?= $post['id']; ?>">
                    <i class="bi bi-hand-thumbs-down"></i>
                    <!-- <span>Dislike</span> -->
                    <span class="ml-1 dislike-count"><?= $dislikeCount; ?></span>
                </button>
            </div>

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

    // Like & Dislike
    $(document).on("click", ".like-btn, .dislike-btn", function() {
        let postId = $(this).data("id");
        let action = $(this).hasClass("like-btn") ? "like" : "dislike";

        $.ajax({
            type: "POST",
            url: "../likes/save.php",
            data: { post_id: postId, action: action },
            dataType: "json",
            success: function(response) {
                if (response.status === "success") {
                    $(".like-btn[data-id='"+postId+"'] .like-count").text(response.likes);
                    $(".dislike-btn[data-id='"+postId+"'] .dislike-count").text(response.dislikes);

                    // highlight active button
                    if (action === "like") {
                        $(".like-btn[data-id='"+postId+"']")
                            .removeClass("btn-outline-success")
                            .addClass("btn-success");
                        $(".dislike-btn[data-id='"+postId+"']")
                            .removeClass("btn-danger")
                            .addClass("btn-outline-danger");
                    } else {
                        $(".dislike-btn[data-id='"+postId+"']")
                            .removeClass("btn-outline-danger")
                            .addClass("btn-danger");
                        $(".like-btn[data-id='"+postId+"']")
                            .removeClass("btn-success")
                            .addClass("btn-outline-success");
                    }
                } else {
                    alert(response.message);
                }
            },
            error: function() {
                alert("Request failed. Please try again.");
            }
        });
    });
</script>
