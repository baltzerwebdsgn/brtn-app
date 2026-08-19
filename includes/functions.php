<?php

// ---- Display formatting (labels, abbreviations, relative dates) ----

function abbreviateDay($day) {
    $map = ['Sunday' => 'Su', 'Monday' => 'Mo', 'Tuesday' => 'Tu', 'Wednesday' => 'We', 'Thursday' => 'Th', 'Friday' => 'Fr', 'Saturday' => 'Sa'];
    return $map[$day] ?? substr($day, 0, 2);
}
function formatFrequencyDetailExpanded($frequency, $dayOfWeek, $weekOfMonth) {
    $frequency = strtolower($frequency);
    $order = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    if ($frequency === 'monthly' && $weekOfMonth) {
        return 'Every ' . formatWeekOfMonth($weekOfMonth) . ' week of the month';
    }

    if ($frequency === 'weekly' && $dayOfWeek) {
        $days = explode(',', $dayOfWeek);
        $indexes = array_map(function ($day) use ($order) {
            return array_search($day, $order);
        }, $days);
        sort($indexes);
        $count = count($indexes);

        if ($count === 1) {
            return 'Every ' . $order[$indexes[0]];
        }

        $indexSet = array_flip($indexes);
        $consecutiveStart = null;
        for ($start = 0; $start < 7; $start++) {
            $isMatch = true;
            for ($i = 0; $i < $count; $i++) {
                if (!isset($indexSet[($start + $i) % 7])) {
                    $isMatch = false;
                    break;
                }
            }
            if ($isMatch) {
                $consecutiveStart = $start;
                break;
            }
        }

        if ($consecutiveStart !== null) {
            $endIndex = ($consecutiveStart + $count - 1) % 7;
            return 'Every ' . $order[$consecutiveStart] . ' thru ' . $order[$endIndex];
        }

        $dayNames = array_map(function ($i) use ($order) { return $order[$i]; }, $indexes);
        $last = array_pop($dayNames);
        return 'Every ' . implode(', ', $dayNames) . ' & ' . $last;
    }

    return null;
}

function formatWeekOfMonth($week) {
    if (is_numeric($week)) {
        $suffixes = [1 => 'st', 2 => 'nd', 3 => 'rd', 4 => 'th'];
        return $week . ($suffixes[(int) $week] ?? 'th');
    }
    return $week;
}

function formatFrequencyDetail($frequency, $dayOfWeek, $weekOfMonth) {
    $label = ucfirst(strtolower($frequency));

    if ($label === 'Weekly' && $dayOfWeek) {
        $days = explode(',', $dayOfWeek);
        $abbreviations = array_map('abbreviateDay', $days);
        return $label . ' · (' . implode('/', $abbreviations) . ')';
    }

    if ($label === 'Monthly' && $weekOfMonth) {
        return $label . ' · (' . formatWeekOfMonth($weekOfMonth) . ')';
    }

    return $label;
}
function formatFrequencyDetailHtml($frequency, $dayOfWeek, $weekOfMonth) {
    $detail = formatFrequencyDetail($frequency, $dayOfWeek, $weekOfMonth);

    if (preg_match('/^([^(]*)(\(.*\))$/', $detail, $matches)) {
        return htmlspecialchars($matches[1]) . '<em>' . htmlspecialchars($matches[2]) . '</em>';
    }

    return htmlspecialchars($detail);
}

function formatRelativeDate($datetime) {
    if (empty($datetime)) {
        return 'Not Yet';
    }

    $date = new DateTime((new DateTime($datetime))->format('Y-m-d'));
    $today = new DateTime('today');
    $diffDays = (int) $today->diff($date)->format('%r%a');

    if ($diffDays === 0) {
        return 'Today';
    }
    if ($diffDays === -1) {
        return 'Yesterday';
    }
    if ($diffDays < 0) {
        return abs($diffDays) . ' Days Ago';
    }

    return $date->format('m-d-Y');
}

// ---- Sorting & filtering (query building) ----

