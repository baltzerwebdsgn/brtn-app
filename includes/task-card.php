<?php /** @var array $task */ ?>
<?php /** @var string $cardActions expects 'status' or 'edit' */ ?>
<?php
$hasBeenCompleted = !empty($task['last_completed']);
$rawStatus = $task['status'] ?? 'due';
$isDone = $rawStatus === 'idle' && $hasBeenCompleted;
$completedToday = wasCompletedToday($task['last_completed']);
$statusClass = getDisplayStatus($rawStatus, $completedToday);
$statusLabel = ucfirst($statusClass);

$isHead = $_SESSION['role'] === 'head';
$isAssignedToMe = !isset($task['assigned_to']) || (int) $task['assigned_to'] === (int) $_SESSION['user_id'];
$canUndo = $isDone && ($isHead || (!empty($allowUndo) && $isAssignedToMe));
?>


<div class="task-card" id="task-<?= htmlspecialchars($task['id']) ?>" data-assigned-to="<?= (int) ($task['assigned_to'] ?? 0) ?>">
    <div class="task-layout">
        <div class="upcoming-profile-icon">
             <?php if ($cardActions === 'upcoming'): ?>
                <?php
                $avatarClass = ((int) ($task['assigned_to'] ?? 0) === (int) $_SESSION['user_id']) ? 'active' : 'inactive';
                ?>
                <span class="profile-icon-sm <?= $avatarClass ?>">
                        <?= htmlspecialchars(strtoupper(substr($task['assignee_name'] ?? '?', 0, 1))) ?>
                    </span>
            <?php endif; ?>
        </div>
        <div class="task-content">
            <div class="task-title">
                <strong class="task-title-text <?= (isset($flashEditedTaskId) && $flashEditedTaskId == $task['id']) ? 'task-just-updated' : '' ?>"><?= htmlspecialchars($task['name']) ?></strong>
                <div class="task-title-right">
                    <?php if ($cardActions === 'upcoming'): ?>
                        <span class="task-title-room"><?= htmlspecialchars($task['room']) ?></span>
                    <?php endif; ?>
                    <button type="button" class="info-icon" data-task-name="<?= htmlspecialchars($task['name']) ?>" data-task-frequency="<?= htmlspecialchars(ucfirst(strtolower($task['frequency']))) ?>" data-task-frequency-detail="<?= htmlspecialchars(formatFrequencyDetailExpanded($task['frequency'], $task['day_of_week'], $task['week_of_month']) ?? '') ?>" data-task-time="<?= htmlspecialchars($task['total_time']) ?> mins" data-task-description="<?= htmlspecialchars(!empty($task['instructions']) ? $task['instructions'] : 'No description added.') ?>">
                        <span class="material-symbols-outlined info-icon">info</span>
                    </button>
                </div>
            </div>
            <?php if ($cardActions === 'upcoming'): ?>
                <div class="task-body upcoming-body">
                    <div class="task-meta-column">
                        <div class="task-meta-repeat">Repeat: <?= htmlspecialchars(formatFrequencyDetail($task['frequency'], $task['day_of_week'], $task['week_of_month'])) ?></div>
                        <div class="task-meta-last" data-next-date="<?= htmlspecialchars($task['next_due_date'] ?? '') ?>">Last: <?= htmlspecialchars(formatRelativeDate($task['last_completed'])) ?></div>
                        <div class="task-meta-next">Next: <?= htmlspecialchars(!empty($task['next_due_date']) ? date('m-d-Y', strtotime($task['next_due_date'])) : '—') ?></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="task-body">
                    <div class="task-meta-column">
                        <div class="task-meta-line">
                            <?php if (!isset($showZone) || $showZone): ?>
                                <?= htmlspecialchars($task['room']) ?> &middot; <?php endif; ?><i><?= htmlspecialchars($task['total_time']) ?>m</i> &middot; <?= formatFrequencyDetailHtml($task['frequency'], $task['day_of_week'], $task['week_of_month']) ?>
                                    <?php if ((!isset($showAssignee) || $showAssignee) || !$isAssignedToMe): ?> 
                                        &middot; <?= htmlspecialchars($task['assignee_name'] ?? 'Unassigned') ?>
                            <?php endif; ?>
                        </div>
                        <div class="completed-by-note" style="<?= ($isDone && !empty($task['completed_by_id']) && (int) $task['completed_by_id'] !== (int) $task['assigned_to']) ? '' : 'display:none;' ?>">
                            <?= !empty($task['completed_by_name']) ? 'Completed by ' . htmlspecialchars($task['completed_by_name']) : '' ?>
                        </div>
                    </div>
                    <div class="task-body-status">
                        <div class="task-action-primary">
                            <?php if ($cardActions === 'edit'): ?>
                                <a href="index.php?page=settings&edit_task=<?= htmlspecialchars($task['id']) ?>" class="edit-task-btn">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                            <?php else: ?>
                                <span class="task-status-info <?= htmlspecialchars($statusClass) ?>"><?= htmlspecialchars($statusLabel) ?></span>
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
                                <?php if ($statusClass === 'done' && !$canUndo): ?>
                                    <button type="button" class="task-status-btn placeholder" tabindex="-1" aria-hidden="true" disabled></button>
                                <?php else: ?>
                                    <button type="button" class="task-status-btn <?= $statusClass === 'done' ? 'is-done' : '' ?>" data-task-id="<?= $task['id'] ?>" aria-label="<?= $statusClass === 'done' ? 'Undo' : 'Mark done' ?>">
                                        <span class="material-symbols-outlined"><?= $statusClass === 'done' ? 'undo' : 'check_small' ?></span>
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
