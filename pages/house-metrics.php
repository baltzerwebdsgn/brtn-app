<?php
$from = $_GET['from'] ?? 'settings';
// ---- House Cleaning Score (last 30 days) ----
// Starts at 100%, then loses points based on two *rates*, not raw counts: the
// share of this month's completions that came in after their due date, and the
// share of currently-active tasks sitting overdue right now. Rates instead of
// flat points-per-incident matter here — a household with a long history (lots
// of completions logged) shouldn't be punished harder than a smaller one just
// for having more data; only the proportion that's late/overdue should count.
// The two rates stay on different time-scopes on purpose: lateness looks back
// over a rolling 30 days (a history), overdue is a live snapshot of right now —
// the schema only tracks each task's next due date, not a full log of every
// occurrence that was ever due, so a single unified metric isn't reconstructable
// without a bigger schema change.

$lateCompletionsStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM task_history
    INNER JOIN household_tasks ON task_history.task_id = household_tasks.id
    WHERE household_tasks.household_id = :household_id
    AND task_history.completed_at >= :thirty_days_ago
    AND task_history.due_date IS NOT NULL
    AND DATE(task_history.completed_at) > task_history.due_date
");
$lateCompletionsStmt->execute([
    'household_id' => $_SESSION['household_id'],
    'thirty_days_ago' => date('Y-m-d H:i:s', strtotime('-30 days')),
]);
$lateCompletions = (int) $lateCompletionsStmt->fetchColumn();

$activeTasksStmt = $pdo->prepare("
    SELECT
        household_tasks.id,
        household_tasks.assigned_to,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_day_of_week, task_library.day_of_week) AS day_of_week,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.household_id = :household_id
    AND household_tasks.is_active = 1
");

$activeTasksStmt->execute(['household_id' => $_SESSION['household_id']]);
$activeTasks = $activeTasksStmt->fetchAll();

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
$housemateCounts = [];
foreach ($housemates as $housemate) {
    $housemateCounts[$housemate['id']] = 0;
}
foreach ($activeTasks as $task) {
    if (isset($housemateCounts[$task['assigned_to']])) {
        $housemateCounts[$task['assigned_to']]++;
    }
}
$maxHousemateCount = !empty($housemateCounts) ? max($housemateCounts) : 0;
$housemateDayOfWeek = [];
$housemateWeekOfMonth = [];
foreach ($housemates as $housemate) {
    $housemateDayOfWeek[$housemate['id']] = ['Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0];
    $housemateWeekOfMonth[$housemate['id']] = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];
}

foreach ($activeTasks as $task) {
    $freq = strtolower($task['frequency']);
    $assignedTo = $task['assigned_to'];
    if (!isset($housemateDayOfWeek[$assignedTo])) {
        continue;
    }
    if ($freq === 'weekly' && $task['day_of_week']) {
        foreach (explode(',', $task['day_of_week']) as $day) {
            $day = trim($day);
            if (isset($housemateDayOfWeek[$assignedTo][$day])) {
                $housemateDayOfWeek[$assignedTo][$day]++;
            }
        }
    } elseif ($freq === 'monthly' && $task['week_of_month']) {
        $week = (string) (int) $task['week_of_month'];
        if (isset($housemateWeekOfMonth[$assignedTo][$week])) {
            $housemateWeekOfMonth[$assignedTo][$week]++;
        }
    }
}

// tallies for frequency distribution
$frequencyCounts = ['daily' => 0, 'weekly' => 0, 'monthly' => 0];

$dailyByHousemate = [];
foreach ($housemates as $housemate) {
    $dailyByHousemate[$housemate['id']] = 0;
}
$weeklyByDay = ['Sunday' => 0, 'Monday' => 0, 'Tuesday' => 0, 'Wednesday' => 0, 'Thursday' => 0, 'Friday' => 0, 'Saturday' => 0];
$monthlyByWeek = ['1' => 0, '2' => 0, '3' => 0, '4' => 0];

