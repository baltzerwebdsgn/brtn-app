<?php
require_once __DIR__ . '/../includes/session-init.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

requireCsrf();

$taskId = (int) ($_POST['task_id'] ?? 0);
$action = $_POST['task_action'] ?? '';

$stmt = $pdo->prepare("
    SELECT
        household_tasks.id,
        household_tasks.assigned_to,
        COALESCE(household_tasks.custom_frequency, task_library.frequency) AS frequency,
        COALESCE(household_tasks.custom_week_of_month, task_library.week_of_month) AS week_of_month
    FROM household_tasks
    LEFT JOIN task_library ON household_tasks.library_task_id = task_library.id
    WHERE household_tasks.id = :id AND household_tasks.household_id = :household_id
");
$stmt->execute([
    'id' => $taskId,
    'household_id' => $_SESSION['household_id'],
]);
$task = $stmt->fetch();

if (!$task) {
    http_response_code(404);
    echo json_encode(['error' => 'Task not found']);
    exit;
}


if ($action === 'complete') {
    $statusRow = getTaskStatus($pdo, $taskId, $task['frequency']);
    if (!$statusRow) {
        http_response_code(404);
        echo json_encode(['error' => 'No tracked occurrence found for this task']);
        exit;
    }

    $historyStmt = $pdo->prepare("INSERT INTO task_history(task_id, user_id, completed_at, day_of_week, due_date) VALUES (:task_id, :user_id, NOW(), :day_of_week, :due_date)");
    $historyStmt->execute([
        'task_id' => $taskId,
        'user_id' => $_SESSION['user_id'],
        'day_of_week' => $statusRow['day_of_week'],
        'due_date' => $statusRow['next_due_date'],
    ]);

    $newNextDue = computeNextDueDate($task['frequency'], $statusRow['day_of_week'], $task['week_of_month'], date('Y-m-d'));

    $updateStmt = $pdo->prepare("UPDATE task_day_status SET last_completed = NOW(), next_due_date = :next_due_date WHERE id = :id");
    $updateStmt->execute([
        'next_due_date' => $newNextDue,
        'id' => $statusRow['id'],
    ]);

    $nameStmt = $pdo->prepare("SELECT COALESCE(name, username) AS display_name FROM users WHERE id = :id");
    $nameStmt->execute(['id' => $_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'status' => 'done',
        'completed_by_id' => $_SESSION['user_id'],
        'completed_by_name' => $nameStmt->fetchColumn(),
    ]);
    exit;
}

if ($action === 'undo') {
    if ((int) $task['assigned_to'] !== (int) $_SESSION['user_id'] && $_SESSION['role'] !== 'head') {
        http_response_code(403);
        echo json_encode(['error' => 'Only the assigned user or a head can undo this task']);
        exit;
    }

    $checkStmt = $pdo->prepare("SELECT id, day_of_week, due_date FROM task_history WHERE task_id = :task_id ORDER BY completed_at DESC LIMIT 1");
    $checkStmt->execute(['task_id' => $taskId]);
    $lastEntry = $checkStmt->fetch();

    if (!$lastEntry) {
        http_response_code(404);
        echo json_encode(['error' => 'No completion found']);
        exit;
    }

    $deleteStmt = $pdo->prepare("DELETE FROM task_history WHERE id = :id");
    $deleteStmt->execute(['id' => $lastEntry['id']]);

    $previousStmt = $pdo->prepare("
        SELECT task_history.completed_at, task_history.user_id, COALESCE(users.name, users.username) AS display_name
        FROM task_history
        LEFT JOIN users ON task_history.user_id = users.id
        WHERE task_history.task_id = :task_id AND task_history.day_of_week <=> :day_of_week
        ORDER BY task_history.completed_at DESC LIMIT 1
    ");
    $previousStmt->execute([
        'task_id' => $taskId,
        'day_of_week' => $lastEntry['day_of_week'],
    ]);
    $previous = $previousStmt->fetch();

    $updateStmt = $pdo->prepare("
        UPDATE task_day_status
        SET last_completed = :last_completed, next_due_date = :next_due_date
        WHERE task_id = :task_id AND day_of_week <=> :day_of_week
    ");
    $updateStmt->execute([
        'last_completed' => $previous ? $previous['completed_at'] : null,
        'next_due_date' => $lastEntry['due_date'],
        'task_id' => $taskId,
        'day_of_week' => $lastEntry['day_of_week'],
    ]);

    $restoredLastCompleted = $previous ? $previous['completed_at'] : null;
    $rawStatus = classifyTaskStatus($lastEntry['due_date'], $task['frequency']);
    $displayStatus = getDisplayStatus($rawStatus, !empty($restoredLastCompleted));

    echo json_encode([
        'success' => true,
        'status' => $displayStatus,
        'completed_by_id' => $previous ? (int) $previous['user_id'] : null,
        'completed_by_name' => $previous ? $previous['display_name'] : null,
    ]);
    exit;
}
http_response_code(400);
echo json_encode(['error' => 'Invalid action']);
