<?php
/*
function generate_csrf_token()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        // Generate new token if none exists
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        throw new Exception("Invalid CSRF token");
    }

    // Regenerate token after validation
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
*/

/*
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field()
{
    // Reuse the existing token generation function
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

*/


/*
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    $isValid = isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    return $isValid;
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

*/

// Always ensure token exists before validation
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    generate_csrf_token(); // Ensure token exists first
    return hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field() {
    generate_csrf_token(); // Ensure token exists first
    return '<input type="hidden" name="csrf_token" value="' . 
           htmlspecialchars($_SESSION['csrf_token']) . '">';
}

?>
