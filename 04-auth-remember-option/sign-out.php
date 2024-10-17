<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * sign-out: Sign out handler
 */

// initialize session
session_start();

// destroy session and remembered data when the sign-out form is submitted
if(isset($_POST['sign-out']))
{
    session_destroy();
    $cookie_expiration = time() - 3600; // 1 hour ago
    setcookie('example4_username', '', $cookie_expiration);
    setcookie('example4_password', '', $cookie_expiration);
}

// redirect to homepage
header('location: sign-in.php');
exit();
?>