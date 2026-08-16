<?php /** @var string $page */ ?>
<?php if (in_array($page, ['home', 'breakdown', 'upcoming', 'settings'])): ?>
    <div class="navSpace"></div>
    <nav class="bottom-nav">
        <a href="index.php?page=home" class="nav-link <?= ($page == 'home' || !isset($page)) ? 'active' : '' ?>">
            <span class="navbar-icon material-symbols-outlined">home</span>
        </a>
        <a href="index.php?page=breakdown" class="nav-link <?= ($page == 'breakdown') ? 'active' : '' ?>">
            <span class="navbar-icon material-symbols-outlined">assignment</span>
        </a>
        <a href="index.php?page=settings&open=add-task" id="addButton" class="nav-link">
            <span class="material-symbols-outlined">add</span>
        </a>
        <a href="index.php?page=upcoming" class="nav-link <?= ($page == 'upcoming') ? 'active' : '' ?>">
            <span class="navbar-icon material-symbols-outlined">list_alt</span>
        </a>
        <a href="index.php?page=settings" class="nav-link <?= ($page == 'settings') ? 'active' : '' ?>">
            <span class="navbar-icon material-symbols-outlined">settings</span>
        </a>
    </nav>
<?php endif; ?>
<script src="includes/main.js"></script>
</body>
</html>