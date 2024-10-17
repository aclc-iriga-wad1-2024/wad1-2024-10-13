<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * get-user-data: Helper to get signed-in user data
 *                (assumes session and database connection are already been initialized)
 */

// prepare global array that contains signed-in user data
$user = [
    'id'        => 0,
    'firstname' => '',
    'lastname'  => '',
    'email'     => '',
    'username'  => '',
    'password'  => ''
];

if(isset($_SESSION['example4_username']) && isset($_SESSION['example4_password']) && isset($conn))
{
    // query for the user with the stored username and hashed password from session
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
    $stmt->bind_param("ss", $_SESSION['example4_username'], $_SESSION['example4_password']);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        // populate signed-in user data
        $row = $result->fetch_assoc();
        $user['id']        = intval($row['id']);
        $user['firstname'] = $row['firstname'];
        $user['lastname']  = $row['lastname'];
        $user['email']     = $row['email'];
        $user['username']  = $row['username'];
        $user['password']  = $row['password'];
    }
    else {
        // force sign-out
        session_destroy();
        $cookie_expiration = time() - 3600; // 1 hour ago
        setcookie('example4_username', '', $cookie_expiration);
        setcookie('example4_password', '', $cookie_expiration);
        header('location: sign-in.php');
    }
}