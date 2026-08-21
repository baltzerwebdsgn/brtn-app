<?php /** @var boolean $isHead */ ?>
<?php
$zoneGridScope = $zoneGridScope ?? 'household'; // 'household' (Settings) or 'mine' (Home)
$isMineScope = $zoneGridScope === 'mine';

$zoneCardsStmt = $pdo->prepare("SELECT id, name, icon FROM zones WHERE household_id = :household_id ORDER BY name");
$zoneCardsStmt->execute(['household_id' => $_SESSION['household_id']]);
$zoneCards = $zoneCardsStmt->fetchAll();

if ($isMineScope) {
    $taskStmt = $pdo->prepare("
        SELECT
            household_tasks.id,
            COALESCE(household_tasks.custom_room, task_library.room) AS room,
            COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency
        FROM household_tasks
        LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
        WHERE household_tasks.household_id = :household_id
        AND household_tasks.is_active = 1
        AND household_tasks.assigned_to = :user_id
    ");
    $taskStmt->execute([
        'household_id' => $_SESSION['household_id'],
        'user_id' => $_SESSION['user_id'],
    ]);
    $myTasks = $taskStmt->fetchAll();

    $weekStart = (new DateTime('today'))->modify('-' . (int) (new DateTime('today'))->format('w') . ' days')->format('Y-m-d');
    $weekEnd = (new DateTime($weekStart))->modify('+6 days')->format('Y-m-d');

    $completedStmt = $pdo->prepare("SELECT DISTINCT task_id FROM task_history WHERE user_id = :user_id AND completed_at >= :week_start");
    $completedStmt->execute(['user_id' => $_SESSION['user_id'], 'week_start' => $weekStart]);
    $completedTaskIdsThisWeek = $completedStmt->fetchAll(PDO::FETCH_COLUMN);

    $zoneStats = [];
    foreach ($myTasks as $task) {
        $frequency = strtolower($task['frequency']);
        $isDueThisWeek = false;

        if ($frequency === 'daily' || $frequency === 'weekly') {
            $isDueThisWeek = true;
        } elseif ($frequency === 'monthly') {
            $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
            $nextDue = $status['next_due_date'] ?? null;
            if (($nextDue && $nextDue >= $weekStart && $nextDue <= $weekEnd) || in_array($task['id'], $completedTaskIdsThisWeek, true)) {
                $isDueThisWeek = true;
            }
        }

        if (!$isDueThisWeek) {
            continue;
        }

        $room = $task['room'];
        if (!isset($zoneStats[$room])) {
            $zoneStats[$room] = ['total' => 0, 'completed' => 0];
        }
        $zoneStats[$room]['total']++;
        if (in_array($task['id'], $completedTaskIdsThisWeek, true)) {
            $zoneStats[$room]['completed']++;
        }
    }

    $zoneCards = array_values(array_filter($zoneCards, function ($z) use ($zoneStats) {
        return !empty($zoneStats[$z['name']]);
    }));
} else {
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
    $taskStmt->execute(['household_id' => $_SESSION['household_id']]);
    $allTasks = $taskStmt->fetchAll();

    $zoneStats = [];
    foreach ($allTasks as $task) {
        $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
        $room = $task['room'];
        if (!isset($zoneStats[$room])) {
            $zoneStats[$room] = ['total' => 0, 'overdue' => 0, 'due' => 0];
        }
        $zoneStats[$room]['total']++;
        if (($status['status'] ?? '') === 'overdue') {
            $zoneStats[$room]['overdue']++;
        }
        if (($status['status'] ?? '') === 'due') {
            $zoneStats[$room]['due']++;
        }
    }
}
?>
<h2>Zones</h2>
<div class="zone-grid">
    <?php foreach ($zoneCards as $zoneCard): ?>
        <?php
        $stats = $zoneStats[$zoneCard['name']] ?? ($isMineScope ? ['total' => 0, 'completed' => 0] : ['total' => 0, 'overdue' => 0, 'due' => 0]);
        $total = $stats['total'];

        if ($isMineScope) {
            $completed = $stats['completed'];
            $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            if ($total === 0) {
                $countText = '0 Tasks';
            } elseif ($total === 1) {
                $countText = '1 Task';
            } else {
                $countText = $completed . '/' . $total . ' Tasks';
            }
        } else {
            $overdue = $stats['overdue'];
            $due = $stats['due'];
            $progress = $total > 0 ? round((($total - $overdue - $due) / $total) * 100) : 100;
            $countText = $total . ' Task' . ($total === 1 ? '' : 's');
        }

        $zoneLink = 'index.php?page=zone&id=' . $zoneCard['id'] . ($isMineScope ? '&from=home' : '');
        ?>
        <a href="<?= $zoneLink ?>" class="zone-card" data-zone-name="<?= htmlspecialchars($zoneCard['name']) ?>" data-zone-total="<?= $total ?>" data-zone-completed="<?= $isMineScope ? $completed : 0 ?>">
            <div class="zoneCard-text">
                <h3><?= htmlspecialchars($zoneCard['name']) ?></h3>
                <div class="zone-task-count"><?= htmlspecialchars($countText) ?></div>
            </div>
            <span class="zone-icon" style="mask-image: url('assets/images/zone-icons/<?= htmlspecialchars($zoneCard['icon']) ?>.svg'); -webkit-mask-image: url('assets/images/zone-icons/<?= htmlspecialchars($zoneCard['icon']) ?>.svg');"></span>
            <div class="zone-progress-bar"><div class="zone-progress-fill" style="width: <?= $progress ?>%;"></div></div>
        </a>
    <?php endforeach; ?>
    <?php if (!$isMineScope && $isHead): ?>
        <a href="index.php?page=zone&new=1&from=settings" class="zone-card zone-card-new">
            <span class="material-symbols-outlined">add_circle</span>
            <span>New zone</span>
        </a>
    <?php endif; ?>
</div>
