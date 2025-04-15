<div class="view-toggle text-end mb-2">
    <div class="btn-group" role="group">
        <a href="?page=<?=$currentPage ?>&view=grid" class="btn btn-sm <?= $currentView == 'grid' ? 'btn-dark' : 'btn-outline-dark' ?>">
            <i class="fas fa-th"></i> Grid
        </a>
        <a href="?page=<?=$currentPage ?>&view=list" class="btn btn-sm <?= $currentView == 'list' ? 'btn-dark' : 'btn-outline-dark' ?>">
            <i class="fas fa-list"></i> List
        </a>
    </div>
</div>