<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * get_all_users: Helper function to get all users
 * @return array
 */
function get_all_users()
{
    // prepare users
    $users = [];

    // get users
    if(isset($GLOBALS['conn'])) {
        // query for all users
        $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `users` ORDER BY `firstname`, `lastname`");
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            // push every $row to $users
            $users['u-' . $row['id']] = [
                'id'        => intval($row['id']),
                'firstname' => $row['firstname'],
                'lastname'  => $row['lastname'],
                'email'     => $row['email'],
                'username'  => $row['username'],
                'avatar'    => file_exists('uploads/avatars/' . $row['avatar']) ? $row['avatar'] : 'no-avatar.jpg'
            ];
        }
    }

    // return users
    return $users;
}