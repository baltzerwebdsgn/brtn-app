<?php
$from = $_GET['from'] ?? 'settings';
$sort = $_GET['sort'] ?? 'title';
$showDateSort = false;
$filter = $_GET['filter'] ?? 'All';
$page = 'assign-all-tasks';
$zone_filter = $_GET['room'] ?? 'All';
$showAssigneeFilter = false;
$showStatusFilter = false;

$currentParams = [
    'from' => $from,
    'filter' => $filter,
    'room' => $zone_filter,
    'sort' => $sort,
];

// Reassign a task, and make sure only the head can do it
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assignment']) && $_SESSION['role'] === 'head') {
    requireCsrf();

    $validUsersStmt = $pdo->prepare("SELECT id FROM users WHERE household_id = :household_id");
    $validUsersStmt->execute(['household_id' => $_SESSION['household_id']]);
    $validUserIds = $validUsersStmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("UPDATE household_tasks SET assigned_to = :assigned_to WHERE id = :id AND household_id = :household_id");
    foreach ($_POST['assignment'] as $taskId => $newAssigneeId) {
        if (!in_array((int) $newAssigneeId, $validUserIds, true)) {
            continue;
        }

        $stmt->execute([
            'assigned_to' => (int) $newAssigneeId,
            'id' => (int) $taskId,
            'household_id' => $_SESSION['household_id'],
        ]);
    }

    header('Location: index.php?page=assign-all-tasks');
    exit;
}



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
        COALESCE(household_tasks.custom_instructions, task_library.instructions) AS instructions,
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
$query = applySortOrder($query, $sort);

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

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

$zoneStmt = $pdo->prepare("SELECT * FROM zones WHERE household_id = :household_id ORDER BY name");
$zoneStmt->execute(['household_id' => $_SESSION['household_id']]);
$zones = $zoneStmt->fetchAll();
?>


<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>Assign All Tasks</h1>
</div>
<p class="text assign-description">Tap a housemate's initial on any task to reassign it. Save at the bottom to confirm changes.</p>
<?php include 'includes/task-filters.php'; ?>
<?php include 'includes/sort-chips.php'; ?>


<form method="POST" action="index.php?page=assign-all-tasks" id="assign-form">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

    <?php foreach ($housemates as $housemate): ?>
        <?php
            $housemateTasks = array_filter($tasks, function ($task) use ($housemate) {
                return (int) $task['assigned_to'] === (int) $housemate['id'];
            });

            $dailyCount = 0;
            $weeklyCount = 0;
            $monthlyCount = 0;
            foreach ($housemateTasks as $t) {
                $freq = strtolower($t['frequency']);
                if ($freq === 'daily') $dailyCount++;
                elseif ($freq === 'weekly') $weeklyCount++;
                elseif ($freq === 'monthly') $monthlyCount++;
            }
        ?>
        <h3 class="housemate-name" id="housemate-header-<?= $housemate['id'] ?>">
            <?= htmlspecialchars($housemate['name'] ?? $housemate['username']) ?> &ndash; <span class="total-count"><?= count($housemateTasks) ?></span> Tasks
        </h3>
        <div class="task-card assign-list" id="housemate-list-<?= $housemate['id'] ?>">
            <?php foreach ($housemateTasks as $task): ?>
                <div class="assign-border" data-frequency="<?= strtolower($task['frequency']) ?>" data-original-assignee-id="<?= (int) $task['assigned_to'] ?>" data-original-assignee-name="<?= htmlspecialchars($task['assignee_name']) ?>">
                    <div class="assign-list-row"> 
                        <div class="assign-list-row-text">
                            <div class="task-title">
                                <strong class="task-title-text"><?= htmlspecialchars($task['name']) ?></strong>
                                <button type="button" class="info-icon" data-task-name="<?= htmlspecialchars($task['name']) ?>" data-task-frequency="<?= htmlspecialchars(ucfirst(strtolower($task['frequency']))) ?>" data-task-frequency-detail="<?= htmlspecialchars(formatFrequencyDetailExpanded($task['frequency'], $task['day_of_week'], $task['week_of_month']) ?? '') ?>" data-task-time="<?= htmlspecialchars($task['total_time']) ?> mins" data-task-description="<?= htmlspecialchars(!empty($task['instructions']) ? $task['instructions'] : 'No description added.') ?>">
                                    <span class="material-symbols-outlined info-icon">info</span>
                                </button>
                            </div>
                            <div class="task-meta-line">
                                <?= htmlspecialchars($task['room']) ?> &middot; <em><?= htmlspecialchars($task['total_time']) ?>m</em> &middot; <?= formatFrequencyDetailHtml($task['frequency'], $task['day_of_week'], $task['week_of_month']) ?>
                            </div>
                            <div class="assign-previous" style="display:none;"></div>
                        </div>
                        <div class="assign-avatars">
                            <?php foreach ($housemates as $hm): ?>
                                <input type="radio" id="assign-<?= $task['id'] ?>-<?= $hm['id'] ?>" name="assignment[<?= $task['id'] ?>]" value="<?= $hm['id'] ?>" class="addTask" <?= ((int) $task['assigned_to'] === (int) $hm['id']) ? 'checked' : '' ?>>
                                <label for="assign-<?= $task['id'] ?>-<?= $hm['id'] ?>" class="addTask profile-icon-sm">
                                    <?= strtoupper(substr($hm['name'] ?? $hm['username'], 0, 1)) ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <div class="task-card">
        <button type="submit" class="btn-primary">Save</button>
    </div>
</form>