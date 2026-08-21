<?php
// This single page covers four modes for one zone (view its tasks, create a new
// zone, edit an existing one, delete/reassign one), switched on by the $isEditing /
// $isDeleting flags computed below plus whether $zone was found at all.

// ---- Request setup & zone lookup ----
// $from remembers which settings page the user came from, so the back link and any
// post-save redirects return them there instead of always landing on Settings.
$from = $_GET['from'] ?? 'settings';
$isHomeView = $from === 'home';
$showAssigneeFilter = !$isHomeView;
$isHead = $_SESSION['role'] === 'head';
$zoneId = isset($_GET['id']) ? (int) $_GET['id'] : null;
// $replacingZoneId is only set while deleting a zone that still has tasks: the user
// was sent here to create a fresh zone to move those tasks into (see the delete-zone
// POST handler below and the "Create New Zone" link in the delete/reassign view).
$replacingZoneId = isset($_GET['replacing']) ? (int) $_GET['replacing'] : (isset($_POST['replacing']) ? (int) $_POST['replacing'] : null);
$zoneError = null;
$zoneTasks = null;
$zone = null;
$showZone = false;

if ($zoneId) {
    $zoneStmt = $pdo->prepare("SELECT * FROM zones WHERE id = :id AND household_id = :household_id");
    $zoneStmt->execute(['id' => $zoneId, 'household_id' => $_SESSION['household_id']]);
    $zone = $zoneStmt->fetch();

    // Bail out to Settings if the id is bogus or belongs to another household —
    // household_id is part of the WHERE above specifically so one household can't
    // view/edit another's zone just by guessing an id in the URL.
    if (!$zone) {
        header('Location: index.php?page=settings#zones-section');
        exit;
    }
}
$zoneTotalTaskCount = 0;
if ($zone) {
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) FROM household_tasks
        LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
        WHERE household_tasks.household_id = :household_id
        AND household_tasks.is_active = 1
        AND COALESCE(household_tasks.custom_room, task_library.room) = :zone_name
    ");
    $countStmt->execute(['household_id' => $_SESSION['household_id'], 'zone_name' => $zone['name']]);
    $zoneTotalTaskCount = (int) $countStmt->fetchColumn();
}

$availableIcons = ['basement', 'bathroom', 'bedroom', 'bedroom-alt-1', 'bedroom-alt-2', 'cat-tree', 'closet', 'dining', 'garden', 'hallway', 'houseplant', 'kitchen', 'kitchen-alt-1', 'laundry', 'living-room', 'nursery', 'office', 'outside', 'storage', 'toilet', 'trash', 'trees'];
$filter = $_GET['filter'] ?? 'All';
$assignee_filter = $isHomeView ? (string) $_SESSION['user_id'] : ($_GET['assignee'] ?? 'All');
$statusFilter = $_GET['status'] ?? 'All';
$sort = $_GET['sort'] ?? 'title';
$showZoneFilter = false;

