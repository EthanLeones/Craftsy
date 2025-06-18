<?php
$host = 'localhost';
$dbname = 's22101184_craftsy';
$username = 's22101184_craftsy';
$password = 'Inakoy13';

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>