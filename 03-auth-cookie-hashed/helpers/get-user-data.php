<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * get-user-data: Helper to get signed-in user data
 *                (assumes cookie and database connection are already been initialized)
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

if(isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']) && isset($conn))
{
    // query for the user with the stored username and hashed password from cookie
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
    $stmt->bind_param("ss", $_COOKIE['example3_username'], $_COOKIE['example3_password']);
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
        $cookie_expiration = time() - 3600; // 1 hour ago
        setcookie('example3_username', '', $cookie_expiration);
        setcookie('example3_password', '', $cookie_expiration);
        header('location: sign-in.php');
    }
}