<?php
// start the session
session_start();

// unset all session vars
session_unset();

// destroy session
session_destroy();
header("Location: index.php");

?>