<?php
ob_start();
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
$page = isset($_GET['page']) ? $_GET['page'] : 'login';
include 'includes/header.php'; // Persistent top nav
$publicPages = ['login', 'register','terms'];
if (!in_array($page, $publicPages)) {
    requireLogin();
}
switch ($page) {
    case 'home':
        include 'pages/home.php';
        break;
    case 'breakdown':
        include 'pages/breakdown.php';
        break;
    case 'upcoming':
        include 'pages/upcoming.php';
        break;
    case 'settings':
        include 'pages/settings.php';
        break;
    case 'profile':
        include 'pages/profile.php';
        break;
    case 'notifications':
        include 'pages/notifications.php';
        break;
    case 'edit-all-tasks':
        include 'pages/edit-all-tasks.php';
        break;
    case 'house-metrics':
        include 'pages/house-metrics.php';
        break;
    case 'vacation-mode':
        include 'pages/vacation-mode.php';
        break;
    case 'zones':
        include 'pages/zones.php';
        break;
    case 'register':
        include 'pages/register.php';
        break;
    case 'logout':
        include 'pages/logout.php';
        break;
    case 'terms':
        include 'pages/terms.php';
        break;
    default:
        include 'pages/login.php';
        break;
}

include 'includes/footer.php'; // Persistent bottom nav
?>

