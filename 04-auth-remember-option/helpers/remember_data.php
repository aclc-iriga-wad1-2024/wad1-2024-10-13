<?php
/**
 * Auth with Remember Option using Hashed Password on Database [Example 4]
 * remember_data: Helper function to remember data from cookies, if no session is set.
 * @return void
 */
function remember_data()
{
    if(!isset($_SESSION['example4_username']) || !isset($_SESSION['example4_password'])) {
        if(isset($_COOKIE['example4_username']) && isset($_COOKIE['example4_password'])) {
            $_SESSION['example4_username'] = $_COOKIE['example4_username'];
            $_SESSION['example4_password'] = $_COOKIE['example4_password'];
        }
    }
}