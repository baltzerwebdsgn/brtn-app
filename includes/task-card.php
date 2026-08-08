<?php /** @var array $task */ ?>
<?php /** @var string $cardActions expects 'status' or 'edit' */ ?>
<div class="task-card" id="task-<?= htmlspecialchars($task['id']) ?>">
    <div class="task-content">
        <div class="task-title">
            <strong class="task-title-text <?= (isset($flashEditedTaskId) && $flashEditedTaskId == $task['id']) ? 'task-just-updated' : '' ?>"><?= htmlspecialchars($task['name']) ?></strong>
            <a class="info-icon">
                <span class="material-symbols-outlined info-icon">info</span>
            </a>
        </div>

        <div class="task-meta-zone">
            <?= htmlspecialchars($task['room']) ?> &middot; <i><?= htmlspecialchars($task['total_time']) ?>m</i>
        </div>
        <div class="task-meta-frequency">
            <?= formatFrequencyDetailHtml($task['frequency'], $task['day_of_week'], $task['week_of_month']) ?>
        </div>

        <div class="task-action-primary">
            <?php if ($cardActions === 'edit'): ?>
                <a href="index.php?page=settings&edit_task=<?= htmlspecialchars($task['id']) ?>#add-a-task" class="edit-task-btn">
                    <span class="material-symbols-outlined">edit</span>
                </a>
            <?php else: ?>
                <span class="task-status-info"></span>
            <?php endif; ?>
        </div>

        <div class="task-action-secondary">
            <?php if ($cardActions === 'edit'): ?>
                <form action="index.php?page=edit-all-tasks" method="POST" class="inline-icon-form" onsubmit="return confirm('Remove this task?\nThis cannot be undone.');">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                    <input type="hidden" name="delete_task_id" value="<?= htmlspecialchars($task['id']) ?>">
                    <button type="submit" class="remove-task-btn">
                        <span class="danger material-symbols-outlined">delete</span>
                    </button>
                </form>
            <?php else: ?>
                <button type="button" class="task-status-btn" aria-label="Mark done">
                    <span class="material-symbols-outlined">check_circle</span>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>
