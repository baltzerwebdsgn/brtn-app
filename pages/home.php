<?php
$cardActions = 'status';
$allowUndo = true;
$showAssignee = false;
$zoneGridScope = 'mine'; 
$page = 'home';
$sort = $_GET['sort'] ?? 'status';
$showStatusSort = true;
$currentParams = ['sort' => $sort];

$query = "
    SELECT
        household_tasks.id,
        household_tasks.assigned_to,
        COALESCE(household_tasks.custom_name, task_library.name) AS name,
        COALESCE(household_tasks.custom_room, task_library.room) AS room,
        COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month,
        COALESCE(assigned_user.name, assigned_user.username) AS assignee_name,
        COALESCE(household_tasks.custom_instructions, task_library.instructions) AS instructions
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    LEFT JOIN users AS assigned_user ON household_tasks.assigned_to = assigned_user.id
    WHERE household_tasks.household_id = :household_id
    AND household_tasks.is_active = 1
    AND (
        household_tasks.assigned_to = :user_id
        OR household_tasks.id IN (SELECT DISTINCT task_id FROM task_history WHERE user_id = :user_id2)
    )
";
$query = applySortOrder($query, $sort);
$stmt = $pdo->prepare($query);
$stmt->execute([
    'household_id' => $_SESSION['household_id'],
    'user_id' => $_SESSION['user_id'],
    'user_id2' => $_SESSION['user_id'],
]);
$candidateTasks = $stmt->fetchAll();

$dueTasks = [];
$completedTasks = [];
$today = date('Y-m-d');

foreach ($candidateTasks as $task) {
    $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
    $task['last_completed'] = $status['last_completed'] ?? null;
    $task['completed_by_id'] = $status['completed_by_id'] ?? null;
    $task['completed_by_name'] = $status['completed_by_name'] ?? null;
    $task['status'] = $status['status'] ?? 'due';

    $hasBeenCompleted = !empty($task['last_completed']);
    $isDone = $task['status'] === 'idle' && $hasBeenCompleted;
    $isActionable = in_array($task['status'], ['due', 'overdue'], true);

    $isMine = (int) $task['assigned_to'] === (int) $_SESSION['user_id'];
    $completedByMe = (int) ($task['completed_by_id'] ?? 0) === (int) $_SESSION['user_id'];

    if ($isDone) {
        if (($isMine || $completedByMe) && substr($task['last_completed'], 0, 10) === $today) {
            $completedTasks[] = $task;
        }
    } elseif ($isMine && $isActionable) {
        $dueTasks[] = $task;
    }
}
if ($sort === 'status') {
    $statusRank = ['overdue' => 1, 'due' => 2, 'soon' => 3, 'done' => 4];
    $byStatusZoneTitle = function ($a, $b) use ($statusRank) {
        $rankA = $statusRank[getDisplayStatus($a['status'], wasCompletedToday($a['last_completed']))] ?? 5;
        $rankB = $statusRank[getDisplayStatus($b['status'], wasCompletedToday($b['last_completed']))] ?? 5;
        if ($rankA !== $rankB) {
            return $rankA <=> $rankB;
        }
        $roomCmp = strcasecmp($a['room'], $b['room']);
        return $roomCmp !== 0 ? $roomCmp : strcasecmp($a['name'], $b['name']);
    };
    usort($dueTasks, $byStatusZoneTitle);
    usort($completedTasks, $byStatusZoneTitle);
}

?>

<h2>To Do</h2>
<?php include 'includes/sort-chips.php'; ?>
<div id="todo-tasks-list">
    <?php foreach ($dueTasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
</div>
<div id="todo-empty-state" class="task-card empty-state" style="<?= empty($dueTasks) ? '' : 'display:none;' ?>">
    <span class="material-symbols-outlined empty-state-icon">celebration</span>
    <h3>All done for today!</h3>
    <p>Nice work — check back in tomorrow for your next tasks.</p>
</div>
<!-- Displays the Users Previous 30 Day Consistency Score -->
<!-- <h2> Cleaning Score </h2>
<div class="task-card">
    <div class="circular-progress" style="--progress: 100;">
        <div class="inner-circle">
            <span class="progress-percentage">100%</span>
            <span class="text">Last 30 Days</span>
        </div>
    </div>
</div> -->
<h2>Completed Tasks</h2>
<div id="completed-tasks-list">
    <?php foreach ($completedTasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
</div>
<?php include 'includes/zones.php'; ?>
