<?php
    require '../config.php';
    session_start();

    // Cek login
    if (empty($_SESSION['username'])) {
        echo json_encode([
            "status" => "error",
            "message" => "You must be logged in to post a comment."
        ]);
        exit();
    }

    // Pastikan request POST dan ada data
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'], $_POST['post_id'])) {
        $comment  = trim($_POST['comment']);
        $post_id  = (int) $_POST['post_id'];
        $username = $_SESSION['username'];

        if (!empty($comment) && $post_id > 0) {
            try {
                $select = $conn->prepare("
                    INSERT INTO comments (post_id, comment, username) 
                    VALUES (:post_id, :comment, :username)
                ");
                $select->execute([
                    'post_id'  => $post_id,
                    'comment'  => $comment,
                    'username' => $username
                ]);

                echo json_encode([
                    "status" => "success",
                    "message" => "Comment posted successfully!"
                ]);
                exit();

            } catch (PDOException $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Database Error: " . $e->getMessage()
                ]);
                exit();
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Comment cannot be empty."
            ]);
            exit();
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request."
        ]);
        exit();
    }
?>