foreach ($activeTasks as $task) {
    $freq = strtolower($task['frequency']);
    if (!isset($frequencyCounts[$freq])) {
        continue;
    }
    $frequencyCounts[$freq]++;

    if ($freq === 'daily') {
        if (isset($dailyByHousemate[$task['assigned_to']])) {
            $dailyByHousemate[$task['assigned_to']]++;
        }
    } elseif ($freq === 'weekly' && $task['day_of_week']) {
        foreach (explode(',', $task['day_of_week']) as $day) {
            $day = trim($day);
            if (isset($weeklyByDay[$day])) {
                $weeklyByDay[$day]++;
            }
        }
    } elseif ($freq === 'monthly' && $task['week_of_month']) {
        $week = (string) (int) $task['week_of_month'];
        if (isset($monthlyByWeek[$week])) {
            $monthlyByWeek[$week]++;
        }
    }
}

$maxFrequencyCount = !empty($frequencyCounts) ? max($frequencyCounts) : 0;
$maxDailyByHousemate = !empty($dailyByHousemate) ? max($dailyByHousemate) : 0;
$maxWeeklyByDay = !empty($weeklyByDay) ? max($weeklyByDay) : 0;
$maxMonthlyByWeek = !empty($monthlyByWeek) ? max($monthlyByWeek) : 0;


// Cleaning score logic
$overdueCount = 0;
foreach ($activeTasks as $task) {
    $status = getTaskStatus($pdo, $task['id'], $task['frequency']);
    if (($status['status'] ?? '') === 'overdue') {
        $overdueCount++;
    }
}
$totalCompletionsStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM task_history
    INNER JOIN household_tasks ON task_history.task_id = household_tasks.id
    WHERE household_tasks.household_id = :household_id
    AND task_history.completed_at >= :thirty_days_ago
");
$totalCompletionsStmt->execute([
    'household_id' => $_SESSION['household_id'],
    'thirty_days_ago' => date('Y-m-d H:i:s', strtotime('-30 days')),
]);
$totalCompletions = (int) $totalCompletionsStmt->fetchColumn();

$lateRate = $totalCompletions > 0 ? ($lateCompletions / $totalCompletions) : 0;
$overdueRate = count($activeTasks) > 0 ? ($overdueCount / count($activeTasks)) : 0;

$cleaningScore = (int) round(100 - ($lateRate * 40) - ($overdueRate * 40));
$cleaningScore = max(0, min(100, $cleaningScore));
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>House Metrics</h1>
</div>
<h2>House Cleaning Score</h2>
<div class="task-card">
    <div class="circular-progress" style="--progress: <?= $cleaningScore ?>;">
        <div class="inner-circle">
            <span class="progress-percentage"><?= $cleaningScore ?>%</span>
            <span class="text">Last 30 Days</span>
        </div>
    </div>
    <p class="text metrics-breakdown">
        <?= round((1 - $lateRate) * 100) ?>% of completions on time this month · <?= $overdueCount ?> task<?= $overdueCount === 1 ? '' : 's' ?> need<?= $overdueCount === 1 ? 's' : '' ?> attention
    </p>

