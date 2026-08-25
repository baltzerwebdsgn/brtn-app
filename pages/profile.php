<?php
$from = $_GET['from'] ?? 'settings';
$profileError = null;
$profileSuccess = null;

$userStmt = $pdo->prepare("SELECT id, username, name, email FROM users WHERE id = :id");
$userStmt->execute(['id' => $_SESSION['user_id']]);
$user = $userStmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();

    $currentPassword = $_POST['current_password'] ?? '';
    $newName = trim($_POST['name'] ?? '');
    $newUsername = trim($_POST['username'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    $passwordStmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = :id");
    $passwordStmt->execute(['id' => $_SESSION['user_id']]);
    $currentHash = $passwordStmt->fetchColumn();

    if (!password_verify($currentPassword, $currentHash)) {
        $profileError = 'Incorrect current password.';
    } elseif ($newName === '' || $newUsername === '') {
        $profileError = 'Name and username are required.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
    $profileError = 'New password must be at least 8 characters.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmNewPassword) {
        $profileError = 'New passwords do not match.';
    } else {
        $dupCheck = $pdo->prepare("SELECT id FROM users WHERE username = :username AND id != :id");
        $dupCheck->execute(['username' => $newUsername, 'id' => $_SESSION['user_id']]);

        if ($dupCheck->fetch()) {
            $profileError = 'That username is already taken.';
        } else {
            try {
                if ($newPassword !== '') {
                    $updateStmt = $pdo->prepare("UPDATE users SET name = :name, username = :username, password_hash = :password_hash WHERE id = :id");
                    $updateStmt->execute([
                        'name' => $newName,
                        'username' => $newUsername,
                        'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
                        'id' => $_SESSION['user_id'],
                    ]);
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET name = :name, username = :username WHERE id = :id");
                    $updateStmt->execute([
                        'name' => $newName,
                        'username' => $newUsername,
                        'id' => $_SESSION['user_id'],
                    ]);
                }

                $_SESSION['name'] = $newName;
                $user['name'] = $newName;
                $user['username'] = $newUsername;

                $profileSuccess = 'Profile updated.';
            } catch (PDOException $e) {
                $profileError = 'That username is already taken.';
            }
        }
    }
}
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>Profile</h1>
</div>

<div class="task-card profile-summary">
    <div class="profile-info">
        <span class="profile-icon active profile-summary-icon"><?= htmlspecialchars(strtoupper(substr($user['name'] ?? $user['username'], 0, 1))) ?></span>
        <div class="profile-summary-text">
            <h3 id="profile-name-display"><?= htmlspecialchars($user['name'] ?? $user['username']) ?></h3>
            <p class="text" id="profile-email-display"><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>
</div>

<form method="POST" action="index.php?page=profile" id="profile-form" class="task-card">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

    <h2 class="profile-subheadings" id="account-details">Account Details</h2>
    <div class="form-group">
        <label for="profile-name" class="subheading">Name</label>
        <input type="text" id="profile-name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" class="addTask track-changes" required>
    </div>
    <div class="form-group">
        <label for="profile-username" class="subheading">Username</label>
        <input type="text" id="profile-username" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="addTask track-changes" required>
    </div>

    <h2 class="profile-subheadings">Change Password</h2>
    <p class="text">Leave blank to keep your current password.</p>
    <div class="form-group">
        <label for="new-password" class="subheading">New Password</label>
        <input type="password" id="new-password" name="new_password" class="addTask track-changes">
    </div>
    <div class="form-group">
        <label for="confirm-new-password" class="subheading">Confirm New Password</label>
        <input type="password" id="confirm-new-password" name="confirm_new_password" class="addTask track-changes">
    </div>

    <h2 class="profile-subheadings">Confirm It's You</h2>
    <div class="confirm-password-card">
        <div id="password-message">
            <p class="text"><span class="material-symbols-outlined">lock</span></p>
            <p class="text password-message-text"> Enter your current password to save any changes on this page.</p>
        </div>
        <div class="form-group">
            <label for="current-password" class="subheading">Current Password</label>
            <input type="password" id="current-password" name="current_password" class="addTask" required>
        </div>
        <?php if ($profileError): ?>
            <p class="danger"><?= htmlspecialchars($profileError) ?></p>
        <?php endif; ?>
        <?php if ($profileSuccess): ?>
            <p class="success"><?= htmlspecialchars($profileSuccess) ?></p>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn-primary" disabled>Save changes</button>
</form>
