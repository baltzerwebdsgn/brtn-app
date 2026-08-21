// ---- Clear error state on input ----
// Removed the input's error border upon changing field info
document.querySelectorAll('.input-error').forEach(function (field){
    field.addEventListener('input', function () {
        field.classList.remove('input-error');
    });
});
// ---- Completed all tasks for the day state ----
function updateTodoEmptyState() {
    var todoSection = document.getElementById('todo-tasks-list');
    var emptyState = document.getElementById('todo-empty-state');
    if (!todoSection || !emptyState) return;
    emptyState.style.display = todoSection.children.length === 0 ? 'block' : 'none';
}

// ---- Color-code upcoming tasks by due date ----
// Change the text color in the upcoming page to fit the due date condition
document.querySelectorAll('.task-meta-last[data-next-date]').forEach(function (el) {
    var nextDate = el.dataset.nextDate;
    if (!nextDate) return;

    var today = new Date();
    today.setHours(0, 0, 0, 0);
    var next = new Date(nextDate + 'T00:00:00');

    el.classList.add(next >= today ? 'on-track' : 'overdue');
});

// ---- Deep-link to the Add/Edit Task section ----
// Scrolls to the Add a task modal if user clicks to edit a task
var params = new URLSearchParams(window.location.search);
if (params.has('edit_task') || params.get('open') === 'add-task') {
    var target = document.getElementById('add-a-task');
    if (target) target.scrollIntoView();
}

// ---- Task info modal (open + close) ----
// Opens and populates the task-info overlay from the clicked icon's data-* attributes
document.querySelectorAll('.info-icon[data-task-name]').forEach(function (icon) {
    icon.addEventListener('click', function () {
        document.querySelector('.modal-title').textContent = this.dataset.taskName;
        document.querySelector('.modal-frequency').textContent = this.dataset.taskFrequency;
        document.querySelector('.modal-frequency-detail').textContent = this.dataset.taskFrequencyDetail;
        document.querySelector('.modal-time').textContent = this.dataset.taskTime;
        document.querySelector('.modal-description').textContent = this.dataset.taskDescription;
        document.body.classList.add('modal-open');
    });
});

// Generic close handler — works for any [data-close-target], not just the task-info
// modal, so it'll cover future overlays without needing a new listener.
document.querySelectorAll('.btn-close[data-close-target]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var target = document.querySelector(this.dataset.closeTarget);
        if (target.classList.contains('modal-overlay')) {
            document.body.classList.remove('modal-open');
        } else {
            target.classList.add('hidden');
        }
    });
});

// ---- Toggle frequency-specific form fields (day-of-week / week-of-month) ----
// Displays corresponding section based off the frequency chosen
var daySection = document.getElementById('day-of-week-section');
var weekSection = document.getElementById('week-of-month-section');

if (daySection && weekSection) {
    function updateFrequencyVisibility() {
        var checkedFrequency = document.querySelector('input[name="frequency"]:checked');
        var value = checkedFrequency ? checkedFrequency.value : null;

        daySection.style.display = (value === 'weekly') ? 'block' : 'none';
        weekSection.style.display = (value === 'monthly') ? 'block' : 'none';
    }

    document.querySelectorAll('input[name="frequency"]').forEach(function (radio) {
        radio.addEventListener('change', updateFrequencyVisibility);
    });

    updateFrequencyVisibility();
}

// ---- Reassign a task to a different housemate ----
// Task rows are reassigned via a hidden radio per housemate (one per avatar),
// rather than drag-and-drop, so this just reacts to the radio changing.
document.querySelectorAll('input[name^="assignment["]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        if (!this.checked) return;

        var taskRow = this.closest('.assign-border');
        var frequency = taskRow.dataset.frequency;
        var newHousemateId = this.value;

        var oldList = taskRow.closest('.assign-list');
        var oldHousemateId = oldList.id.replace('housemate-list-', '');
        var newList = document.getElementById('housemate-list-' + newHousemateId);

        if (oldList === newList) return;

        newList.appendChild(taskRow);
        adjustCount(oldHousemateId, frequency, -1);
        adjustCount(newHousemateId, frequency, 1);
        updatePreviouslyNote(taskRow, newHousemateId);
        updatePreviousAvatar(taskRow, newHousemateId);
    });
});

// Highlights the original assignee's avatar only when the task has been moved away
// from them — cleared entirely once it's moved back.
function updatePreviousAvatar(taskRow, newHousemateId) {
    var originalId = taskRow.dataset.originalAssigneeId;

    taskRow.querySelectorAll('.assign-avatars label').forEach(function (label) {
        label.classList.remove('avatar-previous');
    });

    if (newHousemateId !== originalId) {
        var radios = taskRow.querySelectorAll('.assign-avatars input[type="radio"]');
        radios.forEach(function (radio) {
            if (radio.value === originalId) {
                var label = taskRow.querySelector('label[for="' + radio.id + '"]');
                if (label) label.classList.add('avatar-previous');
            }
        });
    }
}

