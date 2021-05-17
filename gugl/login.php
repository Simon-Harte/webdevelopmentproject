<?php

/*
TODO

1. customise login/signup errors from the API
2. 
3. credit: https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php

 */
// start the session
session_start();

// import conn object
include "connection.php";

// import utilities
include "utilities.php";

// flag for header
$adminPage = FALSE;

// flags for errors later on
$loginIssue = FALSE;
$signedUp = FALSE;

// triggered if the login-button is clicked
if (isset($_POST["login-button"])) {
    // endpoint for logins
    $loginEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/users/";

    // create the data to be sent to the API for verification


    $data = array(
        'email' => $_POST['login-email'],
        'password' => openssl_encrypt(
            $_POST['login-password'],
            'AES-128-ECB', 'userid'),
        'process' => 'login');

    // create the actual 'request' array
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content' => json_encode($data),
        ),
    );

    // idk what this does but it works
    $context = stream_context_create($options);

    // gets the contents of the API response
    $result = file_get_contents($loginEndpoint, false, $context);
    if (!$result) {
        echo $conn->error;
    }

    // decode result and check the login key
    $result = json_decode($result, true);
    // if the key is true
    if ($result['login']) {
        // update relevant information
        $_SESSION['activeUser'] = $result['userid'];
        $_SESSION['loggedIn'] = true;
        $loggedIn = TRUE;

        // add API key to session
        $_SESSION['userKey'] = $result['key'];
        
       

    } else {
        // otherwise flag for notification
        $loginIssue = TRUE;
        $errorMessage = $result['message'];
    }

}

// if the signup button is clicked
if (isset($_POST["signupbutton"])) {

    // the endpoint for signup
    $signupEndPoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/users/";

    // create the data to be sent to the API for verification
    $data = array(
        'firstname'=>$_POST['signup-firstname'],
        'surname'=>$_POST['signup-surname'],
        'email' => $_POST['signup-email'],
        'password' => openssl_encrypt(
            $_POST['signup-password'], 
            'AES-128-ECB', 
            'userid'), 
        'process' => 'signup');

    // create the actual 'request' array
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content' => json_encode($data),
        ),
    );

    // idk what this does but it works
    $context = stream_context_create($options);

    // gets the contents of the API response
    $result = file_get_contents($signupEndPoint, false, $context);
    if (!$result) {
        echo $conn->error;
    }

    // decode result and check the login key
    $result = json_decode($result, true);
    if ($result['signup']) {
        $signedUp = TRUE;
        
    } 
}

// if already logged in, redirect
if ($loggedIn) {
    header('Location: index.php');
}
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>login - GÜGL</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php

include "header.php";
?>

        <div class="content container login-signup">
            <!-- I finally managed to get these submit buttons to be the same colour and im leaving it for the minute
                 in case i break something else -->
            <div class='row'>
            
                <div class='col-sm-6 col-md-4 col-md-offset-4'>
                <!-- I'm pretty proud of this little switcher - its a little clunky in the javascript (it was one of the first
                problems i tackled regarding javascript and jQuery) but essentially it shows/hides the login/signup divs depending
                on which is clicked. -->
                    <div class='row no-gutter switcher'>
                        <div class='col-md-6 '>
                            <button class='btn btn-block btn-primary btn-lg' id='log' disabled='disabled'>log in</button>
                        </div>
                        <div class='col-md-6'>
                            <button class='btn btn-block btn-primary btn-lg' id='sign'>sign up</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class='signup hide' id='sign-up'>

                <div class="row">

                    <div class="col-sm-6 col-md-4 col-md-offset-4">
                        <div class='signup-form'></div>
                        <form method='POST'>
                            <h2 class='text-center'>sign up</h2>
                            <?php 
            
                                if (isset($_POST['signup-button']) && !$signedUp){
                                    
                                        echo "<h3 style='color:red;'>we already have a record for you! please log in instead</h3>";
                                    
                                }
                            ?>
                            <div class='form-group'>
                                <label for='signup-firstname' class='sr-only'>First Name</label>
                                <input type='text' class='form-control' placeholder='First name' required name='signup-firstname' id='signup-firstname'>
                            </div>
                            <div class='form-group'>
                                <label for='signup-surname' class='sr-only'>Surname</label>
                                <input type='text' class='form-control' placeholder='Surname' required name='signup-surname' id='signup-surname'>
                            </div>
                            <div class='form-group'>
                                <label for='signup-email' class='sr-only'>Email</label>
                                <input type='email' class='form-control' placeholder='Email' required name='signup-email' id='signup-email'>
                            </div>
                            <div class='form-group'>
                                <label for='signup-password1' class='sr-only'>Password</label>
                                <input type='password' class='form-control' placeholder='Password' required name='signup-password' id='signup-password'>

                            </div>
                            
                            <div class='form-group'>
                                <label for='signupbutton' class='sr-only'>Submit button</label>
                                <button type='submit' class='btn btn-primary btn-block btn-lg' name='signupbutton' id='signupbutton'>sign up</button>
                            </div>
                            <div class='clearfix'>

                            </div>
                        </form>

                    </div>


                </div>



            </div>
            <div class='signup' id='log-in'>

                <div class="row">

                    <div class="col-sm-6 col-md-4 col-md-offset-4">
                        <div class='signup-form'>
                            <form method='POST'>
                                <h2 class='text-center'>log in</h2>
                                <?php 
            
                                    if (isset($_POST['login-button']) && isset($errorMessages)){
                                        if ($errorMessage){
                                            echo "<h3 style='color:red;'>{$errorMessage}</h3>";
                                        } 
                                    }
                                ?>
                                <div class='form-group'>
                                    <label for='login-email' class='sr-only'>Email</label>
                                    <input type='email' class='form-control' placeholder='Email' required name='login-email' id='login-email'>
                                </div>
                                <div class='form-group'>
                                    <label for='login-password' class='sr-only'>Password</label>
                                    <input type='password' class='form-control' placeholder='Password' required name='login-password' id='login-password'>
                                </div>

                                <div class='form-group'>
                                    <label for='login-button' class='sr-only'>Submit button</label>
                                    <button type='submit' class='btn btn-primary btn-block btn-lg' name='login-button' id='login-button'>log in</button>
                                </div>
                                <div class='clearfix'>

                                </div>
                            </form>
                            <div class='text-center'><button class='btn btn-sm btn-info' id='createacc'>create an account</button></div>
                        </div>


                    </div>



                </div>
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
        <script src="script.js"></script>
    </body>

    </html>