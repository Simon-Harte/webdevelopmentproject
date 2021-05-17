<?php

/*
TODO

1.
2.
3.


*/
// start session
session_start();

// import conn object
include("connection.php");

// import utilities
include("utilities.php");

// import search bar
include("searchBar.php");

// flag for admin page
$adminPage = FALSE;


/*
OK so I appreciate this method of getting the genre IDs is super janky. I originally planned on having more genres but
I whittled it down to 6 to make it easier to deal with (plus I thought the genre page looked so good I kinda got attached to it)
I plan to revisit this down the line
*/


// assign the genre IDs to vars
$socialID = getGenreID($conn, 'social');
$entertainmentID = getGenreID($conn, 'entertainment');
$musicID = getGenreID($conn, 'music');
$officeID = getGenreID($conn, 'office');
$artsID = getGenreID($conn, 'arts');
$gameID = getGenreID($conn, 'game');


?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>
        <?php
        // update the title with the search term, if submitted
        if (!isset($_GET['submit'])){
            echo "search - GÜGL";
        } else {
            echo $submittedSearch." results - GÜGL";
        }
       
        ?>
        </title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php
            include("header.php");
        ?>


        <div class="content search container">
                
            <?php
            
            
            
                // check if a search has been committed
            if (!isset($amountOfResults)){
                // if not, echo out the standard search bar 
                echo "<h2>search for an app</h2>";

                searchBar();
                
            } else {
                // if a search has been commited
                if ($amountOfResults == 0){
                    // if no results, notify and prompt for another search
                    echo "<h3 class='text-center'>oops! no search results for '".$submittedSearch."'</h3>";

                    searchBar();
                } else {
                    // otherwise echo out the results
                    echo "<h2>search for an app</h2>";
                    searchBar();
                    echo "<h3>".$amountOfResults." search results for '".$submittedSearch."'</h3>";
                    echo "<div class='container'>";
                    searchNav();
                    /* 
                        This page is populated using JavaScript - it's not the best method
                        but I like to think it shows some interesting problem-solving techniques and interesting solutions.
                        It can be found in the script.js
    
                    */
                    echo "<div class='row genre-row' id='search-results'>
                    <div id='applist'>
                    </div>
                    </div>";
                        echo "</div>";
                }
            }

            ?>

        </div>
        <div class="content palette">
            <div class="container">
                <h2>browse apps by category</h2>
                <div class="row">
                    <?php
                    // echo the tiles with the proper links
                    echo "
                <div class=\"col-md-2 col-sm-3 col-xs-4\">
                    <a class=\"panel-link\" href=\"genre.php?genreid=$socialID\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"social\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">Social</h3>
                            </div>
                            <div class=\"panel-body\">


                            </div>
                        </div>
                    </a>
                </div>

                <div class=\"col-md-2 col-sm-3 col-xs-4\">
                    <a class=\"panel-link\" href=\"genre.php?genreid=$entertainmentID\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"entertainment\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">Entertainment</h3>
                            </div>
                            <div class=\"panel-body\">

                            </div>
                        </div>
                    </a>
                </div>
                <div class=\"col-md-2 col-sm-3 col-xs-4\">
                    <a href=\"genre.php?genreid=$musicID\" class=\"panel-link\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"music\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">Music</h3>
                            </div>
                            <div class=\"panel-body\">

                            </div>
                        </div>
                    </a>
                </div>
                <div class=\"col-md-2 col-sm-3 col-xs-4\">
                    <a href=\"genre.php?genreid=$officeID\" class=\"panel-link\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"office\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">Office</h3>
                            </div>
                            <div class=\"panel-body\">


                            </div>
                        </div>
                    </a>
                </div>

                <div class=\"col-md-2 col-sm-3  col-xs-4\">
                    <a href=\"genre.php?genreid=$artsID\" class=\"panel-link\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"arts\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">arts</h3>
                            </div>
                            <div class=\"panel-body\">

                            </div>
                        </div>
                    </a>
                </div>
                <div class=\"col-md-2 col-sm-3  col-xs-4\">
                    <a href=\"genre.php?genreid=$gameID\" class=\"panel-link\">
                        <div class=\"panel panel-primary app-panel shadow\" id=\"game\">
                            <div class=\"panel-heading\">
                                <h3 class=\"panel-title\">game</h3>
                            </div>
                            <div class=\"panel-body\">

                            </div>
                        </div>
                    </a>
                </div>";

                ?>
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
        <script type='text/javascript'>
                     
            var appList = <?= $appsJSON ?>;
            var admin = false;

        </script>
        <script src="script.js"></script>
    </body>
    

    </html>