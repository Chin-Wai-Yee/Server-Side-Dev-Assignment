<div class="container">
    <h1>Cooking Competitions</h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['error']; ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="row mb-3 action-buttons d-flex align-items-center">
        <div class="col-auto me-auto">
            <a href="index.php" class="btn btn-outline-secondary">← Back to Home</a>
        </div>
        <div class="col-auto">
            <?php if (!$logged_in): ?>
                <a href="../users/login.php" class="btn btn-primary">Sign Up Now</a>
            <?php else: ?>
                <a href="index.php?page=competitions&action=create" class="btn btn-success">+ Create Competition</a>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
    $status = $_GET['status'] ?? 'all';
    $currentView = 'grid';
    if (isset($_GET['view'])) {
        $currentView = $_GET['view'];
        setcookie('competition_view', $currentView, time() + (86400 * 30), "/");
    } elseif (isset($_COOKIE['competition_view'])) {
        $currentView = $_COOKIE['competition_view'];
    } else {
        $_COOKIE['competition_view'] = $currentView;
    }
    $currentPage = 'competitions';
    ?>
    <?php include_once 'filter_buttons.php'; ?>

    <div class="row">
        <div class="col-md-8">
            <form action="index.php" method="GET" class="mb-3">
                <input type="hidden" name="page" value="competitions">
                <input type="hidden" name="action" value="search">
                <?php if (isset($_GET['status'])): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($_GET['status']) ?>">
                <?php endif; ?>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search competitions..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
        <div class="col-md-4 d-flex justify-content-end align-items-center">
            <?php include_once 'switch_view.php'; ?>
        </div>
    </div>

    <?php if (empty($competitions)): ?>
        <div class="alert alert-primary" role="alert">
            No competitions available at this time.
        </div>
    <?php else:

        if ($currentView == 'grid') {
            include_once 'grid.php';
        } else {
            // Default to list view
            include_once 'list.php';
        }

    endif; ?>
</div>