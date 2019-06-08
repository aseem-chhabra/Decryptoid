<?php
require_once 'login.php';
$connection = new mysqli($hn, $un, $pw, $db);
if ($connection->connect_error)
    die($connection->connect_error);
$query = "CREATE TABLE users(
    `username` VARCHAR(20) PRIMARY KEY NOT NULL,
    `password` VARCHAR(32) NOT NULL,
    `email` VARCHAR(30) NOT NULL
    )";
$result = $connection->query($query);
    if (!$result) die(output());
$query = "CREATE TABLE files(
    `id` int AUTO_INCREMENT PRIMARY KEY NOT NULL,
    `username` VARCHAR(20) NOT NULL,
    `fileName` VARCHAR(20),
    `text` LONGTEXT NOT NULL
    )";
$result = $connection->query($query);
    if (!$result) die(printMe());
function output()
{
    echo "To Login <a href = 'loginpage.php'>click here</a>.";
}

?>
