<?php /** @var string $page */ ?>
<?php
$error = null;
// Allow the head of household to change the name of the household
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['household-name']) && $_SESSION['role'] === 'head') {
    $newHouseholdName = trim($_POST['household-name']);

    $stmt = $pdo->prepare("UPDATE households SET household_name = :household_name WHERE id = :household_id");
    $stmt->execute([
        'household_name' => $newHouseholdName,
        'household_id' => $_SESSION['household_id'],
    ]);

    $_SESSION['household_name'] = $newHouseholdName;
    header('Location: index.php?page=settings');
    exit;
}
// Add a roommate logic and make sure it is only done by the head of household
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name-roommate']) && $_SESSION['role'] === 'head') {
    $roommateName = ucfirst(strtolower(trim($_POST['name-roommate'])));
    $roommateEmail = $_POST['email-roommate'];

    $baseUsername = strtolower(preg_replace('/[^a-z0-9]/i', '', $roommateName));
    $generatedUsername = $baseUsername.random_int(100, 999);

    $tempPassword = bin2hex(random_bytes(5));
    $tempPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users(username, name, email, password_hash, role, household_id) VALUES (:username, :name, :email, :password_hash, 'roommate', :household_id)");
        $stmt->execute([
            'username' => $generatedUsername,
            'name' => $roommateName,
            'email' => $roommateEmail,
            'password_hash' => $tempPasswordHash,
            'household_id' => $_SESSION['household_id'],
        ]);

        $_SESSION['flash_temp_username'] = $generatedUsername;
        $_SESSION['flash_temp_password'] = $tempPassword;
        header('Location: index.php?page=settings');
        exit;
    } catch (PDOException $e) {
        $error = 'That email is already taken.';
    }
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE household_id = :household_id");
$stmt->execute(['household_id' => $_SESSION['household_id']]);
$housemates = $stmt->fetchAll();

// Delete the roommate and make sure its only being done by the head of household
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_roommate_id']) && $_SESSION['role'] === 'head') {
    $deleteId = (int) $_POST['delete_roommate_id'];

    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id AND household_id = :household_id AND role = 'roommate'");
    $stmt->execute([
        'id' => $deleteId,
        'household_id' => $_SESSION['household_id'],
    ]);

    header('Location: index.php?page=settings');
    exit;
}

?>

<h2>Account</h2>
<div class="task-card account-menu">
    <div>
       <form action="index.php?page=logout" method="POST" id="logout-btn">
            <button type="submit"> <span class="account-icon material-symbols-outlined">logout</span><h3 class="account-text">Log out</h3></button>
        </form>
    </div>
    <div class="account-border">
        <a href="index.php?page=profile&from=settings" class="account-menu-row <?= ($page == 'profile') ? 'active' : '' ?>">
            <span class="account-title">
                <span class="account-icon material-symbols-outlined">person</span>
                <h3 class="account-text">Profile</h3>
            </span>
            <span class="account-text">></span>
        </a>
    </div>
    <div class="account-border">
        <a href="index.php?page=notifications&from=settings" class="account-menu-row <?= ($page == 'notifications') ? 'active' : '' ?>">
            <span class="account-title">
                <span class="account-icon material-symbols-outlined">notifications</span>
                <h3 class="account-text">Notifications</h3>
            </span>
            <span class="account-text">></span>
        </a>
    </div>
    <div class="account-border">
        <div class="account-menu-row">
            <span class="account-title">
                <span class="account-icon material-symbols-outlined">donut_large</span>
                <h3 class="account-text">Score & Streak</h3>
            </span>
            <label class="switch">
                <input type="checkbox">
                <span class="slider round"></span>
            </label>
        </div>
    </div>
    <div class="account-border">
        <a href="index.php?page=edit-all-tasks&from=settings" class="account-menu-row <?= ($page == 'edit-all-tasks') ? 'active' : '' ?>">
            <span class="account-title">    
                <span class="account-icon material-symbols-outlined">edit_note</span>
                <h3 class="account-text">Edit All Tasks</h3>
            </span>
            <span class="account-text">></span>
        </a>
    </div>
    <div class="account-border">
        <a href="index.php?page=house-metrics&from=settings" class="account-menu-row <?= ($page == 'house-metrics') ? 'active' : '' ?>">
            <span class="account-title">    
                <span class="account-icon material-symbols-outlined">monitoring</span>
                <h3 class="account-text">House Metrics</h3>
            </span>
            <span class="account-text">></span>
        </a>
    </div>
    <div class="account-border">
        <a href="index.php?page=vacation-mode&from=settings" class="account-menu-row <?= ($page == 'vacation-mode') ? 'active' : '' ?>">
            <span class="account-title">    
                <span class="account-icon material-symbols-outlined">luggage</span>
                <h3 class="account-text">Vacation Mode</h3>
            </span>
            <span class="account-text">></span>
        </a>
    </div>