function applySortOrder($query, $sort) {
    switch ($sort) {
        case 'time':
            return $query . " ORDER BY total_time ASC, name ASC, room ASC";
        case 'zone':
            return $query . " ORDER BY room ASC, name ASC";
        case 'frequency':
            return $query . "
                ORDER BY
                    CASE LOWER(frequency)
                        WHEN 'daily' THEN 1
                        WHEN 'weekly' THEN 2
                        WHEN 'monthly' THEN 3
                        ELSE 4
                    END,
                    CASE SUBSTRING_INDEX(day_of_week, ',', 1)
                        WHEN 'Sunday' THEN 1 WHEN 'Monday' THEN 2 WHEN 'Tuesday' THEN 3
                        WHEN 'Wednesday' THEN 4 WHEN 'Thursday' THEN 5 WHEN 'Friday' THEN 6
                        WHEN 'Saturday' THEN 7 ELSE 8
                    END,
                    CAST(week_of_month AS UNSIGNED),
                    name ASC,
                    room ASC
            ";
        // 'date' isn't handled here — next_due_date lives in task_day_status, not
        // this query, so date sorting happens in PHP after the per-task status
        // loop runs (see breakdown.php/upcoming.php). Falls through to default.

        case 'name':
        default:
            return $query . " ORDER BY name ASC, room ASC";
    }
}

// Builds a page URL that carries the current filter/room/sort/assignee state forward,
// with individual values overridable — used so links (e.g. "clear filter") only change
// the one param they mean to, instead of resetting the whole query string.
function taskFilterLink($page, $current, $overrides = []) {
    $params = array_merge($current, $overrides);
    $query = ['page' => $page];

    if (!empty($params['from'])) {
        $query['from'] = $params['from'];
    }
    $query['filter'] = $params['filter'] ?? 'All';
    $query['room'] = $params['room'] ?? 'All';
    $query['sort'] = $params['sort'] ?? 'name';
    if (isset($params['assignee'])) {
        $query['assignee'] = $params['assignee'];
    }
    if (isset($params['id'])) {
        $query['id'] = $params['id'];
    }
    $query['status'] = $params['status'] ?? 'All';

    return 'index.php?' . http_build_query($query);
}


// ---- Due-date scheduling ----
function wasCompletedToday($lastCompleted) {
    return !empty($lastCompleted) && substr($lastCompleted, 0, 10) === date('Y-m-d');
}

function computeNextDueDate($frequency, $dayOfWeek, $weekOfMonth, $fromDate) {
    $from = new DateTime($fromDate);
    $frequency = strtolower($frequency);

    if ($frequency === 'daily') {
        $from->modify('+1 day');
        return $from->format('Y-m-d');
    }

    if ($frequency === 'weekly') {
        $next = clone $from;
        $next->modify('next ' . strtolower($dayOfWeek));
        return $next->format('Y-m-d');
    }

    if ($frequency === 'monthly') {
        $week = (int) $weekOfMonth;
        $year = (int) $from->format('Y');
        $month = (int) $from->format('n');

        // Finds the Nth 7-day window of the month, where the first window starts on
        // the month's first Sunday. If that window has already passed $from, rolls
        // forward to the same week-of-month in the following month.
        while (true) {
            $firstOfMonth = new DateTime(sprintf('%04d-%02d-01', $year, $month));
            $dow = (int) $firstOfMonth->format('w'); // 0 = Sunday
            $daysToFirstSunday = ($dow === 0) ? 0 : (7 - $dow);
            $windowStart = (clone $firstOfMonth)
                ->modify("+{$daysToFirstSunday} days")
                ->modify('+' . (($week - 1) * 7) . ' days');

            if ($windowStart > $from) {
                return $windowStart->format('Y-m-d');
            }

            $month++;
            if ($month > 12) { $month = 1; $year++; }
        }
    }

    return null;
}

