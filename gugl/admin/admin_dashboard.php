<?php

/*
TODO

*/

// start the session
session_start();

// import conn object
include("../connection.php");

// admin defaulted to false
$adminLoggedIn = FALSE;


$ADMIN_USERNAME = "administrator";

$ADMIN_PASSWORD = "p#c7o2Ay8";
// repeatedly ask for authorisation
do {
    // if the entered credentials match the relevant details
    if (($_SERVER['PHP_AUTH_USER'] == $ADMIN_USERNAME) && ($_SERVER['PHP_AUTH_PW'] == $ADMIN_PASSWORD)){
        // session variable is true
        $_SESSION['adminLoggedIn'] = TRUE;
        // local variable is true
        $adminLoggedIn = TRUE;

        // grab the admin's API key from the database (not through API - too sensitive data)
        $adminKey = "SELECT APIKey FROM g_admin WHERE Name = 'admin'";
        $adminKey = $conn->prepare($adminKey);
        $adminKey->execute();
        $result = $adminKey->get_result();
        $details = $result->fetch_assoc();
        // add API key to variable
        $_SESSION['adminkey'] = $details['APIKey'];
    } else {
        // if entered details are invalid, notify and close the process
        header("WWW-Authenticate: Basic realm='Admin Dashboard");
        header("HTTP/1.0 401 Unauthorized");
        echo "You need to enter a valid username and password";
        exit;
    }
    
} while (!$adminLoggedIn);
$adminPage = TRUE;
include("../utilities.php");

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>admin dashboard - GÜGL</title>

        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
    <?php
        include("../header.php");
        ?>

        <div class="content container">
            <h2 class='text-center'>admin dashboard</h2>
            <div class="jumbotron parallax apptron" id="welcometron">

                <h1>EDIT APPS</h1>
                <p>View, edit, create and delete app records</p>
                <a href="admin_apps.php" class="btn btn-lg" role="button">EDIT APPS</a>
            </div>
        </div>
        <hr>
        <div class="content container">

            <div class="jumbotron parallax usertron" id="welcometron">

                <h1>EDIT USERS</h1>
                <p>View, edit, create and delete user records</p>
                <a href="admin_users.php" class="btn btn-lg" role="button">EDIT USERS</a>
            </div>

        </div>

        <?php
        include("../footer.php");
        ?>



        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="../js/bootstrap.min.js"></script>
        <script src="../script.js"></script>
    </body>

    </html>