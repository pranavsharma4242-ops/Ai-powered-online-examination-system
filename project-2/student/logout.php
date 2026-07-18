<?php
// student/logout.php

// 1) Resume the session so we can clear it
session_start();

// 2) Clear all session variables
$_SESSION = [];

// 3) If sessions use cookies, delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4) Destroy the session on the server
session_destroy();

// 5) Redirect the browser to the login form (one level up)
header("Location: ../login.php");
exit;
