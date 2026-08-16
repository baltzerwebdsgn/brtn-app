<?php
$page = 'upcoming';
$filter = $_GET['filter'] ?? 'All';
$zone_filter = $_GET['room'] ?? 'All';
$sort = $_GET['sort'] ?? 'date';
$showDateSort = true;
$hideDone = isset($_GET['hide_done']);
$showHideDone = false;
$assignee_filter = $_GET['assignee'] ?? 'All';
$cardActions = 'upcoming';

$currentParams = [
    'filter' => $filter,
    'room' => $zone_filter,
    'sort' => $sort,
    'hideDone' => $hideDone,
    'assignee' => $assignee_filter,
];

$query = "
    SELECT
        household_tasks.id,
        household_tasks.assigned_to,
        COALESCE(household_tasks.custom_name, task_library.name) AS name,
        COALESCE(household_tasks.custom_room, task_library.room) AS room,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
        COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month,
        COALESCE(assigned_user.name, assigned_user.username) AS assignee_name
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    LEFT JOIN users AS assigned_user ON household_tasks.assigned_to = assigned_user.id
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
if ($assignee_filter !== 'All') {
    $query .= " AND household_tasks.assigned_to = :assignee_id";
    $params['assignee_id'] = (int) $assignee_filter;
}

$query = applySortOrder($query, $sort);

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

foreach ($tasks as &$task) {
    $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
    $task['last_completed'] = $status['last_completed'] ?? null;
    $task['completed_by_id'] = $status['completed_by_id'] ?? null;
    $task['completed_by_name'] = $status['completed_by_name'] ?? null;
    $task['status'] = $status['status'] ?? 'due';
    $task['next_due_date'] = $status['next_due_date'] ?? null;

}
unset($task);

if ($hideDone) {
    $tasks = array_values(array_filter($tasks, function ($t) { return $t['status'] !== 'idle'; }));
}
if ($sort === 'date') {
    usort($tasks, function ($a, $b) {
        return strcmp($a['next_due_date'] ?? '9999-12-31', $b['next_due_date'] ?? '9999-12-31');
    });
}

$zoneStmt = $pdo->prepare("SELECT * FROM zones WHERE household_id = :household_id ORDER BY name");
$zoneStmt->execute(['household_id' => $_SESSION['household_id']]);
$zones = $zoneStmt->fetchAll();

$stmt = $pdo->prepare("
    SELECT * FROM users
    WHERE household_id = :household_id
    ORDER BY (id = :user_id) DESC, name ASC
");
$stmt->execute([
    'household_id' => $_SESSION['household_id'],
    'user_id' => $_SESSION['user_id'],
]);
$housemates = $stmt->fetchAll();
?>
<?php include 'includes/task-filters.php'; ?>
<?php include 'includes/sort-chips.php'; ?>
<div class="task-list">
    <?php foreach ($tasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
</div>
