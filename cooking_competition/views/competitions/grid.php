<div class="row">
    <?php foreach ($competitions as $comp): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php if (!empty($comp['image'])): ?>
                    <img src="<?= htmlspecialchars($comp['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($comp['title']) ?>">
                <?php else: ?>
                    <img src="assets\images\default_competition.png" class="card-img-top" alt="Default competition image">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($comp['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(substr($comp['description'], 0, 100)) ?>...</p>
                    <div class="mb-2">
                        <span class="badge bg-<?= $comp['status'] == 'active' ? 'success' : 'warning' ?>"><?= ucfirst($comp['status']) ?></span>
                    </div>
                    <p class="card-text">
                        <small class="text-muted">
                        <?php if ($comp['status'] == 'active'): ?>
                            Submission deadline: <?= date('M d, Y', strtotime($comp['end_date'])) ?>
                        <?php elseif ($comp['status'] == 'voting'): ?>
                            Voting ends: <?= date('M d, Y', strtotime($comp['voting_end_date'])) ?>
                        <?php endif; ?>
                        </small>
                    </p>
                    <div class="d-flex justify-content-between">
                        <a href="index.php?page=competitions&action=view&id=<?= $comp['id'] ?>" class="btn btn-primary">View Details</a>
                        <?php if ($comp['status'] == 'active'): ?>
                        <a href="index.php?page=recipes&action=submit&competition_id=<?= $comp['id'] ?>" class="btn btn-success">Submit Recipe</a>
                        <?php elseif ($comp['status'] == 'voting'): ?>
                        <a href="index.php?page=votes&action=view&competition_id=<?= $comp['id'] ?>" class="btn btn-warning">Vote Now</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small><?= $comp['recipe_count'] ?> recipes submitted</small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>