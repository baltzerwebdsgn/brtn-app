<?php /** @var string $page */ ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
    <title>BRIGHT'N</title>
    <link rel="stylesheet" href="includes/css/variables.css">
    <link rel="stylesheet" href="includes/css/base.css">
    <link rel="stylesheet" href="includes/css/buttons.css">
    <link rel="stylesheet" href="includes/css/forms.css">
    <link rel="stylesheet" href="includes/css/status-indicators.css">
    <link rel="stylesheet" href="includes/css/task-card.css">
    <link rel="stylesheet" href="includes/css/lists.css">
    <link rel="stylesheet" href="includes/css/progress-ring.css">
    <link rel="stylesheet" href="includes/css/home.css">
    <link rel="stylesheet" href="includes/css/navigation.css">
    <link rel="stylesheet" href="includes/css/modal.css">
</head>
<body>
<?php if (in_array($page, ['login','register', 'forgot-password'])): ?>
    <div class="brand-wordmark-wrap">
        <h1 class="brand-wordmark">
            BR
            <span class = "logo-slot">
                <img 
                src="assets/images/brightn-logo.svg" 
                alt="flat icon side view of a lemon wedge with sparkles replacing the top two center lemon wedges"
                />
            I
            </span>
            GHT'N
        </h1>
        <p id="tagline">Household chores, brightened.</p>
    </div>
    
<?php endif; ?>
<?php if (in_array($page, ['home','breakdown','upcoming','settings'])): ?>
    <div class="home-title">
    <div class="home-title-text">
        <?php if(in_array($page,['home'])): ?>
            <h1>Hi, <?= htmlspecialchars($_SESSION['name']) ?></h1>
            <h2 class="home-hn">of <?= htmlspecialchars($_SESSION['household_name']) ?></h2>
        <?php endif; ?>
        <?php if(in_array($page,['breakdown'])): ?>
            <h1>Task Breakdown</h1>
        <?php endif; ?>
        <?php if(in_array($page,['upcoming'])): ?>
            <h1>Upcoming</h1>
        <?php endif; ?>
        <?php if(in_array($page,['settings'])): ?>
            <h1>Settings</h1>
        <?php endif; ?>
    </div>
    <span class="profile-icon active"><?= strtoupper(substr($_SESSION['name'], 0, 1)) ?></span>
</div>
<?php endif; ?>