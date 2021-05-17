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
include("../connection.php");

// import the utilities
include("../utilities.php");

// check if admin is logged in
if (!$adminLoggedIn){
    // if not, redirect
    header('Location: ../index.php');
} 

// admin page flag for the page header
$adminPage = TRUE;
// get the app ID from the url
$OldappID = $_GET['app_id'];

/*
    So i had plans to encrypt the app IDs for sending over
    http requests so that if intercepted, the raw record numbers
    wouldn't be visible. This is an example of the process.
*/

// encrypt the app ID for sending through HTTP
$appID = openssl_encrypt($OldappID, 'AES-128-ECB', 'appsid');

// flags for decisions later on
$edited = FALSE;
$updated = FALSE;
$updateIssue = FALSE;
$deleteIssue = FALSE;

// if the edit button is clicked
if (isset($_POST['edit'])){
    

    // edits have been made
    $edited = TRUE;
    // set the endpoint to send the edit request to
    $editEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/";

    // grab the admin's API key to authorise
    $adminKey = $_SESSION['adminkey'];

    // the body of the request
    $data = array(
        'key'=>$adminKey,
        'title'=>$_POST['title'],
        'publisher'=>$_POST['publisher'],
        'description'=>$_POST['description'],
        'price'=>$_POST['price'],
        'id'=>$appID
    );

    // the request whole
    $options = array(
        'http'=> array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'PUT',
            'content' => json_encode($data)
        ),
    );

    $context = stream_context_create($options);

    // get the contents of the request
    $result = file_get_contents($editEndpoint, false, $context);

    /// if there is an issue, notify.
    if (!$result){
        echo "endpoint error";
    }

    // decode the result. true for an associative array
    $result = json_decode($result, true);
    // if the update key is true
    if ($result['update']){

        // success! redirect to app page
        header("Location: ../appPage.php?app_id=$OldappID");
    } else {

        // otherwise flag for notification later
        $updateIssue = TRUE;
    }
}

// if the delete button is clicked
if(isset($_POST['delete'])){
   
    // the endpoint to send the delete request to
    $deleteEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/";

    // grab the admin's API key for authorisation
    $adminKey = $_SESSION['adminkey'];

    // the request body - only two items needed
    $data = array(
        'key'=>$adminKey,
        'id'=>$appID
    );

    // the request whole
    $options = array(
        'http'=> array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'DELETE',
            'content' => json_encode($data)
        ),
    );

    $context = stream_context_create($options);

    // get the response from the API
    $result = file_get_contents($deleteEndpoint, false, $context);
    if (!$result){
        echo "API endpoint error";
    }

    // decode the result - true for associative array
    $result = json_decode($result, true);

    // if the delete key is true
    if ($result['delete']){

        // redirect
        header("Location: admin_apps.php");
    } else {

        // otherwise notify of issue
        $deleteIssue = TRUE;
    }
}


// the endpoint for the app details
$appEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?id={$OldappID}";
//$reviewEndpoint= "http://sharte14.lampt.eeecs.qub.ac.uk/reportsitev5/api/?reviews&app_id={$appID}";

// get the response
$appJSON = file_get_contents($appEndpoint);
//$reviewsJSON = file_get_contents($reviewEndpoint);

// decode the response
$appDetails = json_decode($appJSON, true)[0];

// review details are included in the app result
$reviewDetails = $appDetails['reviews'];




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

        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/bootstrap-horizon.css">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php 
        
        include("../header.php");

        ?>

        <div class="content appinfo">
            <div class="container">
                <div class="row" id="product">
                    <?php 
                
                // get the details for the app
                $imgSrc = $appDetails['image'];
                $appName = $appDetails['name'];
                $publisher = $appDetails['publisher'];
                $description = $appDetails['description'];

                // true rating calculated from the review array
                $rating = getTrueRating($reviewDetails);

                $price = $appDetails['price'];
                

                // notify if issues occur
                if ($updateIssue){
                    echo "<h4 style='color: red;'>sorry, there was a problem updating this app.</h2>";
                }
                if ($deleteIssue){
                    echo "<h4 style='color: red;'>sorry, there was a problem deleting this app.{$result['message']}</h2>";
                }
                echo "
                
                <div class='col-md-4 col-md-offset-2 col-sm-4 col-sm-offset-2 col-xs-10 col-xs-offset-1 picturecol  no-gutter'><img alt='app logo' class='img-rounded app-logo' id='icon' src='{$imgSrc}'>
                </div>
                <form method='POST'>
                <input value='$appID' type='hidden' name='sentid' class='form-control'> 
                <div class='col-md-5 col-sm-4 col-xs-12 col descriptioncol  no-gutter'>
                    <h2 id='app-title'><input type='text' value='{$appName}' name='title'></input> <small id='app-dev'><input value='{$publisher}' type='text' name='publisher'></input></small></h2>
                    <p id='app-description'><textarea rows='10' cols='60' type='textarea' name='description'>{$description}</textarea></p>
                    <div id='rating'><span class='glyphicon glyphicon-star'></span><span>{$rating}</span></div>
                    <h4>price: <span id='price'><input type='number' step='.01' name='price' value='{$price}'></input></span></h4>
                    <div class='btn btn-default'>BUY</div>
                    <div class='btn btn-default' id='basket-btn'>ADD TO BASKET</div>
                   <button type='submit' class='btn btn-default admin-button' id='edit-button' name='edit'>SUBMIT</button>
                   <button type='submit' class='btn btn-default admin-button' id='delete-button' name='delete'>DELETE</button>
                    </div></form>";
                
                
                
            
                ?>

                </div>
            </div>
        </div>
        <hr>
        <div>
            <div class="container reviews-block">
                <?php
            //$reviewCount = $reviewDetails->num_rows;
            $reviewCount = count($reviewDetails);
            echo "<h3>reviews </h3><span id='review-count'>{$reviewCount} total</span>";
            ?>

                    <div class="row reviews row-horizon">
                        <?php
                foreach ($reviewDetails as $row){
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
                            echo "{$firstWords} '...'";
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
            include("../footer.php");
        ?>


        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="../js/bootstrap.min.js"></script>
        
        <script src="../script.js"></script>
        
        <script src="adminScript.js"></script>
    </body>


    </html>