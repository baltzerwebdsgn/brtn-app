<?php
// Logic specific to the Home page
$stmt = $pdo->prepare("
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
");
$stmt->execute(['household_id' => $_SESSION['household_id']]);
$tasks = $stmt->fetchAll();
?>



<?php if (empty($tasks)): ?>
    <h3>All done for today!</h3>
    <p>Nice work - check back in tomorrow for your next tasks.</p>
<?php else: ?>
    <?php foreach ($tasks as $task): ?>
        <?php include 'includes/task-card.php'; ?>
    <?php endforeach; ?>
<?php endif; ?>
<h2> Cleaning Score </h2>
<div class="task-card">
    <div class="circular-progress" style="--progress: 100;">
        <div class="inner-circle">
            <span class="progress-percentage">100%</span>
            <span class="text">Last 30 Days</span>
        </div>
    </div>
</div>