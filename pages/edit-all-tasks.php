<?php
$from = $_GET['from'] ?? 'settings';
$sort = $_GET['sort'] ?? 'name';
$showDateSort = false;
$cardActions = 'edit';
$flashEditedTaskId = $_SESSION['flash_edited_task_id'] ?? null;
unset($_SESSION['flash_edited_task_id']);
$page = 'edit-all-tasks';
$filter = $_GET['filter'] ?? 'All';
$zone_filter = $_GET['room'] ?? 'All';
$assignee_filter = $_GET['assignee'] ?? 'All';
$showHideDone = false;

$currentParams = [
    'from' => $from,
    'filter' => $filter,
    'room' => $zone_filter,
    'sort' => $sort,
    'assignee' => $assignee_filter,
];


$query = "
    SELECT
        household_tasks.id,
        COALESCE(household_tasks.custom_name, task_library.name) AS name,
        COALESCE(household_tasks.custom_room, task_library.room) AS room,
        COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
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


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id']) && $_SESSION['role'] === 'head') {
    requireCsrf();
    $deleteTaskId = (int) $_POST['delete_task_id'];

    $stmt = $pdo->prepare("UPDATE household_tasks SET is_active = 0 WHERE id = :id AND household_id = :household_id");
    $stmt->execute([
        'id' => $deleteTaskId,
        'household_id' => $_SESSION['household_id'],
    ]);

    header('Location: index.php?page=edit-all-tasks');
    exit;
}
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>Edit All Tasks</h1>
</div>
<?php include 'includes/task-filters.php'; ?>
<?php include 'includes/sort-chips.php'; ?>
<div class="task-list">
    <?php foreach ($tasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
</div>
<div class="task-card">
    <a href="index.php?page=settings&open=add-task" class="btn-primary active" id="add-btn">+ Add Task</a>
</div>

