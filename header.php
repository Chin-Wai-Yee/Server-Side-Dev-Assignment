<header>
    <nav>
        <ul>
            <li><a href="/recipe culinary/index.php">Home</a></li>
            <div class="dropdown">
                <li><a href="/recipe culinary/recipes/recipe.php">Recipes</a></li>
                <div class="dropdown-content">
                    <a href="/recipe culinary/recipes/add_recipe.php">Create Recipe</a>
                    <a href="/recipe culinary/recipes/edit_recipe.php?recipe_id=2">Edit Recipe</a>
                    <a href="/recipe culinary/recipes/delete_recipe.php">Delete Recipe</a>
                    <a href="/recipe culinary/recipes/recipe.php#search-bar">Search Recipe</a>
                </div>
            </div>
            <li><a href="meal_planning.php">Meal Planning</a></li>
            <li><a href="community.php">Community</a></li>
            <li><a href="cooking_competition.php">Cooking Competition</a></li>

            <!-- Check if the user is logged in -->
            <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true): ?>
                <li><a href="profile.php"><i class="fas fa-user"></i></a></li>
            <?php else: ?>
                <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>