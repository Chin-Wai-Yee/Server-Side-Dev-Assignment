<div class="mb-3">
    <a href="index.php?page=competitions" class="btn btn-outline-secondary">← Back to Competitions</a>
</div>

<div class="card mb-4">
    <?php if (!empty($competition['image'])): ?>
    <img src="<?= htmlspecialchars($competition['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($competition['title']) ?>" style="max-height: 300px; object-fit: cover;">
    <?php endif; ?>
    
    <div class="card-body">
        <h1 class="card-title"><?= htmlspecialchars($competition['title']) ?></h1>
        
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
        
        <?php if ($competition['status'] == 'completed' && isset($winner)): ?>
        <div class="alert alert-success">
            <h4>Winner: <?= htmlspecialchars($winner['name']) ?></h4>
            <p>Recipe: <a href="index.php?page=recipes&action=view&id=<?= $winner['recipe_id'] ?>"><?= htmlspecialchars($winner['recipe_title']) ?></a></p>
        </div>
        <?php endif; ?>
        
        <?php if ($logged_in && $competition['status'] == 'active'): ?>
        <div class="mt-3">
            <?php 
            // Create a Recipe object to check if user has already submitted
            $recipeCheck = new Recipe($this->conn);
            $alreadySubmitted = $recipeCheck->already_submitted($competition['id'], $_SESSION['user_id']);
            
            if (!$alreadySubmitted): 
            ?>
                <button type="button" class="btn btn-primary" id="submitRecipeBtn">Submit a Recipe</button>
            <?php else: ?>
                <div class="alert alert-info">You have already submitted a recipe to this competition.</div>
            <?php endif; ?>
        </div>
        <?php elseif ($logged_in && $competition['status'] == 'voting'): ?>
        <div class="mt-3">
            <a href="index.php?page=vote&action=competition&id=<?= $competition['id'] ?>" class="btn btn-warning">Vote Now</a>
        </div>
        <?php endif; ?>
    </div>
</div>

<h2>Submitted Recipes</h2>

<?php if (count($recipes) > 0): ?>
<div class="row">
    <?php foreach ($recipes as $recipe): ?>
    <div class="col-md-4 mb-4">
        <div class="card recipe-card" data-recipe-id="<?= $recipe['comp_recipe_id'] ?>">
            <?php if (!empty($recipe['image'])): ?>
            <img src="<?= htmlspecialchars($recipe['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($recipe['title']) ?>">
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($recipe['title']) ?></h5>
                <p class="card-text">By: <?= htmlspecialchars($recipe['username']) ?></p>
                <a href="index.php?page=recipes&action=view&id=<?= $recipe['comp_recipe_id'] ?>" class="btn btn-primary view-recipe-btn">View Recipe</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Modal for recipe popup -->
<div class="modal fade" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="recipeModalLabel">Recipe Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body recipe-modal-content">
        <!-- Content will be loaded dynamically -->
        <div class="text-center loading-spinner">
          <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="#" class="btn btn-primary full-recipe-link">View Full Recipe</a>
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="alert alert-info">No recipes have been submitted yet.</div>
<?php endif; ?>

<!-- Submit Recipe Modal Container - Will be populated via AJAX -->
<div id="submitRecipeModalContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Setup event listener for submit recipe button
  const submitRecipeBtn = document.getElementById('submitRecipeBtn');
  if (submitRecipeBtn) {
    submitRecipeBtn.addEventListener('click', function() {
      // Show loading state
      submitRecipeBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
      submitRecipeBtn.disabled = true;
      
      // Fetch the submit recipe form via AJAX
      fetch('index.php?page=competitions&action=submit_recipe&id=<?= $competition['id'] ?>', {
        method: 'GET',
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => response.text())
      .then(html => {
        // Insert the modal HTML into the page
        document.getElementById('submitRecipeModalContainer').innerHTML = html;
        
        // Initialize the modal with Bootstrap
        const submitRecipeModal = new bootstrap.Modal(document.getElementById('submitRecipeModal'));
        submitRecipeModal.show();
        
        // Reset button state
        submitRecipeBtn.innerHTML = 'Submit a Recipe';
        submitRecipeBtn.disabled = false;
      })
      .catch(error => {
        console.error('Error loading submit recipe form:', error);
        submitRecipeBtn.innerHTML = 'Submit a Recipe';
        submitRecipeBtn.disabled = false;
        alert('Failed to load the submission form. Please try again.');
      });
    });
  }
});
</script>
