<?php
/*
TODO

1. 
2. 
3.

*/

// start the session
session_start();

// import conn object
include "connection.php";

// import utilities
include "utilities.php";

// flag for header
$adminPage = FALSE;

// if the basket exists, add the ID to a local var
if (isset($_SESSION['basket'])){
    $basketID = $_SESSION['basket']['basketid'];
}

// if the checkout button is clicked
if (isset($_POST['submit'])){
    // checkout endpoint
    $checkoutEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/?checkout";

    // the data to be sent, including the user API key
    $data = array(
        'key'=>$_SESSION['userKey'],
        'user' => $_SESSION['activeUser'],
        'basket'=>$basketID
    );

    // the actual request
    $options = array(
        'http'=> array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content'=>json_encode($data)
        ),
    );

    $context = stream_context_create($options);

    // retrieve the response
    $result = file_get_contents($checkoutEndpoint, false, $context);
    if (!$result){
        echo $conn->error;
    }

    // decode the response - true for associative array
    $result = json_decode($result, true);

    // if checkout key is false
    if (!$result['checkout']){

        // notify of error
        $checkedOut = FALSE;
        $errorMessage = $result['message'];
    } else {
        // otherwise get transaction info
        $checkedOut = true;
        $transaction = $result['transactionid'];


        /*

        So i had originally wanted to download the apps as simple text files as a small detail
        of the checkout process, but i could not find a way to do it without installing outside libraries which was forbidden
        so I'm leaving this here as a sign of effort :P

        https://stackoverflow.com/questions/52410546/create-write-and-download-a-txt-file-using-php/52410710

        https://stackoverflow.com/questions/1754352/download-multiple-files-as-a-zip-file-using-php
        */

        /*
        $download = array();
        // download the "app" - just for a bit of fun :)
        foreach($_SESSION['basket']['contents'] as $item){
            array_push($download, createAppFile($item['id']));
        }
        $zipname = 'YourApps.zip';
        $zip = new ZipArchive();
        $zip->open($zipname, ZipArchive::CREATE);
        foreach($download as $app){
            $zip->addFile($app);
        }
        $zip->close();

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zipname));
        readfile($zipname);
        */

        // delete current basket
        unset($_SESSION['basket']);

        /*
        The createbasket method is called here so the user has a fresh basket waiting after they check out
        */
        createBasket($conn);
        
    }
}

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>your basket - GÜGL</title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php
        include "header.php";
    ?>

            <div class='container content'>
                <h2 class='text-center'>your basket</h1>
                    <div class='row'>
                        <div class='col-md-6 col-md-offset-3'>
                            <?php
                            // prompt for login if the user isn't logged in
                                if (!$loggedIn) {
                                    echo "<h3 class='text-center'> you must log in to view your basket! </h3>
                                    <p> you can login <a href='login.php'>here</a></p>";
                                    
                                    // if they have checked out, let them know their success
                                } else if (isset($checkedOut) && $checkedOut){
                                    echo "<h3 class='text-center'> success! </h3>
                                    <p> thank you for shopping at GÜGL - enjoy your apps! </p>
                                    <p> transaction ref: $transaction </p>
                                    ";
                                    // if the user's basket's contents amounts to zero, prompt to go to the store
                                } else if (!(count($_SESSION['basket']['contents']) > 0) ){
                                    echo "<h3 class='text-center'> your basket is empty! </h3>
                                    <p> why not visit our <a href='search.php'>store</a> and add some apps to your basket?</p>
                                    ";
                                } else {
                                    // if none of the above criteria match, show the user their items
                                    $i = 1;
                                    $basketTotal = 0;
                                    
                                    foreach ($_SESSION['basket']['contents'] as $item) {

                                       // each app record is populated from the API
                                        $appEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?id={$item['id']}";
                                        // decode the information
                                        $appInfo = json_decode(file_get_contents($appEndpoint), true)[0];
                                        $description = substr($appInfo['description'], 0, 100);
                                        $ratingCount = count($appInfo['reviews']);

                                        // this includes a "remove" button which works via JQuery's AJAX function, visible in the script.js file, which again queries the API.
                                        echo "<form method='POST'><div class='media'>
                                        
                                        <div class='media-left media-middle'>
                                          <a href='appPage.php?id=app_id={$appInfo['id']}'>
                                            <img class='media-object' src='{$appInfo['image']}' alt='{$appInfo['name']} icon thumbnail' width='100px'>
                                          </a>
                                        </div>
                                        <div class='media-body'>
                                          <h3 class='media-heading' style='font-weight: bold;' class='account-prompt'><a href='appPage.php?id=app_id={$appInfo['id']}'>{$i}. {$appInfo['name']}</a></h3><button class='pull-right btn btn-sm remove-item'><span class='glyphicon glyphicon-remove'><input type='hidden' id='itemid' value='".$appInfo['id']."'><input type='hidden' id='basketid' value='".$basketID."'></span></button>
                                          <p>{$description} <a href='appPage.php?id=app_id={$appInfo['id']}'>...</a></p>
                                          <p class='pull-left'><span class='glyphicon glyphicon-star'></span>{$appInfo['rating']} ({$ratingCount})</p><p class='pull-right' > price: {$appInfo['price']}</p>
                                        </div>
                                        </div>";
                                        $i++;
                                        $basketTotal += $appInfo['price'];
                                   }

                                   echo "<h4 class='pull-right' style='font-weight: bold;'>basket total: {$basketTotal}</h4>";
                                   echo "<button type='submit' name='submit' class='btn btn-block btn-primary'>CHECKOUT</button>
                                   </form>";
                                }
                            
                            
                            ?>
                        </div>
                    </div>
            </div>

            <?php
        include "footer.php";
    ?>
                <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
                <!-- Include all compiled plugins (below), or include individual files as needed -->
                <script src="js/bootstrap.min.js"></script>
                <script type='text/javascript'>
                    var userKey = '<?=  $_SESSION['userKey'] ?>';
                </script>
                <script src="script.js"></script>
    </body>

    </html>