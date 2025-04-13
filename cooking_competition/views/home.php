<div class="mb-5">
    <h1 class="display-4">Welcome to Cooking Competition Platform!</h1>
    <p class="lead">Join cooking competitions, share your recipes, and vote for your favorites.</p>
    <?php if (!$logged_in): ?>
        <a href="../users/login.php" class="btn btn-primary">Sign Up Now</a>
    <?php else: ?>
        <div class="action-buttons mt-3">
            <a href="index.php?page=competitions&action=create" class="btn btn-success me-2">Create Competition</a>
            <a href="index.php?page=competitions" class="btn btn-primary">All Competitions</a>
        </div>
    <?php endif; ?>

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
</div>

<?php include_once 'views/competitions/filter_buttons.php'; ?>

<?php
$competition = new Competition($conn);
$competitions = $competition->get_by_status($status, 6);

if (count($competitions) > 0):
?>
    <?php include_once 'views/competitions/grid.php'; ?>
    <div class="text-muted text-end mb-3">
        Showing frist <?= count($competitions) ?> results
    </div>
    <div class="text-center mb-4">
        <a href="index.php?page=competitions&status=<?= $status ?>" class="btn btn-outline-primary">View More</a>
    </div>
<?php else: ?>
    <div class="alert alert-info">No <?=$status ?> competitions at the moment. Check back soon!</div>
<?php endif; ?>

<h2 class="my-4">Recent Winners</h2>

<?php
$completed_competitions = $competition->get_completed(3);

if (count($completed_competitions) > 0):
?>
<div class="row">
    <?php foreach ($completed_competitions as $comp): ?>
    <div class="col-md-4 mb-4">
        <div class="card">
            <?php if (!empty($comp['image'])): ?>
            <img src="<?= htmlspecialchars($comp['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($comp['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($comp['title']) ?></h5>
                <p class="card-text">Winner: <?= htmlspecialchars($comp['winner_name'] ?? 'No winner declared') ?></p>
                <a href="index.php?page=competitions&action=view&id=<?= $comp['id'] ?>" class="btn btn-primary">View Details</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">No completed competitions yet.</div>
<?php endif; ?>
