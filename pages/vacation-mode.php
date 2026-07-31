<?php
$from = $_GET['from'] ?? 'settings';
?>
<div class="setting-subpage-title">
    <a href="index.php?page=<?= htmlspecialchars($from) ?>">&larr;</a>
    <h1>Vacation Mode</h1>
</div>
<p class="vac-info">
    Pause every task for the whole household while you're away. 
    By default, due dates push out to the day after your return date 
    — check the box below to have tasks start again right on your return
     date instead.
</p>
<div class="task-card"> 
    <form>
        <div class="form-group">
            <label class="subheading">Start date</label>
            <input type="date" class="date">
        </div>
        <div class="form-group"> 
            <label class="subheading">Return date</label>
            <input type="date" class="date">
        </div>
        <div class="form-group vac-iTasks">
            <input type="checkbox">
            <p>Start tasks again on the return date (instead of the day after)</p>
        </div>
        <button type="submit" class="btn-primary active" id="vac-md-btn">Start Vacation Mode</button>
    </form>
</div>
