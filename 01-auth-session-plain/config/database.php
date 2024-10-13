<?php
$servername = 'localhost';
$username   = 'root';
$password   = '';
$dbname     = 'wad1-2024-10-13.1';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('DB CONNECTION FAILED: ' . $conn->connect_error);
}