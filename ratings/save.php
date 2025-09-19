<?php
    session_start();
    require '../config.php';
    header('Content-Type: application/json');

    // Pastikan user login
    if (empty($_SESSION['username'])) {
        echo json_encode([
            "status" => "error",
            "message" => "You must be logged in to rate."
        ]);
        exit();
    }

    // Validasi input
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['post_id']) && !empty($_POST['rating'])) {
        $post_id = (int) $_POST['post_id'];
        $rating = (float) $_POST['rating'];
        $username = $_SESSION['username'];

        try {
            // cek apakah user sudah pernah kasih rating
            $check = $conn->prepare("SELECT id FROM ratings WHERE post_id = :post_id AND username = :username");
            $check->execute([
                'post_id'  => $post_id,
                'username' => $username
            ]);

            if ($check->rowCount() > 0) {
                // update rating
                $update = $conn->prepare("UPDATE ratings SET rating = :rating, updated_at = NOW() 
                                        WHERE post_id = :post_id AND username = :username");
                $update->execute([
                    'rating'   => $rating,
                    'post_id'  => $post_id,
                    'username' => $username
                ]);
            } else {
                // insert rating
                $insert = $conn->prepare("INSERT INTO ratings (post_id, username, rating, created_at) 
                                        VALUES (:post_id, :username, :rating, NOW())");
                $insert->execute([
                    'post_id'  => $post_id,
                    'username' => $username,
                    'rating'   => $rating
                ]);
            }

            echo json_encode([
                "status" => "success",
                "message" => "Your rating has been saved!"
            ]);
            exit();
        } catch (PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Database error: " . $e->getMessage()
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