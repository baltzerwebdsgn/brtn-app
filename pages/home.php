?php
// Logic specific to the Home page
$stmt = $pdo->query("SELECT * FROM task_library LIMIT 10");
$tasks = $stmt->fetchAll();
?>

<h1>Today's Overview</h1>

<?php if (empty($tasks)): ?>
    <p>No tasks found.</p>
<?php else: ?>
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">
            <strong><?= htmlspecialchars($task['name']) ?></strong><br>
            <small><?= htmlspecialchars($task['room']) ?> — <?= htmlspecialchars($task['total_time'] ?? '0') ?> mins</small>
        </div>
    <?php endforeach; ?>
<?php endif; ?>