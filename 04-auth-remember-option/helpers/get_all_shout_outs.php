<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * get_all_shout_outs: Helper function to get all shout-outs.
 * @return array
 */
function get_all_shout_outs()
{
    // prepare shout-outs
    $shout_outs = [];

    // get shout-outs
    if(isset($GLOBALS['conn'])) {
        // query for all shout-outs
        $stmt = null;
        if(isset($GLOBALS['profile'])) {
            // user shout-outs
            if(isset($GLOBALS['tab'])) {
                if($GLOBALS['tab'] == 'received') {
                    // received by specific user
                    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `shoutouts` WHERE `user_mode` = 'selected' AND `selected_user` = ? ORDER BY `id` DESC");
                    $stmt->bind_param("i", $GLOBALS['profile']['id']);
                }
                else if($GLOBALS['tab'] == 'sent') {
                    // sent by specific user
                    $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `shoutouts` WHERE `user_id` = ? ORDER BY `id` DESC");
                    $stmt->bind_param("i", $GLOBALS['profile']['id']);
                }
            }
        }
        else {
            // all shout-outs
            $stmt = $GLOBALS['conn']->prepare("SELECT * FROM `shoutouts` ORDER BY `id` DESC");
        }
        if($stmt != null) {
            $stmt->execute();
            $result = $stmt->get_result();
            while($row = $result->fetch_assoc()) {
                // push every $row to $shout_outs
                $shout_outs['s-' . $row['id']] = $row;
            }
        }
    }

    // return shout-outs
    return $shout_outs;
}