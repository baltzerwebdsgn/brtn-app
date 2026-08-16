<?php /** @var string $closeHref */ ?>
<?php /** @var string $closeLabel — defaults to "Cancel" */ ?>
<a href="<?= htmlspecialchars($closeHref) ?>" class="btn-close" aria-label="<?= htmlspecialchars($closeLabel ?? 'Cancel') ?>">
    <span class="material-symbols-outlined">close</span>
</a>
