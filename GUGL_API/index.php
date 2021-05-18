<?php

include("connection.php");
include("utilities.php");

// ensure returns JSON format
header('Content-Type: application/json');
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );
}

echo "Welcome to the GÜGL API!";



?>