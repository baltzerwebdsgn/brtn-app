<?php
$from = $_GET['from'] ?? 'settings';
$sort = $_GET['sort'] ?? 'name';
$cardActions = 'edit';
$sortBaseUrl = "index.php?page=edit-all-tasks&from=" . urlencode($from);

$query = "
    SELECT
        household_tasks.id,
        COALESCE(household_tasks.custom_name, task_library.name) AS name,
        COALESCE(household_tasks.custom_room, task_library.room) AS room,
        COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.household_id = :household_id
    AND household_tasks.is_active = 1
";

$query = applySortOrder($query, $sort);

$stmt = $pdo->prepare($query);
$stmt->execute(['household_id' => $_SESSION['household_id']]);
$tasks = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task_id']) && $_SESSION['role'] === 'head') {
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
<?php include 'includes/sort-chips.php'; ?>
<div class="task-list">
    <?php foreach ($tasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
</div>
    <button type="submit" class="btn-primary active" id="add-btn">+ Add Task</button>


