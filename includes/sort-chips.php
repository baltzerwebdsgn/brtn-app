<?php /** @var string $page */ ?>
<?php /** @var array $currentParams */ ?>
<?php /** @var string $sort */ ?>
<?php 
$showDateSort = $showDateSort ?? false; 
$showStatusSort = $showStatusSort ?? false;
?>
<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary sort-by">
            <div class="filter-title" id="filter-sort">
                <span class="material-symbols-outlined filter-icon">
                sort
                </span>
                <h2 class="filter-text">Sort By</h2>
            </div>
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <?php if ($showStatusSort): ?>
                <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'status']) ?>" class="chip <?= $sort == 'status' ? 'chip-active' : '' ?>">Status</a>
            <?php endif; ?>
            <?php if ($showDateSort): ?>
                <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'date']) ?>" class="chip <?= $sort == 'date' ? 'chip-active' : '' ?>">Date</a>
            <?php endif; ?>
            <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'title']) ?>" class="chip <?= $sort == 'title' ? 'chip-active' : '' ?>">Title</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'time']) ?>" class="chip <?= $sort == 'time' ? 'chip-active' : '' ?>">Time</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'frequency']) ?>" class="chip <?= $sort == 'frequency' ? 'chip-active' : '' ?>">Frequency</a>
            <a href="<?= taskFilterLink($page, $currentParams, ['sort' => 'zone']) ?>" class="chip <?= $sort == 'zone' ? 'chip-active' : '' ?>">Zones</a>
        </div>
    </details>
</div>
