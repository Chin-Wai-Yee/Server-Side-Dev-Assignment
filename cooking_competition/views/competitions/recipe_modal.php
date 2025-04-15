<div class="modal fade" id="recipeModal<?=$recipe['comp_recipe_id']?>" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="recipeModalLabel<?=$recipe['comp_recipe_id']?>">Recipe Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start"> <!-- Added text-start class for left alignment -->
        <div class="text-center mb-3 recipe-loading-spinner">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading recipe details...</p>
        </div>
        <div class="recipe-details-container" style="display: none;">
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="recipe-image-container text-center">
                <img src="" alt="Recipe Image" class="recipe-image img-fluid rounded" style="max-height: 250px;">
              </div>
            </div>
            <div class="col-md-6 mb-3 text-start"> <!-- Added text-start class -->
              <h4 class="recipe-title"></h4>
              <p class="recipe-author">By: <span class="author-name"></span></p>
              <p class="recipe-cuisine">Cuisine: <span class="cuisine-type"></span></p>
            </div>
          </div>
          <div class="row mt-3">
            <div class="col-md-6 text-start"> <!-- Added text-start class -->
              <h5>Ingredients</h5>
              <ul class="ingredients-list text-start"></ul> <!-- Added text-start class -->
            </div>
            <div class="col-md-6 text-start"> <!-- Added text-start class -->
              <h5>Instructions</h5>
              <div class="instructions-text text-start"></div> <!-- Added text-start class -->
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="../recipes/recipe_detail.php?recipe_id=<?=$recipe['recipe_id'] ?>" target="_blank" class="btn btn-primary full-recipe-link">View Full Recipe</a>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Load recipe details when modal is shown
document.addEventListener('DOMContentLoaded', function() {
  const recipeModal = document.getElementById('recipeModal<?=$recipe['comp_recipe_id']?>');
  
  if (recipeModal) {
    recipeModal.addEventListener('show.bs.modal', function() {
      const compRecipeId = <?=$recipe['comp_recipe_id']?>;
      const spinner = this.querySelector('.recipe-loading-spinner');
      const detailsContainer = this.querySelector('.recipe-details-container');
      
      // Show spinner, hide details
      spinner.style.display = 'block';
      detailsContainer.style.display = 'none';
      
      // Fetch recipe details
      fetch('api_recipe.php?comp_recipe_id=' + compRecipeId)
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => {
          if (data.status === 'success') {
            const recipe = data.data;
            
            // Populate recipe details
            const recipeImage = detailsContainer.querySelector('.recipe-image');
            const recipeTitle = detailsContainer.querySelector('.recipe-title');
            const authorName = detailsContainer.querySelector('.author-name');
            const cuisineType = detailsContainer.querySelector('.cuisine-type');
            const ingredientsList = detailsContainer.querySelector('.ingredients-list');
            const instructionsText = detailsContainer.querySelector('.instructions-text');
            
            // Set image if available
            if (recipe.image_path) {
              recipeImage.src = '../recipes/' + recipe.image_path;
              recipeImage.alt = recipe.title;
            } else {
              recipeImage.src = 'assets/images/default_competition.png';
              recipeImage.alt = 'Default recipe image';
            }
            
            // Set title, author, cuisine
            recipeTitle.textContent = recipe.title;
            authorName.textContent = recipe.username || 'Unknown';
            cuisineType.textContent = recipe.cuisine_type || 'Not specified';
            
            // Set ingredients
            ingredientsList.innerHTML = '';
            if (recipe.ingredients_list && recipe.ingredients_list.length > 0) {
              recipe.ingredients_list.forEach(ingredient => {
                if (ingredient.trim()) {
                  const li = document.createElement('li');
                  li.textContent = ingredient.trim();
                  ingredientsList.appendChild(li);
                }
              });
            } else {
              ingredientsList.innerHTML = '<li>No ingredients listed</li>';
            }
            
            // Set instructions
            if (recipe.instructions) {
              instructionsText.innerHTML = recipe.instructions_formatted;
            } else {
              instructionsText.textContent = 'No instructions provided';
            }
            
            // Show details, hide spinner
            spinner.style.display = 'none';
            detailsContainer.style.display = 'block';
            
            // Update the "View Full Recipe" link
            const fullRecipeLink = recipeModal.querySelector('.full-recipe-link');
            if (fullRecipeLink && recipe.recipe_id) {
              fullRecipeLink.href = '../recipes/recipe_detail.php?recipe_id=' + recipe.recipe_id;
            }
          } else {
            // Show error message
            spinner.style.display = 'none';
            detailsContainer.innerHTML = '<div class="alert alert-danger">Failed to load recipe details: ' + data.message + '</div>';
            detailsContainer.style.display = 'block';
          }
        })
        .catch(error => {
          console.error('Error fetching recipe details:', error);
          // Show error message
          spinner.style.display = 'none';
          detailsContainer.innerHTML = '<div class="alert alert-danger">Error loading recipe details. Please try again later.</div>';
          detailsContainer.style.display = 'block';
        });
    });
  }
});
</script>