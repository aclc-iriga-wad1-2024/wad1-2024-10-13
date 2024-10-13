<?php
/**
 * Auth on Session using Plain Password on Database [Example 1]
 * get-user-data: Helper to get signed-in user data
 *                (assumes session and database connection are already been initialized)
 */

// prepare global array that contains signed-in user data
$user = [
    'id'        => 0,
    'firstname' => '',
    'lastname'  => '',
    'email'     => '',
    'username'  => ''
];

if(isset($_SESSION['example1_user_id']) && isset($conn))
{
    // query for the user with the stored user_id from session
    $stmt = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
    $stmt->bind_param("i", $_SESSION['example1_user_id']);
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
    }
}