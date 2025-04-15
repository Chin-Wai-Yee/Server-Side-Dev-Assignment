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

    <div class="mb-3">
        <a href="index.php" class="btn btn-outline-secondary">← Back to Home</a>
    </div>
    
    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] == 'admin'): ?>
        <div class="mb-3">
            <a href="index.php?page=competitions&action=create" class="btn btn-primary">Create New Competition</a>
        </div>
    <?php endif; ?>
    
    <?php include_once 'filter_buttons.php'; ?>
    <?php include_once 'switch_view.php'; ?>

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