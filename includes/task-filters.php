<?php /** @var string $page */ ?>
<?php /** @var array $currentParams */ ?>
<?php /** @var string $filter */ ?>
<?php /** @var string $zone_filter */ ?>
<?php /** @var string $assignee_filter */ ?>
<?php /** @var array $zones */ ?>
<?php /** @var array $housemates */ ?>
<?php /** @var string $statusFilter */ ?>

<?php $showZoneFilter = $showZoneFilter ?? true; ?>
<?php $showAssigneeFilter = $showAssigneeFilter ?? true; ?>
<?php $showStatusFilter = $showStatusFilter ?? true; ?>
<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            <div class="filter-title">
                <span class="material-symbols-outlined filter-icon">
                filter_list
                </span>
                <h2 class="filter-text">Filters</h2>
            </div>
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-section">
            <details class="filter-dropdown" open>
                <summary class="filter-summary filters">
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
        <?php if ($showZoneFilter): ?>
            <div class="filter-section">
                <details class="filter-dropdown" open>
                    <summary class="filter-summary filters">
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
        <?php endif; ?>
        <?php if ($showAssigneeFilter): ?>
            <div class="filter-section">
                <details class="filter-dropdown" open>
                    <summary class="filter-summary filters">
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
        <?php if ($showStatusFilter): ?>
            <div class="filter-section">
                <details class="filter-dropdown" open>
                    <summary class="filter-summary filters">
                        Status
                        <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
                    </summary>
                    <div class="filter-group">
                        <a href="<?= taskFilterLink($page, $currentParams, ['status' => 'All']) ?>" class="chip <?= $statusFilter == 'All' ? 'chip-active' : '' ?>">All</a>
                        <a href="<?= taskFilterLink($page, $currentParams, ['status' => 'Due']) ?>" class="chip <?= $statusFilter == 'Due' ? 'chip-active' : '' ?>">Due</a>
                        <a href="<?= taskFilterLink($page, $currentParams, ['status' => 'Overdue']) ?>" class="chip <?= $statusFilter == 'Overdue' ? 'chip-active' : '' ?>">Overdue</a>
                        <a href="<?= taskFilterLink($page, $currentParams, ['status' => 'Soon']) ?>" class="chip <?= $statusFilter == 'Soon' ? 'chip-active' : '' ?>">Soon</a>
                        <a href="<?= taskFilterLink($page, $currentParams, ['status' => 'Done']) ?>" class="chip <?= $statusFilter == 'Done' ? 'chip-active' : '' ?>">Done</a>
                    </div>
                </details>
            </div>
        <?php endif; ?>
    </details>
</div>

