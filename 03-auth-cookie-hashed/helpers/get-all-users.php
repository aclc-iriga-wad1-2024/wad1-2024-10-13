<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * get-all-users: Helper to get all users
 *                (assumes cookie and database connection are already been initialized)
 */

// prepare global array that contains users data
$users = [];

if(isset($conn))
{
    // query for all users
    $stmt = $conn->prepare("SELECT * FROM `users` ORDER BY `firstname`, `lastname`");
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
        // push every $row to $users array
        $users['u-' . $row['id']] = [
            'id'        => intval($row['id']),
            'firstname' => $row['firstname'],
            'lastname'  => $row['lastname'],
            'email'     => $row['email'],
            'username'  => $row['username']
        ];
    }
}