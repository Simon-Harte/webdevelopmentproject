<?php

$username = "sharte14";

$pw = "**************";
$host = "sharte14.lampt.eeecs.qub.ac.uk";


$db = $username;

$conn = new mysqli($host, $username, $pw, $db);

if(!$conn){
    echo $conn->error;
    die(); // php is kill
} 




?>
