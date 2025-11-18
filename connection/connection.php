<?php
$host = 'db';
$username = 'developer';
$pass = 'developer';
$dbn = 'pkwu';

$conn = new mysqli($host, $username, $pass, $dbn);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>