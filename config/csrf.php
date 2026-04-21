<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
        echo '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify($redirect = '../dashboard.php?error=invalid')
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }

        $sessionToken = $_SESSION['csrf_token'] ?? '';
        $postedToken  = $_POST['csrf_token'] ?? '';

        if (!$sessionToken || !$postedToken || !hash_equals($sessionToken, $postedToken)) {
            header('Location: ' . $redirect);
            exit;
        }
    }
}
