<?php
    session_start();
    require '../config.php';

    session_unset();
    session_destroy();

    header("Location: " . $baseUrl . "index.php");
    exit();
?>