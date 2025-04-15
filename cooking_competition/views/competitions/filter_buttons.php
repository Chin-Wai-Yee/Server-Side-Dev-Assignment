<div class="quick-nav mb-2">
    <div class="d-flex justify-content-between align-items-center">
        <h2 id="competition-status-label" class="my-3"><?=ucwords($status) ?> Competitions</h2>
        <div class="competition-filters">
            <a href="?page=<?= $currentPage ?>&status=all#competition-status-label" class="btn <?= $status == 'all' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">All</a>
            <a href="?page=<?= $currentPage ?>&status=active#competition-status-label" class="btn <?= $status == 'active' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">Active</a>
            <a href="?page=<?= $currentPage ?>&status=voting#competition-status-label" class="btn <?= $status == 'voting' ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm">Open for Voting</a>
            <a href="?page=<?= $currentPage ?>&status=upcoming#competition-status-label" class="btn <?= $status == 'upcoming' ? 'btn-info' : 'btn-outline-info' ?> btn-sm">Upcoming</a>
            <a href="?page=<?= $currentPage ?>&status=completed#competition-status-label" class="btn <?= $status == 'completed' ? 'btn-secondary' : 'btn-outline-secondary' ?> btn-sm">Completed</a>
        </div>
    </div>
</div>