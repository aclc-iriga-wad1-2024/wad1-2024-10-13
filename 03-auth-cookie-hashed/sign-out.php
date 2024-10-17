<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * sign-out: Sign out handler
 */

// destroy cookie when the sign-out form is submitted
if(isset($_POST['sign-out']))
{
    $cookie_expiration = time() - 3600; // 1 hour ago
    setcookie('example3_username', '', $cookie_expiration);
    setcookie('example3_password', '', $cookie_expiration);
}

// redirect to homepage
header('location: sign-in.php');
exit();
?>