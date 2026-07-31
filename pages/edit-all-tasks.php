<?php
$from = $_GET['from'] ?? 'settings';
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>Edit All Tasks</h1>
</div>
<div class="task-card">
</div>
<button type="submit" class="btn-primary active" id="add-btn">+ Add Task</button>