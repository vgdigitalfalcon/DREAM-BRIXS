<?php
require_once 'config.php';
requireLogin();

$id = $_GET['id'] ?? null;
if ($id) {
    // Delete images
    $imgs = $pdo->prepare("SELECT image_path FROM project_images WHERE project_id = ?");
    $imgs->execute([$id]);
    foreach ($imgs->fetchAll() as $img) {
        @unlink(UPLOAD_DIR . $img['image_path']);
    }
    $pdo->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
}
header('Location: projects.php');
exit;
