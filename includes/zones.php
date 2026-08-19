<?php /** @var boolean $isHead */ ?>
<?php 

$query = "
SELECT
    zones.id,
    zones.name,
    zones.icon,
    COUNT(t.id) AS task_count
FROM zones
LEFT JOIN (
    SELECT
        household_tasks.id,
        household_tasks.household_id,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_room, task_library.room) AS room
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.is_active = 1
) AS t ON t.household_id = zones.household_id AND t.room = zones.name
WHERE zones.household_id = :household_id
GROUP BY zones.id, zones.name, zones.icon
ORDER BY zones.name
";
$taskStmt = $pdo->prepare("
    SELECT
        household_tasks.id,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_room, task_library.room) AS room
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.household_id = :household_id
    AND household_tasks.is_active = 1
");

$stmt = $pdo->prepare($query);
$stmt->execute(['household_id' => $_SESSION['household_id']]);
$zoneCards = $stmt->fetchAll();

$taskStmt->execute(['household_id' => $_SESSION['household_id']]);
$allTasks = $taskStmt->fetchAll();

$zoneStats = [];
foreach ($allTasks as $task) {
    $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
    $room = $task['room'];
    if (!isset($zoneStats[$room])) {
        $zoneStats[$room] = ['total' => 0, 'overdue' => 0];
    }
    $zoneStats[$room]['total']++;
    if (($status['status'] ?? '') === 'overdue') {
        $zoneStats[$room]['overdue']++;
    }
}
?>
<h2 id="zones-section">Zones</h2>
<div class="zone-grid">
    <?php foreach ($zoneCards as $zoneCard): ?>
        <?php
        $stats = $zoneStats[$zoneCard['name']] ?? ['total' => 0, 'overdue' => 0];
        $progress = $stats['total'] > 0 ? round((($stats['total'] - $stats['overdue']) / $stats['total']) * 100) : 100;
        ?>
        <a href="index.php?page=zone&id=<?= $zoneCard['id'] ?>&from=settings" class="zone-card">
            <div class="zoneCard-text">
                <h3><?= htmlspecialchars($zoneCard['name']) ?></h3>
                <div class="zone-task-count"><?= $stats['total'] ?> tasks</div>
            </div>
            <span class="zone-icon" style="mask-image: url('assets/images/zone-icons/<?= htmlspecialchars($zoneCard['icon']) ?>.svg'); -webkit-mask-image: url('assets/images/zone-icons/<?= htmlspecialchars($zoneCard['icon']) ?>.svg');"></span>
            <div class="zone-progress-bar"><div class="zone-progress-fill" style="width: <?= $progress ?>%;"></div></div>
        </a>
    <?php endforeach; ?>
    <?php if ($isHead): ?>
        <a href="index.php?page=zone&new=1" class="zone-card zone-card-new">
            <span class="material-symbols-outlined">add_circle</span>
            <span id="new-zone-text">New zone</span>
        </a>
    <?php endif; ?>
</div>
