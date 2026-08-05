<?php /** @var string $sortBaseUrl - full URL through all params except &sort=... */ ?>
<?php /** @var string $sort */ ?>
<div class="filter-section">
    <details class="filter-dropdown" open>
        <summary class="filter-summary">
            Sort By
            <span class="material-symbols-outlined chevron">keyboard_arrow_up</span>
        </summary>
        <div class="filter-group">
            <!-- Due Date sort placeholder — needs recurring due-date calculation -->
            <a href="<?= $sortBaseUrl ?>&sort=name" class="chip <?= $sort == 'name' ? 'chip-active' : '' ?>">Name</a>
            <a href="<?= $sortBaseUrl ?>&sort=time" class="chip <?= $sort == 'time' ? 'chip-active' : '' ?>">Time</a>
            <a href="<?= $sortBaseUrl ?>&sort=frequency" class="chip <?= $sort == 'frequency' ? 'chip-active' : '' ?>">Frequency</a>
            <a href="<?= $sortBaseUrl ?>&sort=zone" class="chip <?= $sort == 'zone' ? 'chip-active' : '' ?>">Zone</a>
        </div>
    </details>
</div>
