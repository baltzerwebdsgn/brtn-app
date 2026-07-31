<?php
// Logic specific to the Home page
$stmt = $pdo->query("SELECT * FROM task_library LIMIT 10");
$tasks = $stmt->fetchAll();
?>



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
<h2> Cleaning Score </h2>
<div class="task-card">
    <div class="circular-progress" style="--progress: 100;">
        <div class="inner-circle">
            <span class="progress-percentage">100%</span>
            <span class="text">Last 30 Days</span>
        </div>
    </div>
</div>