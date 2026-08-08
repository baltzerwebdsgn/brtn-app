<!-- The function of this page is to display all info to all 
housemates about the different tasks and who they are assigned -->
<?php
//Capture the filter from the URL (default to 'All')
$filter = $_GET['filter'] ?? 'All';
$zone_filter = $_GET['room'] ?? 'All';
$sort = $_GET['sort'] ?? 'name';
$cardActions = 'status';
$sortBaseUrl = "index.php?page=breakdown&filter=" . urlencode($filter) . "&room=" . urlencode($zone_filter);

// Build the SQL Query dynamically
$query = "
    SELECT
        household_tasks.id,
        COALESCE(household_tasks.custom_name, task_library.name) AS name,
        COALESCE(household_tasks.custom_room, task_library.room) AS room,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
        COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.household_id = :household_id
    AND household_tasks.is_active = 1
";
$params = ['household_id' => $_SESSION['household_id']];

if ($filter !== 'All') {
    $query .= " AND LOWER(COALESCE(household_tasks.custom_frequency, task_library.frequency)) = LOWER(:freq)";
    $params['freq'] = $filter;
}

if ($zone_filter !== 'All') {
    $query .= " AND COALESCE(household_tasks.custom_room, task_library.room) = :room";
    $params['room'] = $zone_filter;
}

$query = applySortOrder($query, $sort);

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
$zoneStmt = $pdo->prepare("SELECT * FROM zones WHERE household_id = :household_id ORDER BY name");
$zoneStmt->execute(['household_id' => $_SESSION['household_id']]);
$zones = $zoneStmt->fetchAll();
?>

<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Frequency
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <a href="index.php?page=breakdown&filter=All&room=<?= urlencode($zone_filter) ?>&sort=<?= urlencode($sort) ?>" class="chip <?= $filter == 'All' ? 'chip-active' : '' ?>">All</a>
            <a href="index.php?page=breakdown&filter=Daily&room=<?= urlencode($zone_filter) ?>&sort=<?= urlencode($sort) ?>" class="chip <?= $filter == 'Daily' ? 'chip-active' : '' ?>">Daily</a>
            <a href="index.php?page=breakdown&filter=Weekly&room=<?= urlencode($zone_filter) ?>&sort=<?= urlencode($sort) ?>" class="chip <?= $filter == 'Weekly' ? 'chip-active' : '' ?>">Weekly</a>
            <a href="index.php?page=breakdown&filter=Monthly&room=<?= urlencode($zone_filter) ?>&sort=<?= urlencode($sort) ?>" class="chip <?= $filter == 'Monthly' ? 'chip-active' : '' ?>">Monthly</a>


        </div>
    </details>
</div>
<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Zones
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <a href="index.php?page=breakdown&filter=<?= urlencode($filter) ?>&room=All" class="chip <?= $zone_filter == 'All' ? 'chip-active' : '' ?>">All</a>
            <?php foreach ($zones as $zone): ?>
                <a href="index.php?page=breakdown&filter=<?= urlencode($filter) ?>&room=<?= urlencode($zone['name']) ?>" class="chip <?= $zone_filter == $zone['name'] ? 'chip-active' : '' ?>"><?= htmlspecialchars($zone['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </details>
</div>
<?php include 'includes/sort-chips.php'; ?>
<div class="hide-done-btn">
    <button class="chip">Hide Done</button>
</div>

<div class="task-list">
    <?php foreach ($tasks as $task): ?>
    <?php include 'includes/task-card.php'; ?>
<?php endforeach; ?>
</div>