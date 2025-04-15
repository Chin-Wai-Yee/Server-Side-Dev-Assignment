<div class="mb-3">
    <a href="index.php?page=competitions&action=view&id=<?= $competition['id'] ?>" class="btn btn-outline-secondary">← Back to Competition</a>
</div>

<div class="card mb-4">
    <div class="card-header bg-warning text-white">
        <h2>Vote for Your Favorite Recipe</h2>
    </div>
    <div class="card-body">
        <h3><?= htmlspecialchars($competition['title']) ?></h3>
        <p>Voting Period: <?= date('M d, Y', strtotime($competition['end_date'])) ?> - <?= date('M d, Y', strtotime($competition['voting_end_date'])) ?></p>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> You can vote for multiple recipes. Your votes help determine the winner of this competition!
        </div>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show">
    <?= $_SESSION['success'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <?= $_SESSION['error'] ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php unset($_SESSION['error']); endif; ?>

<?php if (count($recipes) > 0): ?>
<div class="row">
    <?php foreach ($recipes as $recipe): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 floating-card">
            <?php if (!empty($recipe['image'])): ?>
            <img src="<?= htmlspecialchars($recipe['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>" style="height: 200px; object-fit: cover;">
            <?php else: ?>
            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                <span class="text-muted">No Image</span>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                <p class="card-text">By: <?= htmlspecialchars($recipe['username']) ?></p>
                <p class="card-text text-muted">
                    <small>
                        <i class="fas fa-thumbs-up"></i> 
                        <span class="vote-count" data-recipe-id="<?= $recipe['comp_recipe_id'] ?>">
                            <?= $this->vote->count_votes($recipe['comp_recipe_id']) ?>
                        </span> votes
                    </small>
                </p>
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=recipes&action=view&id=<?= $recipe['comp_recipe_id'] ?>" class="btn btn-sm btn-outline-primary">View Recipe</a>
                    
                    <?php if (in_array($recipe['comp_recipe_id'], $user_votes)): ?>
                    <button class="btn btn-sm btn-success disabled">
                        <i class="fas fa-check"></i> Voted
                    </button>
                    <?php else: ?>
                    <button class="btn btn-sm btn-outline-warning vote-btn" data-recipe-id="<?= $recipe['comp_recipe_id'] ?>">
                        <i class="fas fa-thumbs-up"></i> Vote
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Toast notification for vote feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="voteToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Vote Status</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <!-- Toast message will be inserted here -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all vote buttons
    const voteButtons = document.querySelectorAll('.vote-btn');
    
    // Initialize toast
    const toastEl = document.getElementById('voteToast');
    const toast = new bootstrap.Toast(toastEl);
    const toastBody = toastEl.querySelector('.toast-body');
    
    // Add click event to vote buttons
    voteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const recipeId = this.getAttribute('data-recipe-id');
            const voteCountEl = document.querySelector(`.vote-count[data-recipe-id="${recipeId}"]`);
            
            // Disable button and show loading state
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Voting...';
            
            // Send vote request
            fetch('index.php?page=vote&action=submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `recipe_id=${recipeId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update vote count
                    voteCountEl.textContent = data.vote_count;
                    
                    // Change button to voted state
                    this.classList.remove('btn-outline-warning');
                    this.classList.add('btn-success');
                    this.innerHTML = '<i class="fas fa-check"></i> Voted';
                    this.disabled = true;
                    
                    // Show success toast
                    toastBody.textContent = data.message;
                    toastBody.className = 'toast-body bg-success text-white';
                } else {
                    // Re-enable button
                    this.disabled = false;
                    this.innerHTML = '<i class="fas fa-thumbs-up"></i> Vote';
                    
                    // Show error toast
                    toastBody.textContent = data.message;
                    toastBody.className = 'toast-body bg-danger text-white';
                }
                
                // Show toast
                toast.show();
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable button
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-thumbs-up"></i> Vote';
                
                // Show error toast
                toastBody.textContent = 'An error occurred. Please try again.';
                toastBody.className = 'toast-body bg-danger text-white';
                toast.show();
            });
        });
    });
});
</script>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle"></i> No recipes have been submitted to this competition yet.
</div>
<?php endif; ?>