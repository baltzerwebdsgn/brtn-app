<?php
$from = $_GET['from'] ?? 'login';
?>
<a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr; Back</a>
<div class="task-card">
    <h1>Terms & Privacy</h1>
    <p><em>Last updated: July 28, 2026</em></p>

    <h2>Terms of Use</h2>
    <p>BRIGHT'N is a household task-tracking tool. By creating an account you agree to:</p>
    <ul>
        <li>Provide accurate information when registering (username, email, name).</li>
        <li>Use BRIGHT'N only to manage tasks for households you're a genuine member of.</li>
        <li>Keep your password secure — you're responsible for activity under your account.</li>
        <li>Not attempt to access another household's data without an invite.</li>
    </ul>
    <p>BRIGHT'N is provided as-is, without warranty. It's a personal project, not a commercial service, and may change or be unavailable at times.</p>

    <h2>Privacy Policy</h2>
    <p>BRIGHT'N collects the minimum data needed to run the app:</p>
    <ul>
        <li><strong>Account info:</strong> username, email, name, and a securely hashed password (we never store your plain-text password).</li>
        <li><strong>Household info:</strong> household name and invite code, and which household you belong to.</li>
        <li><strong>Task activity:</strong> which tasks are assigned to you and a history of tasks you've completed, so your household can track progress.</li>
    </ul>
    <p>We do not sell, share, or use your data for advertising. Your data is only visible to members of your own household. We do not use third-party trackers or analytics.</p>
    <p>You can request deletion of your account and household data at any time by contacting the household head or the app maintainer.</p>
</div>