</div>
<h2>Task Distribution</h2>
<div class="distribution-section">
    <div class="chip-group distribution-toggle">
        <input type="radio" id="distribution-housemate" name="distribution_view" value="housemate" class="addTask" checked>
        <label for="distribution-housemate" class="addTask chip">By housemate</label>
        <input type="radio" id="distribution-frequency" name="distribution_view" value="frequency" class="addTask">
        <label for="distribution-frequency" class="addTask chip">By frequency</label>
    </div>
    <div class="task-card" id="distribution-by-housemate">
        <?php
        $housemateColors = ['--color-citrus-lemon', '--color-ice500', '--color-citrus-terracotta', '--color-citrus-grapefruit', '--color-citrus-lime', '--color-citrus-orange'];
        ?>
        <?php foreach ($housemates as $i => $housemate): ?>
        <?php
        $count = $housemateCounts[$housemate['id']];
        $barWidth = $maxHousemateCount > 0 ? round(($count / $maxHousemateCount) * 100) : 0;
        $barColor = $housemateColors[$i % count($housemateColors)];
        ?>
        <details class="filter-dropdown">
            <summary class="filter-summary">
                <div class="metrics-row">
                    <span class="metrics-row-label"><?= htmlspecialchars($housemate['name'] ?? $housemate['username']) ?></span>
                    <div class="zone-progress-bar metrics-bar">
                        <div class="zone-progress-fill" style="width: <?= $barWidth ?>%; background-color: var(<?= $barColor ?>);"></div>
                    </div>
                    <span class="metrics-row-count"><?= $count ?></span>
                </div>
                <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
            </summary>
            <div class="metrics-subgroup">
                <p class="text metrics-subgroup-label">Days of Week</p>
                <?php
                $barChartData = [];
                foreach ($housemateDayOfWeek[$housemate['id']] as $day => $count) {
                    $barChartData[abbreviateDay($day)] = $count;
                }
                include 'includes/bar-chart.php';
                ?>
                <p class="text metrics-subgroup-label">Weeks of Month</p>
                <?php
                $barChartData = [];
                foreach ($housemateWeekOfMonth[$housemate['id']] as $week => $count) {
                    $barChartData[formatWeekOfMonth($week)] = $count;
                }
                include 'includes/bar-chart.php';
                ?>
            </div>
        </details>
    <?php endforeach; ?>

    </div>

    <div class="task-card" id="distribution-by-frequency">
        <?php 
        $frequencyColors = [
            'daily' => '--color-citrus-grapefruit',
            'weekly' => '--color-citrus-lime',
            'monthly' => '--color-citrus-orange',
        ];
        ?>
        <?php
        $frequencyLabels = ['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'];
        foreach ($frequencyLabels as $freqKey => $freqLabel):
            $count = $frequencyCounts[$freqKey];
            $barWidth = $maxFrequencyCount > 0 ? round(($count / $maxFrequencyCount) * 100) : 0;
        ?>
            <details class="filter-dropdown">
                <summary class="filter-summary">
                    <div class="metrics-row">
                        <span class="metrics-row-label"><?= htmlspecialchars($freqLabel) ?></span>
                        <div class="zone-progress-bar metrics-bar">
                            <div class="zone-progress-fill" style="width: <?= $barWidth ?>%; background-color: var(<?= $frequencyColors[$freqKey] ?>);"></div>
                        </div>
                        <span class="metrics-row-count"><?= $count ?></span>
                    </div>
                    <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
                </summary>
                <div class="metrics-subgroup">
                    <?php if ($freqKey === 'daily'): ?>
                        <p class="text metrics-subgroup-label">Housemates</p>
                        <?php
                        $barChartData = [];
                        foreach ($housemates as $housemate) {
                            $barChartData[$housemate['name'] ?? $housemate['username']] = $dailyByHousemate[$housemate['id']];
                        }
                        include 'includes/bar-chart.php';
                        ?>
                    <?php elseif ($freqKey === 'weekly'): ?>
                        <p class="text metrics-subgroup-label">Days of Week</p>
                        <?php
                        $barChartData = [];
                        foreach ($weeklyByDay as $day => $count) {
                            $barChartData[abbreviateDay($day)] = $count;
                        }
                        include 'includes/bar-chart.php';
                        ?>
                    <?php else: ?>
                        <p class="text metrics-subgroup-label">Weeks of Month</p>
                        <?php
                        $barChartData = [];
                        foreach ($monthlyByWeek as $week => $count) {
                            $barChartData[formatWeekOfMonth($week)] = $count;
                        }
                        include 'includes/bar-chart.php';
                        ?>
                    <?php endif; ?>


                </div>
            </details>
        <?php endforeach; ?>

    </div>
</div>
<h2>Task Progress</h2>
<div class="task-card">
</div>