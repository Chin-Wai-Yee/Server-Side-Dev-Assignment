<?php
session_start();


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: users/login.php');
    exit;
}

require_once 'header.php';
   
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Planner</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            padding: 70px 0 60px 0; /* Add padding to account for fixed nav and footer */
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: Arial, sans-serif;
        }

        .page-header {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }

        .page-header h1 {
            color: #333;
            font-size: 2.5em;
            margin: 0;
        }

        .page-header p {
            color: #666;
            font-size: 1.1em;
            margin: 10px 0 0;
        }

        .meal-planner-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1;
            min-height: calc(100vh - 130px); /* Account for nav and footer height */
        }
        
        .meal-planner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-right: 240px;
        }
        
        .day-column {
            border: 1px solid #ddd;
            padding: 15px;
            min-height: 300px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }

        .day-column:hover {
            transform: translateY(-5px);
        }
        
        .day-header {
            text-align: center;
            padding: 10px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            border-radius: 6px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .meal-slot {
            border: 1px solid #dee2e6;
            padding: 10px;
            margin: 5px 0;
            cursor: move;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 6px;
            transition: all 0.3s ease;
            min-height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            position: relative;
        }

        .meal-slot:hover {
            background: #f8f9fa;
            transform: scale(1.02);
        }

        .recipe-list {
            position: fixed;
            right: 20px;
            top: 150px;
            width: 250px;
            border: 1px solid #ddd;
            padding: 15px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            max-height: calc(100vh - 200px);
            overflow-y: auto;
            z-index: 2;
        }

        .recipe-list .drop-zone {
            min-height: 50px;
            border: 2px dashed #ddd;
            border-radius: 6px;
            margin: 10px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-style: italic;
            transition: all 0.3s ease;
        }

        .recipe-list .drop-zone.dragover {
            border-color: #6a11cb;
            background: rgba(106, 17, 203, 0.1);
        }

        .recipe-list h3 {
            margin-top: 0;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            color: #333;
            font-size: 1.1em;
        }
        
        .recipe-item {
            padding: 8px;
            margin: 4px 0;
            cursor: move;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 6px;
            transition: all 0.3s ease;
            user-select: none;
            -webkit-user-drag: element;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            font-size: 0.9em;
        }
        
        .recipe-item:hover {
            background: #f8f9fa;
            transform: translateX(-5px);
        }
        
        .meal-type {
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #495057;
            text-transform: capitalize;
        }
        
        .empty-slot {
            color: #868e96;
            font-style: italic;
        }
        
        .controls {
            margin-bottom: 2%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }
        
        .week-navigation {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            transition: all 0.3s ease;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        @media (max-width: 1200px) {
            .meal-planner {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                margin-right: 220px;
            }
        }

        @media (max-width: 992px) {
            .meal-planner {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
                margin-right: 200px;
            }
        }

        @media (max-width: 768px) {
            .meal-planner {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
                margin-right: 0;
            }
            
            .recipe-list {
                position: static;
                width: 100%;
                max-height: 250px;
                margin-top: 20px;
            }
        }

        @media (max-width: 576px) {
            .meal-planner {
                grid-template-columns: 1fr;
            }
            
            .controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .week-navigation {
                width: 100%;
                justify-content: center;
            }
            
            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>Meal Planner</h1>
        <p>Plan your meals for the week</p>
    </div>

    <div class="meal-planner-container">
        <div class="controls">
            <div class="week-navigation">
                <button class="btn" onclick="previousWeek()">Previous Week</button>
                <button class="btn" onclick="nextWeek()">Next Week</button>
                <button class="btn" onclick="saveMealPlan()">Save Plan</button>
            </div>
        </div>
        
        <div class="meal-planner" id="mealPlanner">
            <!-- Days will be populated by JavaScript -->
        </div>

        <div class="recipe-list" id="recipeList">
            <h3>Available Recipes</h3>
            <!-- Recipes will be populated by JavaScript -->
        </div>
    </div>

    <script>
        let currentPlan = {};
        let recipes = [];
        let currentDate = new Date();
        
        // Initialize the calendar
        function initCalendar() {
            const planner = document.getElementById('mealPlanner');
            planner.innerHTML = '';
            
            for (let i = 0; i < 7; i++) {
                const date = new Date(currentDate);
                date.setDate(currentDate.getDate() + i);
                
                const dayColumn = document.createElement('div');
                dayColumn.className = 'day-column';
                dayColumn.dataset.date = date.toISOString().split('T')[0];
                
                const dayHeader = document.createElement('div');
                dayHeader.className = 'day-header';
                dayHeader.innerHTML = `
                    <div>${date.toLocaleDateString('en-US', { weekday: 'short' })}</div>
                    <div>${date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}</div>
                `;
                dayColumn.appendChild(dayHeader);
                
                const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'];
                mealTypes.forEach(type => {
                    const mealTypeDiv = document.createElement('div');
                    mealTypeDiv.className = 'meal-type';
                    mealTypeDiv.textContent = type.charAt(0).toUpperCase() + type.slice(1);
                    dayColumn.appendChild(mealTypeDiv);
                    
                    const mealSlot = document.createElement('div');
                    mealSlot.className = 'meal-slot empty-slot';
                    mealSlot.dataset.mealType = type;
                    mealSlot.dataset.date = date.toISOString().split('T')[0];
                    mealSlot.draggable = true;
                    mealSlot.textContent = 'Add meal';
                    
                    mealSlot.addEventListener('dragstart', handleDragStart);
                    mealSlot.addEventListener('dragover', handleDragOver);
                    mealSlot.addEventListener('drop', handleDrop);
                    
                    dayColumn.appendChild(mealSlot);
                });
                
                planner.appendChild(dayColumn);
            }
        }

        // Load meal plan
        async function loadMealPlan() {
            try {
                const startDate = currentDate.toISOString().split('T')[0];
                const response = await fetch(`meal_planner.php?action=get_plan&start_date=${startDate}`);
                const data = await response.json();
                
                if (data.success && data.plan) {
                    // Ensure currentPlan is an object
                    currentPlan = {};
                    Object.assign(currentPlan, data.plan);
                    console.log('Loaded meal plan:', currentPlan);
                    
                    // Load recipes first if not already loaded
                    if (recipes.length === 0) {
                        await loadRecipes();
                    }
                    
                    updateMealPlanDisplay();
                } else {
                    console.log('No existing meal plan found, initializing empty plan');
                    currentPlan = {};
                }
            } catch (error) {
                console.error('Error loading meal plan:', error);
                currentPlan = {};
            }
        }

        // Update meal plan display
        function updateMealPlanDisplay() {
            console.log('Updating display with plan:', currentPlan);
            console.log('Available recipes:', recipes);
            
            Object.entries(currentPlan).forEach(([date, meals]) => {
                Object.entries(meals).forEach(([mealType, meal]) => {
                    const slot = document.querySelector(`.meal-slot[data-date="${date}"][data-meal-type="${mealType}"]`);
                    if (slot) {
                        if (meal.recipe_id) {
                            const recipe = recipes.find(r => r.recipe_id == meal.recipe_id);
                            if (recipe) {
                                slot.textContent = recipe.title;
                                slot.classList.remove('empty-slot');
                            } else {
                                // If recipe not found, try to use the stored title
                                slot.textContent = meal.recipe_title || 'Unknown Recipe';
                                slot.classList.remove('empty-slot');
                            }
                        } else if (meal.custom_meal_name) {
                            slot.textContent = meal.custom_meal_name;
                            slot.classList.remove('empty-slot');
                        } else {
                            slot.textContent = 'Add meal';
                            slot.classList.add('empty-slot');
                        }
                    }
                });
            });
        }

        // Load recipes
        async function loadRecipes() {
            try {
                const response = await fetch('recipes/recipe.php?action=list');
                const data = await response.json();
                
                if (Array.isArray(data)) {
                    recipes = data;
                    console.log('Loaded recipes:', recipes);
                    
                    const recipeList = document.getElementById('recipeList');
                    recipeList.innerHTML = '<h3>Available Recipes</h3>';
                    
                    // Add drop zone for returning recipes
                    const dropZone = document.createElement('div');
                    dropZone.className = 'drop-zone';
                    dropZone.textContent = 'Drop here to remove from plan';
                    dropZone.addEventListener('dragover', handleDragOver);
                    dropZone.addEventListener('drop', handleRecipeReturn);
                    recipeList.appendChild(dropZone);
                    
                    recipes.forEach(recipe => {
                        const recipeItem = document.createElement('div');
                        recipeItem.className = 'recipe-item';
                        recipeItem.draggable = true;
                        recipeItem.textContent = recipe.title;
                        recipeItem.dataset.recipeId = recipe.recipe_id;
                        
                        recipeItem.addEventListener('dragstart', handleDragStart);
                        recipeItem.addEventListener('dragend', function() {
                            this.classList.remove('dragging');
                        });
                        
                        recipeList.appendChild(recipeItem);
                    });
                } else {
                    console.error('Invalid recipes data received:', data);
                }
            } catch (error) {
                console.error('Error loading recipes:', error);
            }
        }

        // Navigation functions
        function previousWeek() {
            currentDate.setDate(currentDate.getDate() - 7);
            initCalendar();
            loadMealPlan();
        }

        function nextWeek() {
            currentDate.setDate(currentDate.getDate() + 7);
            initCalendar();
            loadMealPlan();
        }

        // Drag and drop handlers
        function handleDragStart(e) {
            const recipeId = e.target.dataset.recipeId;
            const recipeTitle = e.target.textContent;
            e.dataTransfer.setData('text/plain', JSON.stringify({
                recipeId: recipeId,
                recipeTitle: recipeTitle
            }));
            e.target.classList.add('dragging');
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
        }

        function handleDrop(e) {
            e.preventDefault();
            const slot = e.target.closest('.meal-slot');
            
            if (!slot) return;
            
            try {
                const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                const recipeId = data.recipeId;
                const recipeTitle = data.recipeTitle;
                
                if (!recipeId) return;
                
                const date = slot.dataset.date;
                const mealType = slot.dataset.mealType;
                
                // Ensure currentPlan is an object
                if (typeof currentPlan !== 'object' || Array.isArray(currentPlan)) {
                    currentPlan = {};
                }
                
                // Initialize the date object if it doesn't exist
                if (!currentPlan[date]) {
                    currentPlan[date] = {};
                }
                
                // Update the meal slot
                currentPlan[date][mealType] = {
                    recipe_id: recipeId,
                    recipe_title: recipeTitle
                };
                
                // Update the display
                slot.textContent = recipeTitle;
                slot.classList.remove('empty-slot');
                
                // Debug log
                console.log('Updated currentPlan:', currentPlan);
            } catch (error) {
                console.error('Error handling drop:', error);
            }
        }

        // Delete meal from slot
        function deleteMeal(slot) {
            const date = slot.dataset.date;
            const mealType = slot.dataset.mealType;
            
            if (currentPlan[date] && currentPlan[date][mealType]) {
                delete currentPlan[date][mealType];
                if (Object.keys(currentPlan[date]).length === 0) {
                    delete currentPlan[date];
                }
            }
            
            slot.textContent = 'Add meal';
            slot.classList.add('empty-slot');
            console.log('Updated currentPlan after delete:', currentPlan);
        }

        // Handle recipe return to list
        function handleRecipeReturn(e) {
            e.preventDefault();
            const slot = document.querySelector('.meal-slot.dragging');
            if (slot) {
                deleteMeal(slot);
            }
        }

        // Save meal plan
        async function saveMealPlan() {
            try {
                // Ensure currentPlan is an object
                if (typeof currentPlan !== 'object' || Array.isArray(currentPlan)) {
                    currentPlan = {};
                }
                
                console.log('Current plan before save:', currentPlan);
                
                // Check if any meals have been added
                let hasMeals = false;
                for (const date of Object.keys(currentPlan)) {
                    const meals = currentPlan[date];
                    if (meals && Object.keys(meals).length > 0) {
                        hasMeals = true;
                        break;
                    }
                }

                if (!hasMeals) {
                    alert('Please add some meals to your plan before saving.');
                    return;
                }

                const response = await fetch('meal_planner.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'save_plan',
                        plan: currentPlan
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();
                
                if (result.success) {
                    alert('Meal plan saved successfully!');
                } else {
                    alert('Save failed: ' + (result.error || 'Unknown error occurred'));
                }
            } catch (error) {
                console.error('Error saving meal plan:', error);
                alert('Save failed: ' + error.message);
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            initCalendar();
            loadRecipes();
            loadMealPlan();
        });
    </script>
</body>
</html>
<?php require_once 'footer.php'; ?>