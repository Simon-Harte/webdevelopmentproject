<?php
/*
TODO

1. Connect reviews to reviews page?
2.
3.




*/
// start the session
session_start();

// import the conn object
include("connection.php");

// import utilities
include("utilities.php");

// flag used for the header
$adminPage = FALSE;

// get the app id from the url
$appID = $_GET['app_id'];


// app endpoint
$appEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?id={$appID}";

/*
    The reviews can also be retrieved from the app endpoint:
    it returns an array of reviews for each app. This can be seen in 
    the admin/editApp.php
*/

// review endpoint
$reviewEndpoint= "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/reviews/?app_id={$appID}";

// get the repsonses from the endpoints
$appJSON = file_get_contents($appEndpoint);
$reviewsJSON = file_get_contents($reviewEndpoint);

// decode the endpoints - true for associative arrays
$appDetails = json_decode($appJSON, true)[0];
$reviewDetails = json_decode($reviewsJSON, true);


?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>
            <?php echo $appDetails['name']; ?> - GÜGL</title>

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

        <div class="content appinfo">
            <div class="container">
                <div class="row" id="product">
                    <?php 
                    // get the details for each app
                $imgSrc = $appDetails['image'];
                $appName = $appDetails['name'];
                $publisher = $appDetails['publisher'];
                $description = $appDetails['description'];

                // rating is dynamically generated from the reviews
                $rating = getTrueRating($reviewDetails);
                
                // if the price is 0, output "free"
                $price = $appDetails['price'] == '0.00' ? "FREE" : $appDetails['price'];
                

                
                echo "
                <div class='col-md-4 col-md-offset-2 col-sm-4 col-sm-offset-2 col-xs-10 col-xs-offset-1 picturecol  no-gutter'><img alt='app logo' class='img-rounded app-logo' id='icon' src='{$imgSrc}'>
                </div>
                <input type='hidden' id='appid' value='{$appID}'>
                <div class='col-md-5 col-sm-4 col-xs-12 col descriptioncol  no-gutter'>
                    <h2 id='app-title'>{$appName} <small id='app-dev'>{$publisher}</small></h2>
                    <p id='app-description'>{$description}</p>
                    <div id='rating'>";
                    echo ($rating == 0) ? "no ratings" : "<span class='glyphicon glyphicon-star'></span><span>{$rating}</span>";
                    echo "</div>
                    <h4>price: <span id='price'>{$price}</span></h4>
                    ";
                    if (!$loggedIn){
                        echo "<div class='btn btn-default' id='basket-btn' disabled>ADD TO BASKET</div><span>you must be logged in to use this feature</span>";
                    } else {
                        echo "<div class='btn btn-default' id='basket-btn'>ADD TO BASKET</div>";
                    }
                    
                    if ($adminLoggedIn){
                        echo "<a type='button' class='btn btn-default' id='edit-button' href='admin/editApp.php?app_id={$appID}'>EDIT</a>";
                    }
                echo "</div>";
                
                
                
            
                ?>

                </div>
            </div>
        </div>
        <hr>
        <div>

            <div class="container content reviews-block">
                <div class='row  
                <?php
                    // determines if the current user has already reviewed this app
                    
                    foreach($reviewDetails as $review){
                        
                        if ($review['userid'] == $_SESSION['activeUser']){
                            
                            echo 'hide';
                            break;
                        }
                    }
                ?>
                   '> <div class='col-md-4 col-sm-8 col-xs-12'>
                        <div id='new-review'>
                            <h2>add a review</h2>
                            <form id='review-form'>
                                <div class='form-group'>
                                <?php if(!$loggedIn) echo "<h3 style='color:red;'> you must be logged in to leave reviews</h3>";?>
                                    <label for='content'>review content (optional)</label>
                                    <textarea type='textarea' rows=5 class='form-control' id='content' placeholder='write your review' name='content'  <?php if(!$loggedIn) echo "disabled='disabled'";?>></textarea>

                                </div>
                                <label for="rating">rating (required)</label>
                                <div class='form-group' id='review-rating' required>
                                <label class="radio-inline">
                                    <input type="radio" name="review-rating" value=1 <?php if(!$loggedIn) echo "disabled='disabled'";?>>1
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="review-rating" value=2 <?php if(!$loggedIn) echo "disabled='disabled'";?>>2
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="review-rating" value=3 <?php if(!$loggedIn) echo "disabled='disabled'";?>>3
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="review-rating" value=4 <?php if(!$loggedIn) echo "disabled='disabled'";?>>4
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="review-rating" value=5 <?php if(!$loggedIn) echo "disabled='disabled'";?>>5
                                </label>
                                </div>
                                <button type='button' class='btn btn-default' id='review-submit' disabled='disabled'>SUBMIT</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            $reviewCount = count($reviewDetails);
            echo "<h3>reviews </h3><span id='review-count'>{$reviewCount} total</span>";
            ?>

                    <div class="row reviews row-horizon">
                        <?php
                        
                foreach($reviewDetails as $row){
                    $rating = $row['rating'];
                    $content = $row['content'];
                    $firstWords = substr($content, 0, 15);
                    $bodyPreview = substr($content, 0, 60);
                    echo "<div class='col-md-2 col-sm-3 col-xs-6'>
                    <div class='panel panel-default review'>
                        <div class='panel-heading review-title'>";
                        if (!$firstWords){
                            echo $content;
                        } else {
                            echo "{$firstWords} ...";
                        }
                            
                        echo "</div>
                        <div class='panel-body review-body'>";
                        if (!$firstWords){
                            echo $content;
                        } else {
                            echo "{$bodyPreview} ... <a href='#' class='read-more'>read more</a>";
                        }
                            
                        echo "</div>
                        <div class='panel-footer'>";
                        for ($i = 0; $i < $rating; $i++){
                            echo "<span class='glyphicon glyphicon-star'></span>";
                        }
                        echo "</div>

                    </div>
                </div>";
                }

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
                var basketID = <?= $_SESSION['basket']['basketid']?>;
                var userID = <?= $_SESSION['activeUser'] ?>;
                var userKey = '<?= $_SESSION['userKey'] ?>';
            </script>
            <script src="script.js"></script>

    </body>


    </html>