<?php
/*
TODO

1.fix underlining issue on .account-prompt
2.
3.




*/
// start the session
session_start();

// import conn object
include("connection.php");

// import utilities
include("utilities.php");

// admin page flag for header
$adminPage = FALSE;




?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>home - GÜGL</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php 
        
        include("header.php");
        ?>
        
        
        <div class="content">

            <div class="container">
                <div class="jumbotron parallax apptron" id="welcometron">

                    <h1>WELCOME</h1>
                    <p>Here at GÜGL, we pride ourselves on our selection of the <i>finest</i> apps on the market.</p>
                    <a href="search.php" class="btn btn-lg" role="button">ENTER OUR STORE</a>
                </div>
            </div>

        </div>

        <?php
            include("footer.php");
        ?>



        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>
        <script src="script.js"></script>
    </body>

    </html>