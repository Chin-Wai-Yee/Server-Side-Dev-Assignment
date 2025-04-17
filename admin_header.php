<header>
    <?php
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Ensure CSRF token is generated and add it as a meta tag
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    ?>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <nav>
        <ul>
            <?php $base_path = '/recipe culinary'; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li> <a href="<?= $base_path ?>/users/admin_dashboard.php">Dashboard</a> </li>
            <?php endif; ?>
            <li> <a href="<?= $base_path ?>/users/manage_recipes.php">Recipes</a> </li>
            <li> <a href="<?= $base_path ?>/users/manage_competition.php">Cooking Competition</a> </li>

            <!-- Check if the user is logged in -->
            <div class="dropdown">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li> <a href="<?= $base_path ?>/users"><i class="fas fa-user"></i></a> </li>
                    <div class="dropdown-content">
                        <a href="<?= $base_path ?>/users">Profile</a>
                        <a href="<?= $base_path ?>/users/logout.php">Logout</a>
                    </div>
                <?php else: ?>
                    <li> <a href="<?= $base_path ?>/users"><i class="fas fa-sign-in-alt"></i> Login</a> </li>
                    <div class="dropdown-content">
                        <a href="<?= $base_path ?>/users">Profile</a>
                        <a href="<?= $base_path ?>/users/login.php">Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </ul>
    </nav>
</header>