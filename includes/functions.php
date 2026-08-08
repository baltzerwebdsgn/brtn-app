<?php 
// Helper functions for displaying the cards with abreviations
function abbreviateDay($day) {
    $map = ['Sunday' => 'Su', 'Monday' => 'Mo', 'Tuesday' => 'Tu', 'Wednesday' => 'We', 'Thursday' => 'Th', 'Friday' => 'Fr', 'Saturday' => 'Sa'];
    return $map[$day] ?? substr($day, 0, 2);
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

//Helper function for displaying the desired sorting logic
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
        // case 'due_date': not implemented yet — no due-date calculation exists 
        // Revisit once recurring due dates are actually computed.
        case 'name':
        default:
            return $query . " ORDER BY name ASC, room ASC";
    }
}
?>