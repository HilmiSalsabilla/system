<?php
    session_start();
    require '../config.php'; 
    header('Content-Type: application/json');

    if (empty($_SESSION['username'])) {
        echo json_encode([
            "status"  => "error", 
            "message" => "Please login first",
            "loginUrl" => $baseUrl . "auth/login.php"
        ]);
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['post_id']) && !empty($_POST['action'])) {
        $post_id  = (int) $_POST['post_id'];
        $action   = $_POST['action'];
        $username = $_SESSION['username'];

        if (!in_array($action, ['like','dislike'])) {
            echo json_encode(["status" => "error", "message" => "Invalid action"]);
            exit();
        }

        try {
            // cek apakah user sudah pernah vote
            $check = $conn->prepare("
                SELECT id FROM likes 
                WHERE post_id = :post_id AND username = :username
            ");
            $check->execute(['post_id' => $post_id, 'username' => $username]);

            if ($check->rowCount() > 0) {
                // update vote
                $update = $conn->prepare("
                    UPDATE likes 
                    SET action = :action 
                    WHERE post_id = :post_id AND username = :username
                ");
                $update->execute([
                    'action'   => $action, 
                    'post_id'  => $post_id, 
                    'username' => $username
                ]);
            } else {
                // insert baru
                $insert = $conn->prepare("
                    INSERT INTO likes (post_id, username, action) 
                    VALUES (:post_id, :username, :action)
                ");
                $insert->execute([
                    'post_id'  => $post_id, 
                    'username' => $username, 
                    'action'   => $action
                ]);
            }

            // hitung ulang
            $count = $conn->prepare("
                SELECT 
                    SUM(action = 'like') as likes,
                    SUM(action = 'dislike') as dislikes 
                FROM likes 
                WHERE post_id = :post_id
            ");
            $count->execute(['post_id' => $post_id]);
            $data = $count->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                "status"    => "success",
                "likes"     => $data['likes'],
                "dislikes"  => $data['dislikes'],
                "postUrl"   => $baseUrl . "posts/post.php?id=" . $post_id
            ]);
        } catch (PDOException $e) {
            echo json_encode([
                "status"  => "error", 
                "message" => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid request"]);
    }
