<?php

/*
TODO


*/

// start the session
session_start();

// import conn object
include("../connection.php");
// import utilities
include("../utilities.php");
// import the search bar
include("../searchBar.php");
// redirect if admin is not logged in
if (!$adminLoggedIn){
    header("Location: admin_dashboard.php");
}

// this is an admin page: flag for the site header
$adminPage = TRUE;

// runs when a new app is created
if (isset($_POST['submit-new'])){
    // the endpoint to send new app info to
    $newAppEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/";

    // the data holding the new app information, including the admin's API key
    $data = array(
        'key' =>$_SESSION['adminkey'],
        'title'=>$_POST['title'],
        'publisher'=>$_POST['publisher'],
        'description'=>$_POST['description'],
        'genre'=>$_POST['genre'],
        'age-restrict' => $_POST['age-restrict'],
        'image'=>$_POST['image'],
        'price'=>$_POST['price']
    );


    // create request
    $options = array(
    'http'=> array(
        'header'=>"Content-type: application/x-www-form-urlencoded",
        'method'=>'POST',
        'content'=>json_encode($data),
        ),
    );
  

    $context = stream_context_create($options);

    // get the result of the response
    $result = file_get_contents($newAppEndpoint, false, $context);
    // if theres an error, mention it
    if (!$result){
        echo "problem with API endpoint"; 
    }

    // decode the result into an associative array
    $result = json_decode($result, true);
    // if the create key is false
    if (!$result['create']){

        // flag for notification
        $createIssue = TRUE;
        $errorMessage = $result['message'];
    } else {
        // otherwise redirect to the new app page
        $newAppID = $result['id'];
        header("Location: ../appPage.php?app_id=$newAppID");
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
        <title>admin apps - GÜGL</title>

        <link href="../css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php
        include("../header.php");
        ?>

            <div class="content container text-center">
                <button class='text-center btn btn-lg btn-primary' id='add-app'>add new app</button>
                <div id='new-app'>

                    <form method='POST'>
                        <div class='form-group'>
                            <div class='col-md-4 col-md-offset-4 col-sm-4 col-sm-offset-4 col-xs-12 col text-center'>
                                <h2 id='app-title' > app name: <input type='text' class='form-control' placeholder='<?php echo $DEFAULT_APP_NAME ?>' name='title' required></input> <small id='app-dev'>publisher: <input placeholder='<?php echo $DEFAULT_PUBLISHER; ?>' class='form-control' type='text' name='publisher' required></input></small></h2>
                                <h4 id='app-description'>description: <textarea class='form-control' rows='10' cols='60' type='textarea' name='description' placeholder='<?php echo $DEFAULT_DESCRIPTION; ?>' required></textarea></h4>
                                <h4 id='app-description'>genre: <h4>
                                <select name='genre' id='genre' class='form-control' required>
                                    <option value='' disabled selected>select the genre</option>
                                    <option value='1'>social</option>
                                    <option value='2'>entertainment</option>
                                    <option value='3'>music</option>
                                    <option value='4'>office</option>
                                    <option value='5'>arts</option>
                                    <option value='6'>game</option>

                                </select>
                                <h4 id='app-description'>age restricted: <h4>
                                <select name='age-restrict' id='age-restrict'  class='form-control' required>
                                    <option value='' disabled selected>select age restriction option</option>
                                    <option value='0'>no</option>
                                    <option value='1'>yes</option>
                                </select>
                                <h4>price: <span id='price'><input class='form-control' type='number' step='.01' name='price' placeholder='<?php echo $DEFAULT_PRICE; ?>' required></input></span></h4>
                                <h4>image url: <input type='text' class='form-control' placeholder='enter image url' name='image' required></input>
                                </h4>
                                <button type='submit' class='btn btn-default' id='submit' name='submit-new' disabled='disabled'>SUBMIT</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class='content search container'>
                <?php
            
            
            
            // check if a search has been committed
        if (!isset($_GET['submit'])){
            // if not, echo out the standard search bar 
            echo "<h2>search for an app to edit</h2>";

            searchBar();
            
        } else {
            // if a search has been commited
            if ($amountOfResults == 0){
                // if no results, notify and prompt for another search
                echo "<h2>search for an app to edit</h2>";

                echo "<h3 class='text-center'>oops! no search results for ".$searchTerm."</h3>";
                
                searchBar();
            } else {
                // otherwise echo out the results
                echo "<h2>search for an app to edit</h2>";
                searchBar();
                echo "<h3>".$amountOfResults." search results for ".$searchTerm."</h3>";
                echo "<div class='container'>";
                searchNav();
                echo "<div class='row genre-row' id='search-results'>
                <div id='applist'>
                </div>
                </div>";
                    echo "</div>";
            }
        }

        ?>
            </div>



            <?php
        include("../footer.php");
        ?>



                <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
                <!-- Include all compiled plugins (below), or include individual files as needed -->
                <script src="../js/bootstrap.min.js"></script>
                <script type='text/javascript'>
                    var appList = <?= $appsJSON ?>;
                    var admin = true;
                </script>
                <script src="../script.js"></script>
    </body>

    </html>