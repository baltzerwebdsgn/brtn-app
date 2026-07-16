<?php
// 1. Capture the filter from the URL (default to 'All')
$filter = $_GET['filter'] ?? 'All';
$room_filter = $_GET['room'] ?? 'All';

// 2. Build the SQL Query dynamically
$query = "SELECT * FROM task_library WHERE 1=1";
$params = [];

if ($filter !== 'All') {
    $query .= " AND frequency = :freq";
    $params['freq'] = $filter;
}

if ($room_filter !== 'All') {
    $query .= " AND room = :room";
    $params['room'] = $room_filter;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>

<div class="filter-section">
    <h2>Task Breakdown</h2>
    
    <div class="filter-group">
        <a href="index.php?page=breakdown&filter=All" class="chip <?= $filter == 'All' ? 'active' : '' ?>">All</a>
        <a href="index.php?page=breakdown&filter=Daily" class="chip <?= $filter == 'Daily' ? 'active' : '' ?>">Daily</a>
        <a href="index.php?page=breakdown&filter=Weekly" class="chip <?= $filter == 'Weekly' ? 'active' : '' ?>">Weekly</a>
        <a href="index.php?page=breakdown&filter=Monthly" class="chip <?= $filter == 'Monthly' ? 'active' : '' ?>">Monthly</a>
    </div>
</div>

<div class="task-list">
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">
            <div class="task-info">
                <strong><?= htmlspecialchars($task['name']) ?></strong>
                <span><?= htmlspecialchars($task['room']) ?></span>
            </div>
            <div class="task-meta">
                <?= htmlspecialchars($task['total_time']) ?>m
            </div>
        </div>
    <?php endforeach; ?>
</div>