<?php
/**
 * Auth on Cookie using Hashed Password on Database [Example 3]
 * get-all-shout-outs: Helper to get all shout-outs.
 *                (assumes cookie and database connection are already been initialized)
 */

// prepare global array that contains shout-outs data
$shout_outs = [];

if(isset($conn))
{
    // query for all shout-outs
    $stmt = null;
    if(isset($profile)) {
        if(isset($tab)) {
            if($tab == 'received') {
                // received by specific user
                $stmt = $conn->prepare("SELECT * FROM `shoutouts` WHERE `user_mode` = 'selected' AND `selected_user` = ? ORDER BY `id` DESC");
                $stmt->bind_param("i", $profile['id']);
            }
            else if($tab == 'sent') {
                // sent by specific user
                $stmt = $conn->prepare("SELECT * FROM `shoutouts` WHERE `user_id` = ? ORDER BY `id` DESC");
                $stmt->bind_param("i", $profile['id']);
            }
        }
    }
    else {
        // all
        $stmt = $conn->prepare("SELECT * FROM `shoutouts` ORDER BY `id` DESC");
    }
    if($stmt != null) {
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            // push every $row to $shout_outs array
            $shout_outs['s-' . $row['id']] = $row;
        }
    }
}