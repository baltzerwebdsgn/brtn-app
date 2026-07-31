<?php
$from = $_GET['from'] ?? 'settings';
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>House Metrics</h1>
</div>
<h2>House Cleaning Score</h2>
<div class="task-card">
    <div class="circular-progress" style="--progress: 100;">
        <div class="inner-circle">
            <span class="progress-percentage">100%</span>
            <span class="text">Last 30 Days</span>
        </div>
    </div>
</div>
<h2>Task Distribution</h2>
<div class="task-card">
</div>
<h2>Task Progress</h2>
<div class="task-card">
</div>