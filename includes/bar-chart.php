<?php /** @var array $barChartData — associative label => count */ ?>
<?php
$barChartMax = !empty($barChartData) ? max($barChartData) : 0;
?>
<div class="metrics-bar-chart">
    <?php foreach ($barChartData as $label => $value): ?>
        <?php $barHeight = $barChartMax > 0 ? round(($value / $barChartMax) * 100) : 0; ?>
        <div class="metrics-bar-item">
            <span class="metrics-bar-count"><?= $value ?></span>
            <div class="metrics-bar-track">
                <div class="metrics-bar-vertical-fill" style="height: <?= $barHeight ?>%;"></div>
            </div>
            <span class="metrics-bar-label"><?= htmlspecialchars($label) ?></span>
        </div>
    <?php endforeach; ?>
</div>
