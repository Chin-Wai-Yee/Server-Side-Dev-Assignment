<div class="mb-3">
    <a href="index.php?page=competitions" class="btn btn-outline-secondary">← Back to Competitions</a>
</div>

<div class="card mb-4">
    <?php if (!empty($competition['image'])): ?>
        <?php $imagePath = $competition['image']; ?>
        <img src="<?= htmlspecialchars($competition['image']) ?>" 
             class="card-img-top" 
             alt="<?= htmlspecialchars($competition['title']) ?>" 
             style="max-height: 300px; object-fit: cover;"
             onerror="this.onerror=null; this.src='assets/images/default_competition.png';">
    <?php else: ?>
        <img src="assets/images/default_competition.png" 
             class="card-img-top" 
             alt="Default Competition Image" 
             style="max-height: 300px; object-fit: cover;">
    <?php endif; ?>

    <div class="card-body">
        <h1 class="card-title"><?= htmlspecialchars($competition['title']) ?></h1>
        
        <div class="mb-3 d-flex justify-content-between align-items-center">
            <div class="col"></div>
            <div class="text-center col">
                <span class="badge bg-<?= $competition['status'] == 'active' ? 'success' : ($competition['status'] == 'voting' ? 'warning' : 'secondary') ?>">
                    <?= ucfirst($competition['status']) ?>
                </span>
            </div>
            <div class="col">
                <?php if ((isset($_SESSION['role']) && $_SESSION['role'] == 'admin') || 
                    (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $competition['created_by'])): ?>
                <a href="index.php?page=competitions&action=edit&id=<?= $competition['id'] ?>" class="btn btn-warning">
                    <i class="bi bi-pencil"></i> Edit
                </a>
                <a href="index.php?page=competitions&action=delete&id=<?= $competition['id'] ?>" 
                class="btn btn-danger">
                    <i class="bi bi-trash"></i> Delete
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php
        // Get creator information
        $creator_query = "SELECT username FROM users WHERE user_id = ?";
        $stmt = $this->conn->prepare($creator_query);
        $stmt->bind_param('i', $competition['created_by']);
        $stmt->execute();
        $creator_result = $stmt->get_result();
        $creator = $creator_result->fetch_assoc();
        $stmt->close();
        ?>
        <p class="text-muted mb-3">Created by: <?= htmlspecialchars($creator['username']) ?></p>
        
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
        
        <?php if ($competition['status'] == 'completed' && isset($winner)): ?>
        <div class="alert alert-success">
            <h4>Winner: <?= htmlspecialchars($winner['name']) ?></h4>
            <p>Recipe: <a href="../recipes/recipe_detail.php?recipe_id=<?= $winner['recipe_id'] ?>" target="_blank"><?= htmlspecialchars($winner['recipe_title']) ?></a></p>
        </div>    
        <?php elseif ($competition['status'] == 'upcoming'): ?>
        <div class="mt-3">
          <div class="alert alert-info">Stay tuned on this competition!</div>
        </div>
        <?php elseif ($logged_in && $competition['status'] == 'active'): ?>
        <div class="mt-3">
            <?php 
            // Create a Recipe object to check if user has already submitted
            $recipeCheck = new Recipe($this->conn);
            $alreadySubmitted = $recipeCheck->already_submitted($competition['id'], $_SESSION['user_id']);
            
            if (!$alreadySubmitted): 
            ?>
                <button type="button" class="btn btn-primary" id="submitRecipeBtn" data-bs-toggle="modal" data-bs-target="#submitRecipeModal">Submit a Recipe</button>
                <?php include_once 'views/competitions/submit_recipe.php'; ?>
            <?php else: ?>
                <div class="alert alert-info">You have already submitted a recipe to this competition.</div>
                <a href="index.php?page=competitions&action=withdraw&id=<?= $competition['id'] ?>" 
                  class="btn btn-warning mt-2" 
                  onclick="return confirm('Are you sure you want to withdraw your recipe from this competition? This action cannot be undone.');">
                   <i class="bi bi-x-circle"></i> Withdraw My Submission
                </a>
            <?php endif; ?>
        </div>
        <?php elseif ($logged_in && $competition['status'] == 'voting'): ?>
        <div class="mt-3">
            <div class="alert alert-warning">Vote for your favourite recipes by clicking the like button!</div>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success mt-3">
            <?= $_SESSION['success']; ?>
            <?php unset($_SESSION['success']); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger mt-3">
                <?= $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<h2>Submitted Recipes</h2>

<?php if (count($recipes) > 0): ?>

