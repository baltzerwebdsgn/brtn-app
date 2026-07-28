<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BRIGHT'N</title>
    <link rel="stylesheet" href="includes/main.css">
    <link rel="stylesheet" href="includes/navBar.css">
</head>
<body>
<?php if (in_array($page, ['login','register', 'forgot-password'])): ?>
    <img 
    src="assets/brightn-logo.svg" 
    alt="flat icon side view of a lemon wedge with sparkles replacing the top two center lemon wedges"
    class="max-size-img"
    />
    <h1>BRIGHT'N</h1>
    <p id="tagline">Household chores, brightened.</p>
<?php endif; ?>