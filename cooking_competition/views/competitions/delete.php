<?php
// views/competitions/delete.php - Confirmation page for deleting a competition
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h2 class="mb-0"><i class="fas fa-trash"></i> Delete Competition</h2>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?= $_SESSION['error']; ?>
                            <?php unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-warning">
                        <p><strong>Warning:</strong> You are about to delete the competition "<strong><?= htmlspecialchars($competition['title']) ?></strong>".</p>
                        <p>This action cannot be undone. All associated recipes and votes will also be deleted.</p>
                    </div>

                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= htmlspecialchars($competition['title']) ?></h4>
                            
                            <div class="mb-3">
                                <span class="badge bg-<?= $competition['status'] == 'active' ? 'success' : ($competition['status'] == 'voting' ? 'warning' : 'secondary') ?>">
                                    <?= ucfirst($competition['status']) ?>
                                </span>
                            </div>
                            
                            <p class="card-text"><?= nl2br(htmlspecialchars($competition['description'])) ?></p>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <p><strong>Submission Period:</strong><br>
                                    <?= date('M d, Y', strtotime($competition['start_date'])) ?> - 
                                    <?= date('M d, Y', strtotime($competition['end_date'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Voting Period:</strong><br>
                                    <?= date('M d, Y', strtotime($competition['end_date'])) ?> - 
                                    <?= date('M d, Y', strtotime($competition['voting_end_date'])) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="index.php?page=competitions&action=delete&id=<?= $competition['id'] ?>">
                        <div class="d-flex justify-content-between">
                            <a href="index.php?page=competitions" class="btn btn-secondary" role="button">Cancel</a>
                            <button type="submit" class="btn btn-danger">Confirm Delete</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    <small>Created on: <?= date('Y-m-d H:i', strtotime($competition['created_at'])) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>