<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * get_user_data: Helper function to get signed-in user data
 * @return array|void
 */
function get_user_data()
{
    // prepare signed-in user data
    $user = [
        'id'        => 0,
        'firstname' => '',
        'lastname'  => '',
        'email'     => '',
        'username'  => '',
        'password'  => ''
    ];

    // process signed-in user data
    if(isset($_COOKIE['example3_username']) && isset($_COOKIE['example3_password']) && isset($GLOBALS['conn'])) {
        // query for the user with the stored username and hashed password from cookie
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `users` WHERE `username` = ? AND `password` = ?");
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
            $user['avatar']    = file_exists('uploads/avatars/' . $row['avatar']) ? $row['avatar'] : 'no-avatar.jpg';
        }
        else {
            // force sign-out
            $cookie_expiration = time() - 3600; // 1 hour ago
            setcookie('example3_username', '', $cookie_expiration);
            setcookie('example3_password', '', $cookie_expiration);
            header('location: sign-in.php');
            exit();
        }
    }

    // return signed-in user data
    return $user;
}