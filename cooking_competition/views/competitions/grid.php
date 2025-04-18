<div class="row">
    <?php foreach ($competitions as $comp): ?>
        <div class="col-md-4 mb-4" onclick="window.location.href='index.php?page=competitions&action=view&id=<?= $comp['id'] ?>'">
            <div class="card floating-card h-100">
                <?php if (!empty($comp['image'])): ?>
                    <?php $imagePath = $comp['image']; ?>
                    <?php if (file_exists($imagePath) || strpos($imagePath, 'http') === 0): ?>
                        <img src="<?= htmlspecialchars($comp['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($comp['title']) ?>" onerror="this.src='assets/images/default_competition.png';">
                    <?php else: ?>
                        <img src="assets/images/default_competition.png" class="card-img-top" alt="Default competition image">
                    <?php endif; ?>
                <?php else: ?>
                    <img src="assets/images/default_competition.png" class="card-img-top" alt="Default competition image">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($comp['title']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars(substr($comp['description'], 0, 100)) ?>...</p>
                    <div class="mb-2">
                        <?php
                        $badgeClass = 'warning'; // Default
                        if ($comp['status'] == 'active') {
                            $badgeClass = 'success';
                        } elseif ($comp['status'] == 'upcoming') {
                            $badgeClass = 'info';
                        } elseif ($comp['status'] == 'completed') {
                            $badgeClass = 'secondary';
                        }
                        ?>
                        <span class="badge bg-<?= $badgeClass ?>"><?= ucfirst($comp['status']) ?></span>
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
                </div>
                <div class="card-footer text-muted">
                    <small><?= $comp['recipe_count'] ?> recipes submitted</small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>