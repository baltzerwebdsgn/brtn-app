<?php 
require_once __DIR__ . '/../includes/auth.php';
requireCsrf();
session_destroy();
$_SESSION = [];
header('Location: index.php?page=login');
exit;
?>