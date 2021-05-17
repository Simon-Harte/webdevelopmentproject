<?php

/*
TODO

1. add a "spotlight" jumbotron for a specific app
2.
3.

*/
// start session and import conn object
session_start();
include("connection.php");
// import utilities
include("utilities.php");

// flag for header file
$adminPage = FALSE;

// use GET to acquire genre ID
$genreID = $_GET['genreid'];

// extract information from API
$genreEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?genreid={$genreID}";
$genreJSON = file_get_contents($genreEndpoint);
$genreApps = json_decode($genreJSON, true);



// find the genre name (a bit clunky - from before the API incorporation)
$genreQuery = "SELECT GenreName FROM g_genre WHERE GenreID = $genreID";
$genreRes = $conn->query($genreQuery);

if (!$genreRes){
    echo $conn->error;
}

$genreName = $genreRes->fetch_assoc()['GenreName'];
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>
            <?php echo $genreName;?> apps - GÜGL</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/bootstrap-horizon.css">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php 
        
        include("header.php");
        ?>


        <div class="content palette">
            <div class="container">
                <?php

           
                echo "<h2>{$genreName} apps</h2>";

                echo "<h3>top rated {$genreName} apps</h3>";

                
                // echo out the results of the genre app search
                foreach($genreApps as $row){
                    echo   "<div class='col-md-2 col-xs-4'>
                            <a href='appPage.php?app_id={$row['id']}' class='panel-link'>
                                <div class='panel panel-primary genre-panel shadow'>
                                    <div class='panel-heading'>
                                        <h3 class='panel-title'>{$row['name']}</h3>
                                    </div>
                                    <div class='panel-body'>
                                        <img src='{$row['image']}'>
                                    </div>
                                    <div class='panel-footer rating'>";
                                    $rating = (int) $row['rating'];
                                    for ($i = 0; $i < $rating; $i++){
                                        echo "<span class='glyphicon glyphicon-star star'></span>";
                                    }
                                    echo "</div>
                                </div>
                            </a>
                        </div>";
                                
                }
                    echo "</div>";
                ?>
                </div>


        </div>
        

        <?php
            include("footer.php");
        ?>



        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="js/bootstrap.min.js"></script>

    </body>
    <script src="script.js"></script>

    </html>