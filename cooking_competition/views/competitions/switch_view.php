<div class="view-toggle text-end mb-2">
    <?php
    function getViewUrl($newView) {
        $params = $_GET;
        $params['view'] = $newView;
        return '?' . http_build_query($params);
    }
    $currentView = isset($_COOKIE['competition_view']) ? $_COOKIE['competition_view'] : 'grid';
    if (isset($_GET['view'])) {
        $currentView = $_GET['view'];
        setcookie('competition_view', $currentView, time() + (86400 * 30), '/'); // Cookie expires in 30 days
    }
    ?>
    <div class="btn-group" role="group">
        <a href="<?= getViewUrl('grid') ?>" class="btn btn-sm <?= $currentView == 'grid' ? 'btn-dark' : 'btn-outline-dark' ?>">
            <i class="fas fa-th"></i> Grid
        </a>
        <a href="<?= getViewUrl('list') ?>" class="btn btn-sm <?= $currentView == 'list' ? 'btn-dark' : 'btn-outline-dark' ?>">
            <i class="fas fa-list"></i> List
        </a>
    </div>
</div>