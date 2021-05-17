<?php
session_start();

include("../connection.php");
include("../utilities.php");

if (!$adminLoggedIn){
    header("Location: ../index.php");
}

// remove session var
unset($_SESSION['adminLoggedIn']);

header("Location: ../index.php");
