<?php
require_once 'includes/db.php';
include 'includes/header.php'; // Persistent top nav

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

switch ($page) {
    case 'breakdown':
        include 'pages/breakdown.php';
        break;
    case 'upcoming':
        include 'pages/upcoming.php';
        break;
    case 'settings':
        include 'pages/settings.php';
        break;
    default:
        include 'pages/home.php';
        break;
}

include 'includes/footer.php'; // Persistent bottom nav
// // This pulls your first 10 tasks
// $stmt = $pdo->query("SELECT * FROM task_library LIMIT 10");
// $tasks = $stmt->fetchAll();
?>

