<?php /** @var string $page */ ?>
<?php if (!in_array($page, ['login', 'register'])): ?>
    <nav class="bottom-nav">
        <a href="index.php?page=home" class="nav-link <?= ($page == 'home' || !isset($page)) ? 'active' : '' ?>">
            <span class="material-symbols-outlined">home</span>
        </a>
        <a href="index.php?page=breakdown" class="nav-link <?= ($page == 'breakdown') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">assignment</span>
        </a>
        <button id="addButton" onclick="window.location.href='index.php?page=add'">
            <span class="material-symbols-outlined">add</span>
        </button>
        <a href="index.php?page=upcoming" class="nav-link <?= ($page == 'upcoming') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">list_alt</span>
        </a>
        <a href="index.php?page=settings" class="nav-link <?= ($page == 'settings') ? 'active' : '' ?>">
            <span class="material-symbols-outlined">settings</span>
        </a>
    </nav>
<?php endif; ?>
</body>
</html>