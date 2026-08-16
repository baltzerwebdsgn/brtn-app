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
<div class="modal-overlay" id="task-info-overlay">
    <div class="modal-card">
        <div class="modal-header">
            <h2 class="modal-title"></h2>
            <button type="button" id="task-info-close" class="btn-close" data-close-target="#task-info-overlay" aria-label="Close">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div class="modal-stats">
            <div class="modal-frequency-section">
                <div class="modal-stat-label">Frequency</div>
                <div class="modal-frequency"></div>
                <em><div class="modal-frequency-detail"></div></em>
            </div>
            <div class="modal-time-section">
                <div class="modal-stat-label">Completion Time</div>
                <div class="modal-time"></div>
            </div>
        </div>
        <div class="modal-stat-label modal-description-label">Description</div>
        <p class="modal-description"></p>
    </div>
</div>

<script src="includes/main.js"></script>
</body>
</html>