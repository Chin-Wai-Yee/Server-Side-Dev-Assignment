document.addEventListener('DOMContentLoaded', function() {
    // Get all recipe cards
    const recipeCards = document.querySelectorAll('.recipe-card');
    const recipeModal = document.getElementById('recipeModal');
    const modalTitle = document.querySelector('#recipeModal .modal-title');
    const modalContent = document.querySelector('.recipe-modal-content');
    const fullRecipeLink = document.querySelector('.full-recipe-link');
    
    // Initialize Bootstrap modal
    const modal = new bootstrap.Modal(recipeModal);
    
    // Add click event to recipe cards
    recipeCards.forEach(card => {
        // Add floating animation on hover
        card.addEventListener('mouseenter', function() {
            this.classList.add('float-animation');
        });
        
        card.addEventListener('mouseleave', function() {
            this.classList.remove('float-animation');
        });
        
        // Add click event for popup
        card.addEventListener('click', function(e) {
            // Don't trigger modal if they clicked the view button directly
            if (e.target.classList.contains('view-recipe-btn') || 
                e.target.closest('.view-recipe-btn')) {
                return;
            }
            
            const recipeId = this.getAttribute('data-recipe-id');
            const recipeTitle = this.querySelector('.card-title').textContent;
            const recipeImage = this.querySelector('img')?.src;
            const fullUrl = `index.php?page=recipes&action=view&id=${recipeId}`;
            
            // Update modal content
            modalTitle.textContent = recipeTitle;
            fullRecipeLink.href = fullUrl;
            
            // Show loading spinner
            modalContent.innerHTML = `
                <div class="text-center loading-spinner">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
            
            // Show modal
            modal.show();
            
            // Fetch recipe details (simplified for demo)
            setTimeout(() => {
                // Create a preview of the recipe in the modal
                let content = '';
                
                if (recipeImage) {
                    content += `<img src="${recipeImage}" alt="${recipeTitle}" class="img-fluid">`;
                }
                
                content += `
                    <h4>${recipeTitle}</h4>
                    <p class="card-text">Click "View Full Recipe" to see ingredients and instructions.</p>
                    <div class="text-center mt-3">
                        <span class="badge bg-info">Preview</span>
                    </div>
                `;
                
                modalContent.innerHTML = content;
            }, 500);
        });
    });
});