</div>
<h2>Household</h2>
<div class="task-card">
    <?php if ($_SESSION['role'] === 'head'): ?>
        <form action="index.php?page=settings" method="POST">
            <div class="form-group">    
                <label for="hn_settings" class="subheading">Household Name</label>
                <input
                    type="text"
                    name="household-name"
                    id="hn_settings"
                    value="<?= htmlspecialchars($_SESSION['household_name']) ?>"
                    class="settings"
                    required>
            </div>    
            <button type="submit" class="btn-primary">Save</button>
        </form>
    <?php else: ?>
        <label class="subheading">Household Name</label>
        <p><?= htmlspecialchars($_SESSION['household_name']) ?></p>
    <?php endif; ?>
</div>

<h2>Housemates</h2>
<?php foreach ($housemates as $housemate): ?>
    <div class="task-card">
        <div class="housemate-content">
            <div class="housemate-info">
                <span class="profile-icon-sm <?= $housemate['id'] === $_SESSION['user_id'] ? 'active' : 'inactive' ?>">
                    <?= strtoupper(substr($housemate['name'] ?? $housemate['username'], 0, 1)) ?>
                </span>
                <h3><?= htmlspecialchars($housemate['name'] ?? $housemate['username']) ?></h3>
                <span class="subheading role"><?= htmlspecialchars(ucfirst(strtolower(trim($housemate['role'])))) ?></span>
            </div>
            <div class="housemate-options">
                <span class="housemate-icon material-symbols-outlined">
                    pause_circle
                </span>
                <?php if ($housemate['role'] === 'roommate'): ?>
                    <span class="housemate-icon material-symbols-outlined">
                        assignment_ind
                    </span>
                <?php endif; ?>
                <?php if ($_SESSION['role'] === 'head' && $housemate['role'] === 'roommate'): ?>
                    <form action="index.php?page=settings" method="POST" class="inline-icon-form" onsubmit="return confirm('Remove this roommate?\nThis cannot be undone.');">
                        <input type="hidden" name="delete_roommate_id" value="<?= htmlspecialchars($housemate['id']) ?>">
                        <button type="submit" class="danger btn-icon">
                            <span class="material-symbols-outlined">
                                person_remove
                            </span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endforeach; ?>
<!-- Add Roommate Section for Head of Household User -->
<?php if ($_SESSION['role'] === 'head'): ?>
    <h2>Add Roommate</h2>
    <div class="task-card">
        <form action="" method="POST" class="add-roommate-form">
                <div class="form-group" >
                    <label for="name-roommate" class="subheading">Name</label>
                    <input
                        type="text"
                        id="name-roommate"
                        name="name-roommate"
                        placeholder="Enter roommate's name"
                        required
                        class="addRoommate"
                    >
                </div>
                <div class="form-group">
                    <label for="email-roommate" class="subheading">Email</label>
                    <input 
                        type="text"
                        id="email-roommate"
                        name="email-roommate"
                        placeholder="e.g. roommate@brightnhouse.com"
                        required
                        class="addRoommate"
                    >
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-primary">
                        <span class="addRoommate-btn-text">
                            <span class="btn-icon material-symbols-outlined">
                                person_add
                            </span>
                            Add Roommate
                        </span>
                        </button>
                </div>
                <?php if ($error): ?>
                    <p class="danger"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
        </form>
        <?php if (isset($_SESSION['flash_temp_password'])): ?>
            <h3 class="success">Success!</h3>
            <p class="success">Roommate <strong><?= htmlspecialchars($_SESSION['flash_temp_username']) ?></strong> added.</p>
            <p class="success">Temporary password: <strong><?= htmlspecialchars($_SESSION['flash_temp_password']) ?></strong></p>
        <?php
            unset($_SESSION['flash_temp_username']);
            unset($_SESSION['flash_temp_password']);
        ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
