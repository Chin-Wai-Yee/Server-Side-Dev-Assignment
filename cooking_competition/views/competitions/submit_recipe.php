<?php
// Don't include header/footer since this will be displayed in a popup
?>
<div class="modal fade" id="submitRecipeModal" tabindex="-1" aria-labelledby="submitRecipeModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="submitRecipeModalLabel">Submit Recipe to Competition</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (empty($user_recipes)): ?>
          <div class="alert alert-info">
            You don't have any recipes to submit. <a href="index.php?page=recipes&action=add">Create a recipe</a> first!
          </div>
        <?php else: ?>
          <form id="submitRecipeForm" method="POST" action="index.php?page=competitions&action=submit_recipe&id=<?= $competition_id ?>">
            <div class="mb-3">
              <label for="recipe_id" class="form-label">Select a Recipe</label>
              <select class="form-select" id="recipe_id" name="recipe_id" required>
                <option value="">-- Select a Recipe --</option>
                <?php foreach ($user_recipes as $recipe): ?>
                  <option value="<?= $recipe['recipe_id'] ?>">
                    <?= htmlspecialchars($recipe['title']) ?>
                    <?php if (!empty($recipe['competition_name'])): ?>
                      (Also in: <?= htmlspecialchars($recipe['competition_name']) ?>)
                    <?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text mt-2">
                You can submit any recipe you own, even if it's already in another competition.
              </div>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Submit Recipe</button>
            </div>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  // Automatically show the modal when the page loads
  document.addEventListener('DOMContentLoaded', function() {
    const submitRecipeModal = new bootstrap.Modal(document.getElementById('submitRecipeModal'));
    submitRecipeModal.show();
    
    // Handle form submission
    const form = document.getElementById('submitRecipeForm');
    if (form) {
      form.addEventListener('submit', function() {
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
      });
    }
  });
</script>