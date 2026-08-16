<?php /** @var string $page */ ?>
<?php /** @var array $currentParams */ ?>
<?php /** @var string $filter */ ?>
<?php /** @var string $zone_filter */ ?>
<?php /** @var string $assignee_filter */ ?>
<?php /** @var array $zones */ ?>
<?php /** @var array $housemates */ ?>

<?php $showAssigneeFilter = $showAssigneeFilter ?? true; ?>
<?php $showHideDone = $showHideDone ?? true; ?>

<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Frequency
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <a href="<?= taskFilterLink($page, $currentParams, ['filter' => 'All']) ?>" class="chip <?= $filter == 'All' ? 'chip-active' : '' ?>">All</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['filter' => 'Daily']) ?>" class="chip <?= $filter == 'Daily' ? 'chip-active' : '' ?>">Daily</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['filter' => 'Weekly']) ?>" class="chip <?= $filter == 'Weekly' ? 'chip-active' : '' ?>">Weekly</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['filter' => 'Monthly']) ?>" class="chip <?= $filter == 'Monthly' ? 'chip-active' : '' ?>">Monthly</a>
        </div>
    </details>
</div>
<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Zones
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <a href="<?= taskFilterLink($page, $currentParams, ['room' => 'All']) ?>" class="chip <?= $zone_filter == 'All' ? 'chip-active' : '' ?>">All</a>
            <?php foreach ($zones as $zone): ?>
                <a href="<?= taskFilterLink($page, $currentParams, ['room' => $zone['name']]) ?>" class="chip <?= $zone_filter == $zone['name'] ? 'chip-active' : '' ?>"><?= htmlspecialchars($zone['name']) ?></a>
            <?php endforeach; ?>
        </div>
    </details>
</div>
<?php if ($showAssigneeFilter): ?>
    <div class="filter-section">
        <details class="filter-dropdown" open>
            <summary class="filter-summary">
                Assignee
                <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
            </summary>
            <div class="filter-group">
                <a href="<?= taskFilterLink($page, $currentParams, ['assignee' => 'All']) ?>" class="chip <?= $assignee_filter == 'All' ? 'chip-active' : '' ?>">All</a>
                <?php foreach ($housemates as $housemate): ?>
                    <a href="<?= taskFilterLink($page, $currentParams, ['assignee' => $housemate['id']]) ?>" class="chip <?= $assignee_filter == $housemate['id'] ? 'chip-active' : '' ?>"><?= htmlspecialchars($housemate['name'] ?? $housemate['username']) ?></a>
                <?php endforeach; ?>
            </div>
        </details>
    </div>
<?php endif; ?>
<?php if ($showHideDone): ?>
    <div class="hide-done-btn">
        <a href="<?= taskFilterLink($page, $currentParams, ['hideDone' => empty($currentParams['hideDone'])]) ?>" class="chip <?= !empty($currentParams['hideDone']) ? 'chip-active' : '' ?>">Hide Done</a>
    </div>
<?php endif; ?>