function createTaskDayStatusRows($pdo, $taskId, $frequency, $dayOfWeek, $weekOfMonth) {
    $frequency = strtolower($frequency);
    $days = ($frequency === 'weekly' && $dayOfWeek) ? explode(',', $dayOfWeek) : [null];

    $insert = $pdo->prepare("INSERT INTO task_day_status (task_id, day_of_week, last_completed, next_due_date) VALUES (:task_id, :day_of_week, NULL, :next_due_date)");
    $today = date('Y-m-d');

    foreach ($days as $day) {
        if ($frequency === 'monthly') {
            $week = (int) $weekOfMonth;
            $year = (int) date('Y');
            $month = (int) date('n');

            // Same Nth-week-of-month window logic as computeNextDueDate(), but seeded
            // from today rather than a prior due date — this only runs once, when the
            // task is first created, to find its first-ever due window.
            while (true) {
                $firstOfMonth = new DateTime(sprintf('%04d-%02d-01', $year, $month));
                $dow = (int) $firstOfMonth->format('w');
                $daysToFirstSunday = ($dow === 0) ? 0 : (7 - $dow);
                $windowStart = (clone $firstOfMonth)->modify("+{$daysToFirstSunday} days")->modify('+' . (($week - 1) * 7) . ' days');
                $windowEnd = (clone $windowStart)->modify('+6 days');

                if ($windowEnd->format('Y-m-d') >= $today) {
                    $nextDue = $windowStart->format('Y-m-d');
                    break;
                }
                $month++;
                if ($month > 12) { $month = 1; $year++; }
            }
        } else {
            // Seed from "yesterday" so a daily/weekly task can become due today,
            // rather than always starting one full cycle out.
            $yesterday = (new DateTime($today))->modify('-1 day')->format('Y-m-d');
            $nextDue = computeNextDueDate($frequency, $day, $weekOfMonth, $yesterday);
        }

        $insert->execute([
            'task_id' => $taskId,
            'day_of_week' => $day,
            'next_due_date' => $nextDue,
        ]);
    }
}

// ---- Task status (idle / due / overdue / done) ----

// Monthly tasks stay "due" for their full 7-day window; daily/weekly tasks are only
// "due" on their exact next_due_date.
function classifyTaskStatus($nextDueDate, $frequency, $today = null) {
    if ($nextDueDate === null) {
        return 'idle';
    }

    $today = $today ?? date('Y-m-d');
    $frequency = strtolower($frequency);

    $windowStart = $nextDueDate;
    $windowEnd = $nextDueDate;

    if ($frequency === 'monthly') {
        $end = new DateTime($nextDueDate);
        $end->modify('+6 days');
        $windowEnd = $end->format('Y-m-d');
    }

    if ($today < $windowStart) {
        return 'idle';
    }
    if ($today > $windowEnd) {
        return 'overdue';
    }
    return 'due';
}

function getDisplayStatus($rawStatus, $hasBeenCompleted) {
    if ($rawStatus === 'overdue') {
        return 'overdue';
    }
    if ($rawStatus === 'due') {
        return 'due';
    }
    return $hasBeenCompleted ? 'done' : 'soon';
}

// <=> (not =) in the join below so it still matches when day_of_week is NULL (daily tasks)
function getTaskStatus($pdo, $taskId, $frequency) {
    $stmt = $pdo->prepare("
        SELECT
            task_day_status.id,
            task_day_status.day_of_week,
            task_day_status.last_completed,
            task_day_status.next_due_date,
            task_history.user_id AS completed_by_id,
            COALESCE(completed_by_user.name, completed_by_user.username) AS completed_by_name
        FROM task_day_status
        LEFT JOIN task_history
            ON task_history.task_id = task_day_status.task_id
            AND task_history.day_of_week <=> task_day_status.day_of_week
            AND task_history.completed_at = task_day_status.last_completed
        LEFT JOIN users AS completed_by_user ON task_history.user_id = completed_by_user.id
        WHERE task_day_status.task_id = :task_id
        ORDER BY (task_day_status.next_due_date IS NULL), task_day_status.next_due_date ASC
        LIMIT 1
    ");
    $stmt->execute(['task_id' => $taskId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $row['status'] = classifyTaskStatus($row['next_due_date'], $frequency);
    return $row;
}
// ---- Try to match zone name with corresponding icon ----
function matchZoneIcon($name) {
    $availableIcons = [
        'basement', 'bathroom', 'bedroom', 'bedroom-alt-1', 'bedroom-alt-2',
        'cat-tree', 'closet', 'dining', 'garden', 'hallway', 'houseplant',
        'kitchen', 'kitchen-alt-1', 'laundry', 'living-room', 'nursery',
        'office', 'outside', 'storage', 'toilet', 'trash', 'trees',
    ];

    $slug = strtolower(trim($name));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');

    if (in_array($slug, $availableIcons, true)) {
        return $slug;
    }

    $singleWordIcons = ['basement', 'bathroom', 'bedroom', 'closet', 'dining', 'garden', 'hallway', 'houseplant', 'kitchen', 'laundry', 'nursery', 'office', 'outside', 'storage', 'toilet', 'trash', 'trees'];
    $words = explode('-', $slug);
    foreach ($singleWordIcons as $icon) {
        if (in_array($icon, $words, true)) {
            return $icon;
        }
    }

    return null;
}
