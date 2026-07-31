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
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Frequency
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <a href="index.php?page=breakdown&filter=All" class="chip <?= $filter == 'All' ? 'chip-active' : '' ?>">All</a>
            <a href="index.php?page=breakdown&filter=Daily" class="chip <?= $filter == 'Daily' ? 'chip-active' : '' ?>">Daily</a>
            <a href="index.php?page=breakdown&filter=Weekly" class="chip <?= $filter == 'Weekly' ? 'chip-active' : '' ?>">Weekly</a>
            <a href="index.php?page=breakdown&filter=Monthly" class="chip <?= $filter == 'Monthly' ? 'chip-active' : '' ?>">Monthly</a>
        </div>
    </details>
</div>
<div class="hide-done-btn">
    <button class="chip">Hide Done</button>
</div>

<div class="task-list">
    <?php foreach ($tasks as $task): ?>
        <div class="task-card">
            <div class="task-info">
                <strong><?= htmlspecialchars($task['name']) ?></strong>
                <a>
                    <span class="material-symbols-outlined info-icon">
                        info
                    </span>
                </a>
            </div>
            <div class="task-meta">
                <span><?= htmlspecialchars($task['room']) ?></span>
                    &middot;
                <?= htmlspecialchars($task['total_time']) ?>m
            </div>
        </div>
    <?php endforeach; ?>
</div>