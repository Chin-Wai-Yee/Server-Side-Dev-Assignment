<div class="quick-nav mb-2">
    <div class="d-flex justify-content-between align-items-center">
        <?php $status = isset($_GET['status']) ? $_GET['status'] : "all"; ?>
        <h2 class="my-3"><?=ucwords($status) ?> Competitions</h2>
        <div class="competition-filters">
            <?php 
            // Get all current GET parameters and create URLs that preserve them
            $params = $_GET;
            
            function getFilterUrl($newStatus) {
                $params = $_GET;
                $params['status'] = $newStatus;
                return '?' . http_build_query($params);
            }
            ?>
            <a href="<?= getFilterUrl('all') ?>" class="btn <?= $status == 'all' ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">All</a>
            <a href="<?= getFilterUrl('active') ?>" class="btn <?= $status == 'active' ? 'btn-success' : 'btn-outline-success' ?> btn-sm">Active</a>
            <a href="<?= getFilterUrl('voting') ?>" class="btn <?= $status == 'voting' ? 'btn-warning' : 'btn-outline-warning' ?> btn-sm">Open for Voting</a>
            <a href="<?= getFilterUrl('upcoming') ?>" class="btn <?= $status == 'upcoming' ? 'btn-info' : 'btn-outline-info' ?> btn-sm">Upcoming</a>
            <a href="<?= getFilterUrl('completed') ?>" class="btn <?= $status == 'completed' ? 'btn-secondary' : 'btn-outline-secondary' ?> btn-sm">Completed</a>
        </div>
    </div>
</div>