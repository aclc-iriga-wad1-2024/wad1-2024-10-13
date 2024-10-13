<?php
/**
 * Auth on Session using Plain Password on Database [Example 1]
 * get-all-users: Helper to get all users
 *                (assumes session and database connection are already been initialized)
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