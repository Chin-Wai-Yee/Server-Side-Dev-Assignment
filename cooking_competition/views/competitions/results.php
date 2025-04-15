<div class="mb-3">
    <a href="index.php?page=competitions&action=view&id=<?= $competition['id'] ?>" class="btn btn-outline-secondary">← Back to Competition</a>
</div>

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h2>Competition Results: <?= htmlspecialchars($competition['title']) ?></h2>
    </div>
    <div class="card-body">
        <p>Competition Period: <?= date('M d, Y', strtotime($competition['start_date'])) ?> - <?= date('M d, Y', strtotime($competition['end_date'])) ?></p>
        <p>Voting Period: <?= date('M d, Y', strtotime($competition['end_date'])) ?> - <?= date('M d, Y', strtotime($competition['voting_end_date'])) ?></p>
        <div class="alert alert-info">
            <i class="fas fa-trophy"></i> Here are the top voted recipes from this competition.
        </div>
    </div>
</div>

<?php if (count($top_recipes) > 0): ?>
    <div class="row">
        <?php $placement = 1; ?>
        <?php foreach ($top_recipes as $recipe): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 floating-card">
                    <?php if ($placement === 1): ?>
                        <div class="position-absolute top-0 start-0 p-2">
                            <span class="badge bg-warning text-dark fs-5">
                                <i class="fas fa-crown"></i> Winner
                            </span>
                        </div>
                    <?php elseif ($placement <= 3): ?>
                        <div class="position-absolute top-0 start-0 p-2">
                            <span class="badge bg-secondary fs-5">
                                #<?= $placement ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($recipe['image_path'])): ?>
                        <img src="<?= htmlspecialchars($recipe['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>" style="height: 200px; object-fit: cover;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                        <p class="card-text">By: <?= htmlspecialchars($recipe['username']) ?></p>
                        <p class="card-text">
                            <span class="badge bg-success">
                                <i class="fas fa-thumbs-up"></i> <?= $recipe['vote_count'] ?> votes
                            </span>
                        </p>
                        <a href="index.php?page=recipes&action=view&id=<?= $recipe['recipe_id'] ?>" class="btn btn-primary">View Recipe</a>
                    </div>
                </div>
            </div>
            <?php $placement++; ?>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> No votes have been recorded for this competition yet.
    </div>
<?php endif; ?>