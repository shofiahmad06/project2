<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbb      ="registration";

// Create connection
$db = new mysqli($servername, $username, $password,$dbb);

// Check connection
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
echo "Connected successfully";
?>