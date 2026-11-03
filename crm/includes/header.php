<?php
require_once __DIR__ . '/../config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'DreamBrixs CRM' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/crm.css">
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <h5 class="mb-0 fw-bold">DreamBrixs</h5>
            <small>Projects CRM</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':'' ?>" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link <?= basename($_SERVER['PHP_SELF'])=='projects.php'?'active':'' ?>" href="projects.php"><i class="fas fa-building me-2"></i> Projects</a></li>
            <li class="nav-item"><a class="nav-link" href="project-form.php"><i class="fas fa-plus me-2"></i> Add Project</a></li>
            <li class="nav-item mt-auto"><a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
        <div class="sidebar-footer">
            <small>Logged in as <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></small>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content flex-grow-1">
        <header class="topbar">
            <h4 class="mb-0"><?= $pageTitle ?? 'Dashboard' ?></h4>
        </header>
        <div class="content-area">
