<?php
    try {
        $host = 'localhost';
        $dbname = 'system_db';
        $user = 'root';
        $pass = '';
        
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Connected successfully";
    }catch(PDOException $e) {
        echo 'error is:' . $e->getMessage();
    }
?>