<?php
    require '../config.php';
    session_start();

    // Cek login
    if (empty($_SESSION['username'])) {
        echo json_encode([
            "status" => "error",
            "message" => "You must be logged in to delete a comment."
        ]);
        exit;
    }

    // Pastikan request POST dan ada data id & post_id
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['post_id'])) {
        $comment_id = (int) $_POST['id'];
        $post_id    = (int) $_POST['post_id'];

        if ($comment_id > 0 && $post_id > 0) {
            try {
                // Pastikan hanya pemilik komentar yang bisa hapus
                $select = $conn->prepare("SELECT username FROM comments WHERE id = :id AND post_id = :post_id");
                $select->execute([
                    'id'      => $comment_id,
                    'post_id' => $post_id
                ]);
                $comment = $select->fetch(PDO::FETCH_ASSOC);

                if (!$comment) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Comment not found."
                    ]);
                    exit;
                }

                if ($comment['username'] !== $_SESSION['username']) {
                    echo json_encode([
                        "status" => "error",
                        "message" => "You are not authorized to delete this comment."
                    ]);
                    exit;
                }

                // Hapus komentar
                $delete = $conn->prepare("DELETE FROM comments WHERE id = :id AND post_id = :post_id");
                $delete->execute([
                    'id'      => $comment_id,
                    'post_id' => $post_id
                ]);

                echo json_encode([
                    "status" => "success",
                    "message" => "Comment deleted successfully."
                ]);
                exit;

            } catch (PDOException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Database Error: " . $e->getMessage()
                ]);
                exit;
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid comment or post ID."
            ]);
            exit;
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request."
        ]);
        exit;
    }
?>