<?php 
// If logged in and in voting phase, get user's votes
$userVotes = [];
if ($logged_in && $competition['status'] == 'voting') {
    $vote = new Vote($this->conn);
    $vote->user_id = $_SESSION['user_id'];
    $userVotes = $vote->get_user_votes();
}
?>

<div class="row">
    <?php foreach ($recipes as $recipe): ?>
    <div class="col-md-4 mb-4">
        <div class="card recipe-card floating-card" data-recipe-id="<?= $recipe['comp_recipe_id'] ?>" data-bs-target="#recipeModal<?=$recipe['comp_recipe_id']?>">
            <?php if (!empty($recipe['image_path'])): ?>
            <img src="../recipes/<?= htmlspecialchars($recipe['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                <p class="card-text">By: <?= htmlspecialchars($recipe['username']) ?></p>
                
                <div class="d-flex justify-content-between align-items-center">
                    <a href="../recipes/recipe_detail.php?recipe_id=<?=$recipe['recipe_id'] ?>" target="_blank" class="btn btn-primary view-recipe-btn">View Recipe</a>
                    
                    <?php if ($competition['status'] == 'voting'): ?>
                    <div class="d-flex align-items-center">
                        <?php 
                        // Check if user has voted for this recipe
                        $hasVoted = in_array($recipe['comp_recipe_id'], $userVotes);
                        ?>
                        <button class="btn like-btn <?= $hasVoted ? 'btn-danger' : 'btn-outline-danger' ?>" 
                            data-recipe-id="<?= $recipe['comp_recipe_id'] ?>" 
                            data-competition-id="<?= $competition['id'] ?>">
                            <i class="bi bi-heart<?= $hasVoted ? '-fill' : '' ?>"></i>
                            <span class="vote-count"><?= (new Vote($this->conn))->count_votes($recipe['comp_recipe_id']) ?></span>
                        </button>
                    </div>
                    <?php else: ?>
                    <div class="d-flex align-items-center">
                        <span class="text-muted">
                            <i class="bi bi-heart"></i>
                            <span class="vote-count"><?= (new Vote($this->conn))->count_votes($recipe['comp_recipe_id']) ?></span>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
      </div>
      
      <!-- Modal for recipe popup -->
      <?php include 'views/competitions/recipe_modal.php'; ?>
      <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">No recipes have been submitted yet.</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Setup for recipe cards and modals
    document.querySelectorAll('.recipe-card').forEach(card => {
      const modalId = card.getAttribute('data-bs-target');
      
      // Remove the automatic trigger
      card.removeAttribute('data-bs-toggle');
      
      // Add our custom click handler
      card.addEventListener('click', function(e) {
        // Don't open the modal if clicked on a button, link, or other interactive element
        if (e.target.closest('.btn') || e.target.closest('button') || e.target.closest('a')) {
          return; // Stop here - don't open modal
        }
        
        // Open the modal for clicks directly on the card
        const modal = new bootstrap.Modal(document.querySelector(modalId));
        modal.show();
      });
    });
    
    // Setup event listeners for all like buttons
    const likeButtons = document.querySelectorAll('.like-btn');
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            // Prevent the event from bubbling up to the card
            event.stopPropagation();
            
            if (!this.disabled) {
                const recipeId = this.getAttribute('data-recipe-id');
                const competitionId = this.getAttribute('data-competition-id');
                const isLiked = this.classList.contains('btn-danger');
                const voteCountElement = this.querySelector('.vote-count');
                const heartIcon = this.querySelector('.bi');
                
                // Disable button during request to prevent multiple clicks
                this.disabled = true;
                
                // Send AJAX request to vote
                fetch('api_vote.php?action=toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: `recipe_id=${recipeId}&competition_id=${competitionId}&vote=${isLiked ? 'remove' : 'add'}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.status == 'success') {
                        if (data.voted) {
                          this.classList.replace('btn-outline-danger', 'btn-danger');
                          heartIcon.classList.replace('bi-heart', 'bi-heart-fill');
                        } else {
                          this.classList.replace('btn-danger', 'btn-outline-danger');
                          heartIcon.classList.replace('bi-heart-fill', 'bi-heart');
                        }
                            
                        // Update vote count
                        voteCountElement.textContent = data.count;
                    }
                    else if (data.status == 'error') {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error voting for recipe:', error);
                    alert('Failed to vote. Please try again.');
                })
                .finally(() => {
                    // Re-enable button after request completes
                    this.disabled = false;
                });
            }
        });
    });
});
</script>