// ---- Zone task list (only relevant when viewing an existing zone) ----
if ($zone) {
    $currentParams = [
        'id' => $zoneId,
        'filter' => $filter,
        'assignee' => $assignee_filter,
        'status' => $statusFilter,
        'sort' => $sort,
    ];

    // Every field is COALESCE(custom_*, library_*): a household can either use a task
    // straight from the shared task_library, or override any field with its own
    // custom_* value — the custom column wins whenever it's set (non-null).
    //
    // Tasks aren't linked to zones by id — there's no zone_id column yet, only this
    // room name string, matched against zones.name. That's why renaming a zone below
    // has to cascade-update every task's room name to match (see the UPDATE ...
    // household_tasks query in the create/update handler further down); a real
    // zone_id foreign key is a known backlog item, not an oversight.
    $taskListQuery = "
        SELECT
            household_tasks.id,
            household_tasks.assigned_to,
            COALESCE(household_tasks.custom_name, task_library.name) AS name,
            COALESCE(household_tasks.custom_room, task_library.room) AS room,
            COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
            COALESCE(household_tasks.custom_total_time, task_library.total_time) AS total_time,
            COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
            COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month,
            COALESCE(household_tasks.custom_instructions, task_library.instructions) AS instructions,
            COALESCE(assigned_user.name, assigned_user.username) AS assignee_name
        FROM household_tasks
        LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
        LEFT JOIN users AS assigned_user ON household_tasks.assigned_to = assigned_user.id
        WHERE household_tasks.household_id = :household_id
        AND household_tasks.is_active = 1
        AND COALESCE(household_tasks.custom_room, task_library.room) = :zone_name
    ";
    $taskListParams = [
        'household_id' => $_SESSION['household_id'],
        'zone_name' => $zone['name'],
    ];

    if ($filter !== 'All') {
        $taskListQuery .= " AND LOWER(COALESCE(household_tasks.custom_frequency, task_library.frequency)) = LOWER(:freq)";
        $taskListParams['freq'] = $filter;
    }
    if ($assignee_filter !== 'All') {
        $taskListQuery .= " AND household_tasks.assigned_to = :assignee_id";
        $taskListParams['assignee_id'] = (int) $assignee_filter;
    }

    $taskListQuery = applySortOrder($taskListQuery, $sort);

    $taskListStmt = $pdo->prepare($taskListQuery);
    $taskListStmt->execute($taskListParams);
    $zoneTasks = $taskListStmt->fetchAll();

    // Status (due/overdue/done/idle) isn't stored on the task row — it's derived per
    // task from task_day_status, so it has to be computed in a loop after the fetch
    // rather than pulled in by the query above.
    foreach ($zoneTasks as &$task) {
        $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
        $task['last_completed'] = $status['last_completed'] ?? null;
        $task['completed_by_id'] = $status['completed_by_id'] ?? null;
        $task['completed_by_name'] = $status['completed_by_name'] ?? null;
        $task['status'] = $status['status'] ?? 'due';
    }
    unset($task);

    // Status can't be filtered in SQL (see the loop above), so unlike $filter/
    // $assignee_filter this one is applied in PHP after the fact.
    if ($statusFilter !== 'All') {
        $zoneTasks = array_values(array_filter($zoneTasks, function ($t) use ($statusFilter) {
            $displayStatus = getDisplayStatus($t['status'], wasCompletedToday($t['last_completed']));
            return strtolower($statusFilter) === $displayStatus;
        }));
    }

    // Housemate list, used by includes/task-filters.php to populate the assignee filter.
    $housemateStmt = $pdo->prepare("
        SELECT * FROM users
        WHERE household_id = :household_id
        ORDER BY (id = :user_id) DESC, name ASC
    ");
    $housemateStmt->execute([
        'household_id' => $_SESSION['household_id'],
        'user_id' => $_SESSION['user_id'],
    ]);
    $housemates = $housemateStmt->fetchAll();
}

// Candidates offered in the delete/reassign view below, for moving this zone's
// tasks elsewhere before the zone itself is removed. Fetched unconditionally
// (not just when $isDeleting) since $isDeleting is only known further down.
$otherZones = [];
if ($zone) {
    $otherZonesStmt = $pdo->prepare("SELECT id, name FROM zones WHERE household_id = :household_id AND id != :id ORDER BY name");
    $otherZonesStmt->execute(['household_id' => $_SESSION['household_id'], 'id' => $zoneId]);
    $otherZones = $otherZonesStmt->fetchAll();
}

// ---- View-state flags & task-card display options ----
// $isEditing covers both "creating a brand-new zone" (no $zone at all) and
// "editing an existing one" (?edit=1) — both render the same form further down.
$isDeleting = isset($_GET['delete']);
$isEditing = !$zone || isset($_GET['edit']);
// Passed through to includes/task-card.php for every task rendered on this page:
// zone tasks are shown with a simple status pill, no undo, always with the assignee
// name (unlike e.g. the home page, which hides the assignee for "your own" tasks).
$cardActions = 'status';
$allowUndo = false;
$showAssignee = true;

// ---- POST: create or update a zone ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['zone_name']) && $isHead) {
    requireCsrf();
    $zoneName = ucfirst(strtolower(trim($_POST['zone_name'])));
    $zoneIcon = $_POST['zone_icon'] ?? '';

    if ($zoneName === '') {
        $zoneError = 'Zone name is required.';
    } elseif (!in_array($zoneIcon, $availableIcons, true)) {
        // No icon submitted (or it's not a real option, e.g. tampered form data) —
        // guess one from the name via matchZoneIcon(), falling back to a generic icon.
        $zoneIcon = matchZoneIcon($zoneName) ?? 'hallway';
    }

    if (!$zoneError) {
        // id != :id excludes this zone's own current name from the duplicate check,
        // so saving a zone without changing its name doesn't false-positive on itself.
        $dupCheck = $pdo->prepare("SELECT id FROM zones WHERE household_id = :household_id AND name = :name AND id != :id");
        $dupCheck->execute([
            'household_id' => $_SESSION['household_id'],
            'name' => $zoneName,
            'id' => $zoneId ?? 0,
        ]);

        if ($dupCheck->fetch()) {
            $zoneError = 'A zone with that name already exists.';
        } elseif ($zoneId) {
            $oldName = $zone['name'];

            $updateStmt = $pdo->prepare("UPDATE zones SET name = :name, icon = :icon WHERE id = :id AND household_id = :household_id");
            $updateStmt->execute([
                'name' => $zoneName,
                'icon' => $zoneIcon,
                'id' => $zoneId,
                'household_id' => $_SESSION['household_id'],
            ]);

            // Zones are matched to tasks by name (see the note on the task list query
            // above), so renaming one is meaningless unless every task currently
            // pointing at the old room name gets moved to the new one too — otherwise
            // this zone's tasks would silently vanish from it after a rename.
            if ($zoneName !== $oldName) {
                $cascadeStmt = $pdo->prepare("
                    UPDATE household_tasks
                    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
                    SET household_tasks.custom_room = :new_name
                    WHERE household_tasks.household_id = :household_id
                    AND COALESCE(household_tasks.custom_room, task_library.room) = :old_name
                ");
                $cascadeStmt->execute([
                    'new_name' => $zoneName,
                    'household_id' => $_SESSION['household_id'],
                    'old_name' => $oldName,
                ]);
            }


            header('Location: index.php?page=zone&id=' . $zoneId);
            exit;
        } else {
            $insertStmt = $pdo->prepare("INSERT INTO zones(household_id, name, icon) VALUES (:household_id, :name, :icon)");
            $insertStmt->execute([
                'household_id' => $_SESSION['household_id'],
                'name' => $zoneName,
                'icon' => $zoneIcon,
            ]);

            // If this new zone exists to replace one being deleted, send the user
            // straight back into that zone's delete/reassign flow (see $replacingZoneId
            // above) so they can pick this brand-new zone as the reassignment target.
            if ($replacingZoneId) {
                header('Location: index.php?page=zone&id=' . $replacingZoneId . '&delete=1&from=settings');
            } else {
                header('Location: index.php?page=settings#zones-section');
            }
            exit;
        }
    }
}

// ---- POST: delete a zone ----
// A zone with tasks can't just be deleted outright — its tasks would be left
// pointing at a room name with no matching zone. So this either requires a
// reassignment target first, or (if the zone is already empty) deletes immediately.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_zone']) && $isHead && $zoneId) {
    requireCsrf();

    $reassignToId = isset($_POST['reassign_to']) ? (int) $_POST['reassign_to'] : null;

    if ($zoneTotalTaskCount > 0 && !$reassignToId) {
        $zoneError = 'Please choose a zone to move these tasks to.';
    } else {
        if ($zoneTotalTaskCount > 0) {
            $targetStmt = $pdo->prepare("SELECT name FROM zones WHERE id = :id AND household_id = :household_id");
            $targetStmt->execute(['id' => $reassignToId, 'household_id' => $_SESSION['household_id']]);
            $targetZone = $targetStmt->fetch();

            if (!$targetZone) {
                $zoneError = 'Please choose a valid zone to move these tasks to.';
            } else {
                // Same room-name update as the rename cascade above, just moving
                // tasks to a different zone's name instead of this zone's new name.
                $reassignStmt = $pdo->prepare("
                    UPDATE household_tasks
                    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
                    SET household_tasks.custom_room = :new_name
                    WHERE household_tasks.household_id = :household_id
                    AND COALESCE(household_tasks.custom_room, task_library.room) = :old_name
                ");
                $reassignStmt->execute([
                    'new_name' => $targetZone['name'],
                    'household_id' => $_SESSION['household_id'],
                    'old_name' => $zone['name'],
                ]);
            }
        }

        if (!$zoneError) {
            $deleteStmt = $pdo->prepare("DELETE FROM zones WHERE id = :id AND household_id = :household_id");
            $deleteStmt->execute(['id' => $zoneId, 'household_id' => $_SESSION['household_id']]);

            header('Location: index.php?page=settings#zones-section');
            exit;
        }
    }
}
?>
<!-- ---- Title row: back link + (view-mode only) edit/delete actions ---- -->
<div class="zone-title-row">
    <div class="setting-subpage-title">
        <?php
        // Normally "back" returns to wherever the user came from (#zones-section on
        // that page). Mid-replacement, it instead returns to the zone being deleted,
        // since that's the flow this "new zone" page was opened from.
        $backHref = 'index.php?page=' . htmlspecialchars($from) . '#zones-section';
        if (!$zone && $replacingZoneId) {
            $backHref = 'index.php?page=zone&id=' . $replacingZoneId . '&delete=1&from=' . htmlspecialchars($from);
        } elseif ($isHomeView) {
            $backHref = 'index.php?page=home';
        }

        ?>
        <a href="<?= $backHref ?>">&larr;</a>
        <h1><?= $zone ? htmlspecialchars($zone['name']) : ($replacingZoneId ? 'Replacement Zone' : 'New Zone') ?></h1>
    </div>
    <?php if ($isHead && $zone && !$isEditing && !$isDeleting && !$isHomeView): ?>
        <div class="zone-title-actions">
            <a href="index.php?page=zone&id=<?= $zone['id'] ?>&edit=1&from=<?= htmlspecialchars($from) ?>" class="edit-task-btn">
                <span class="material-symbols-outlined">edit</span>
            </a>
            <?php if ($zoneTotalTaskCount > 0): ?>
                <!-- Zone has tasks: send to the delete/reassign view instead of deleting outright -->
                <a href="index.php?page=zone&id=<?= $zone['id'] ?>&delete=1&from=<?= htmlspecialchars($from) ?>" class="remove-task-btn">
                    <span class="danger material-symbols-outlined">delete</span>
                </a>
            <?php else: ?>
                <!-- Zone is already empty: delete immediately, no reassignment needed -->
                <form method="POST" action="index.php?page=zone&id=<?= $zone['id'] ?>" onsubmit="return confirm('Delete this zone?\nThis cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="delete_zone" value="1">
                    <button type="submit" class="remove-task-btn">
                        <span class="danger material-symbols-outlined">delete</span>
                    </button>
                </form>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
<?php if ($zoneError): ?>
    <p class="danger"><?= htmlspecialchars($zoneError) ?></p>
<?php endif; ?>

<!-- ---- View mode: this zone's task list (filters, sort, cards) ---- -->
<?php if ($zone && !$isEditing && !$isDeleting): ?>
    <?php include 'includes/task-filters.php'; ?>
    <?php include 'includes/sort-chips.php'; ?>
    <?php if (empty($zoneTasks)): ?>
        <?php if ($zoneTotalTaskCount === 0): ?>
            <?php if ($isHomeView): ?>
                <p class="text">You don't have any tasks in this zone.</p>
            <?php else: ?>
                <p class="text">This zone doesn't have any tasks yet. Assign an existing task here, or create a new one to get started.</p>
            <?php endif; ?>
        <?php else: ?>
            <p class="text">No tasks match the selected filters.</p>
        <?php endif; ?>
    <?php else: ?>
        <div class="task-list">
            <?php foreach ($zoneTasks as $task): ?>
                <?php include 'includes/task-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- ---- Edit mode: create-new-zone / edit-existing-zone form (name + icon picker) ---- -->
<?php if ($isHead && $isEditing): ?>
    <?php
    // Falls back to $zone's saved values on a fresh GET, but re-shows whatever was
    // just submitted on POST (e.g. after a validation error) so the user's input
    // isn't lost.
    $formName = $_POST['zone_name'] ?? ($zone['name'] ?? '');
    $formIcon = $_POST['zone_icon'] ?? ($zone['icon'] ?? null);
    // Same as the POST handler's fallback above: if there's no valid icon yet
    // (brand-new zone, or an invalid one snuck through), guess one from the name.
    if (!$formIcon || !in_array($formIcon, $availableIcons, true)) {
        $formIcon = matchZoneIcon($formName);
    }
    ?>
    <form method="POST" action="index.php?page=zone<?= $zone ? '&id=' . $zone['id'] : '' ?>&from=settings" class="task-card">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
        <?php if (!$zone && $replacingZoneId): ?>
            <input type="hidden" name="replacing" value="<?= $replacingZoneId ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="zone_name" class="subheading">Name</label>
            <input type="text" id="zone_name" name="zone_name" placeholder="e.g. Lemon's Bedroom" value="<?= htmlspecialchars($formName) ?>" class="addTask" required>
        </div>
        <div class="form-group">
            <label class="subheading">Icon</label>
            <!-- One radio input per icon, styled as chips (see .icon-chip) — an
                 image-based radio group rather than a <select>, so all options are
                 visible and pickable at a glance. -->
            <div class="chip-group icon-picker">
                <?php foreach ($availableIcons as $icon): ?>
                    <input type="radio" id="icon-<?= $icon ?>" name="zone_icon" value="<?= $icon ?>" class="addTask" <?= ($icon === $formIcon) ? 'checked' : '' ?>>
                    <label for="icon-<?= $icon ?>" class="addTask chip icon-chip">
                        <span class="icon-chip-img" style="mask-image: url('assets/images/zone-icons/<?= $icon ?>.svg'); -webkit-mask-image: url('assets/images/zone-icons/<?= $icon ?>.svg');"></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <p class="text" id="new-zone-text"><em>* If icon is not selected a best matched to the name will be attempt or a default will be chosen.</em></p>
        </div>
        <div class="form-group">
            <button type="submit" class="btn-primary"><?= $zone ? 'Save Changes' : 'Create Zone' ?></button>
        </div>
    </form>
<?php endif; ?>

<!-- ---- Delete mode: reassign this zone's tasks to another zone, then delete ---- -->
<?php if ($isHead && $zone && $isDeleting): ?>
    <div class="task-card zone-picker-card">
        <div class="zone-picker-header">
            <a href="index.php?page=zone&id=<?= $zone['id'] ?>&from=<?= htmlspecialchars($from) ?>" class="btn-close" aria-label="Cancel">
                <span class="material-symbols-outlined">close</span>
            </a>
            <p class="text replacement-zone-text"><span class="danger">This zone has <?= $zoneTotalTaskCount ?> task<?= $zoneTotalTaskCount === 1 ? '' : 's' ?> assigned to it.</span><br> Choose a zone to move <?= $zoneTotalTaskCount === 1 ? 'it' : 'them' ?> to before deleting.</p>
        </div>
        <?php if (empty($otherZones)): ?>
            <p class="text">There's no other zone to move these tasks to yet.</p>
        <?php else: ?>
            <form method="POST" action="index.php?page=zone&id=<?= $zone['id'] ?>" onsubmit="return confirm('Move all tasks and delete this zone?\nThis cannot be undone.');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <input type="hidden" name="delete_zone" value="1">
                <div class="chip-group replacement-zones">
                    <?php foreach ($otherZones as $otherZone): ?>
                        <input type="radio" id="reassign-<?= $otherZone['id'] ?>" name="reassign_to" value="<?= $otherZone['id'] ?>" class="addTask" required>
                        <label for="reassign-<?= $otherZone['id'] ?>" class="addTask chip"><?= htmlspecialchars($otherZone['name']) ?></label>
                    <?php endforeach; ?>
                    <!-- Escape hatch: no existing zone is a good fit, so spin up a new one
                         (routes through &replacing=, picked up at the top of this file) -->
                    <a href="index.php?page=zone&new=1&replacing=<?= $zone['id'] ?>&from=<?= htmlspecialchars($from) ?>" class="chip">Create New Zone</a>
                </div>
                <button type="submit" class="btn-primary">Move Tasks &amp; Delete Zone</button>
            </form>
        <?php endif; ?>
    </div>
<?php endif; ?>

