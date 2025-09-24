<?php
    session_start();
    require __DIR__ . '/../config.php';
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Blog System</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Bootstrap 5 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Star Rating Plugin -->
        <link rel="stylesheet" href="<?= $baseUrl ?>assets/css/star-rating-svg.css">
        <script src="<?= $baseUrl ?>assets/js/jquery.star-rating-svg.js"></script>

        <!-- Theme Color -->
        <!-- <meta name="theme-color" content="#712cf9"> -->
    </head>

    <body class="d-flex flex-column min-vh-100">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
            <div class="container">
                <a class="navbar-brand fw-bold" href="<?= $baseUrl ?>index.php">Blog System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#navbarNav" aria-controls="navbarNav" 
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto align-items-center">
                        <!-- Search box -->
                        <li class="nav-item me-3">
                            <input type="text" id="search" class="form-control" 
                                placeholder="Search posts..." style="width: 220px;">
                        </li>

                        <li class="nav-item">
                            <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? ' active' : '' ?>" 
                                href="<?= $baseUrl ?>index.php">Home</a>
                        </li>
                        <?php if(empty($_SESSION['username'])): ?>
                            <li class="nav-item">
                                <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'login.php' ? ' active' : '' ?>" 
                                    href="<?= $baseUrl ?>auth/login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'register.php' ? ' active' : '' ?>" 
                                    href="<?= $baseUrl ?>auth/register.php">Register</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link<?= basename($_SERVER['PHP_SELF']) == 'create.php' ? ' active' : '' ?>" 
                                    href="<?= $baseUrl ?>posts/create.php">Create</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" 
                                data-bs-toggle="dropdown" aria-expanded="false">
                                    <?= htmlspecialchars($_SESSION['username']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="<?= $baseUrl ?>auth/logout.php">Logout</a></li>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="flex-grow-1 container mt-4">
