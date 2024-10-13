<?php
/**
 * Auth on Session using Plain Password on Database [Example 1]
 * sign-out: Sign out handler
 */

// initialize session
session_start();

// destroy session when the sign-out form is submitted
if(isset($_POST['sign-out']))
{
    session_destroy();
}

// redirect to homepage
header('location: sign-in.php');
exit();
?>