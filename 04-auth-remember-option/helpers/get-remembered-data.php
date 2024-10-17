<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * get-remembered-data: Helper to get remembered data, if no session is set.
 *                      (assumes session has already been initialized)
 */

if(!isset($_SESSION['example4_username']) || !isset($_SESSION['example4_password']))
{
    if(isset($_COOKIE['example4_username']) && isset($_COOKIE['example4_password'])) {
        $_SESSION['example4_username'] = $_COOKIE['example4_username'];
        $_SESSION['example4_password'] = $_COOKIE['example4_password'];
    }
}