// Keeps each housemate's task-count header in sync as tasks move between them.
function adjustCount(housemateId, frequency, delta) {
    var header = document.getElementById('housemate-header-' + housemateId);
    if (!header) return;

    var totalEl = header.querySelector('.total-count');
    totalEl.textContent = parseInt(totalEl.textContent, 10) + delta;

    var freqEl = header.querySelector('.' + frequency + '-count');
    if (freqEl) {
        freqEl.textContent = parseInt(freqEl.textContent, 10) + delta;
    }
}

function updatePreviouslyNote(taskRow, newHousemateId) {
    var previousEl = taskRow.querySelector('.assign-previous');
    var originalId = taskRow.dataset.originalAssigneeId;
    var originalName = taskRow.dataset.originalAssigneeName;

    if (newHousemateId === originalId) {
        previousEl.style.display = 'none';
        previousEl.textContent = '';
    } else {
        previousEl.textContent = 'Previously: ' + originalName;
        previousEl.style.display = 'block';
    }
}

// Keeps a Home "My Zones" card's count/progress bar in sync after a complete/undo,
// without needing a full page reload. Only the "completed" numerator moves here —
// whether a task still counts as "due this week" at all can also shift (e.g. a
// Monthly task rolling out of this week's window), but that needs server data this
// response doesn't return, so a full reload stays the source of truth for that case.
function updateZoneCard(room, delta) {
    var zoneCard = document.querySelector('.zone-card[data-zone-name="' + CSS.escape(room) + '"]');
    if (!zoneCard) return;

    var total = parseInt(zoneCard.dataset.zoneTotal, 10);
    var completed = parseInt(zoneCard.dataset.zoneCompleted, 10) + delta;
    completed = Math.max(0, Math.min(total, completed));
    zoneCard.dataset.zoneCompleted = completed;

    var countEl = zoneCard.querySelector('.zone-task-count');
    if (countEl) {
        if (total === 0) {
            countEl.textContent = '0 Tasks';
        } else if (total === 1) {
            countEl.textContent = '1 Task';
        } else {
            countEl.textContent = completed + '/' + total + ' Tasks';
        }
    }

    var fill = zoneCard.querySelector('.zone-progress-fill');
    if (fill) {
        var progress = total > 0 ? Math.round((completed / total) * 100) : 0;
        fill.style.width = progress + '%';
    }
}

// ---- Mark a task done/undone (optimistic UI + server sync) ----
document.querySelectorAll('.task-status-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var taskId = this.dataset.taskId;
        var isDone = this.classList.contains('is-done');
        var action = isDone ? 'undo' : 'complete';
        var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        var formData = new FormData();
        formData.append('task_id', taskId);
        formData.append('task_action', action);
        formData.append('csrf_token', csrfToken);

        fetch('actions/task-completion.php', {
            method: 'POST',
            body: formData,
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) return;

                var card = btn.closest('.task-card');
                var statusInfo = card.querySelector('.task-status-info');
                var icon = btn.querySelector('.material-symbols-outlined');
                var completedByNote = card.querySelector('.completed-by-note');
                var assignedTo = card.dataset.assignedTo;

                if (completedByNote) {
                    if (data.status === 'done' && data.completed_by_id && String(data.completed_by_id) !== assignedTo) {
                        completedByNote.textContent = 'Completed by ' + data.completed_by_name;
                        completedByNote.style.display = 'block';
                    } else {
                        completedByNote.textContent = '';
                        completedByNote.style.display = 'none';
                    }
                }

                statusInfo.classList.remove('due', 'overdue', 'soon', 'done');
                statusInfo.classList.add(data.status);
                statusInfo.textContent = data.status.charAt(0).toUpperCase() + data.status.slice(1);

                if (data.status === 'done') {
                    btn.classList.add('is-done');
                    btn.classList.remove('soon');
                    btn.disabled = false;
                    icon.textContent = 'undo';

                    var completedSection = document.getElementById('completed-tasks-list');
                    if (completedSection) completedSection.appendChild(card);
                } else if (data.status === 'soon') {
                    // "soon" = task was just completed but its next occurrence isn't
                    // due yet — button is disabled until then.
                    btn.classList.remove('is-done');
                    btn.classList.add('soon');
                    btn.disabled = true;
                    btn.removeAttribute('data-task-id');
                    icon.textContent = '';

                    var todoSectionSoon = document.getElementById('todo-tasks-list');
                    if (todoSectionSoon) todoSectionSoon.appendChild(card);
                } else {
                    btn.classList.remove('is-done', 'soon');
                    btn.disabled = false;
                    icon.textContent = 'check_small';

                    var todoSectionSameDay = document.getElementById('todo-tasks-list');
                    if (todoSectionSameDay) todoSectionSameDay.appendChild(card);
                }
                var wasDone = isDone;
                var isNowDone = data.status === 'done';
                var zoneDelta = 0;
                if (!wasDone && isNowDone) {
                    zoneDelta = 1;
                } else if (wasDone && !isNowDone) {
                    zoneDelta = -1;
                }
                updateZoneCard(btn.dataset.room, zoneDelta);
                updateTodoEmptyState();
            });
    });
});
