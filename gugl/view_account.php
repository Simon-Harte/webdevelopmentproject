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

include("utilities.php");
if (!$loggedIn) {
    header("Location: index.php");
}
$adminPage = FALSE;


$user = $_SESSION['activeUser'];
$userID = openssl_encrypt($user, 'AES-128-ECB', 'userid');

if (isset($_POST["details-submit"])) {

    // endpoint for user update
    
    $loginEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/users/";

    // create the data to be sent to the API for verification


    $data = array(
        'first-name'=>$_POST['first-name'],
        'surname'=>$_POST['surname'],
        'email' => $_POST['email'],
        'password' => openssl_encrypt(
            $_POST['password'],
            'AES-128-ECB', 'userid'),
        'process' => 'update',
        'key' =>$_SESSION['userKey'],
        'id'=>$userID);

    // create the actual 'request' array
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded",
            'method' => 'PUT',
            'content' => json_encode($data),
        ),
    );
    
    // idk what this does but its required
    $context = stream_context_create($options);

    // gets the contents of the API response
    $result = file_get_contents($loginEndpoint, false, $context);
    if (!$result) {
        echo "problem with API endpoint";
    }

    // decode result and check the login key
    $result = json_decode($result, true);
    if (!$result['update']) {
        $updateIssue = TRUE;
        $errorMessage = $result['message'];
    } 

}

$userSQL = $conn->prepare("SELECT FirstName, Surname, Email, Password FROM g_user WHERE UserID = $user");
//$userSQL->bind_param("i", $user);
$userSQL->execute();
if (!$userSQL){
    echo $conn->error;
} 
$result = $userSQL->get_result()->fetch_assoc();
$userSQL->close();

?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
        <title>view account - GÜGL</title>

        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="css/style.css">
        <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

    </head>

    <body>
        <?php 
        
        include("header.php");
        if (isset($errorMessage)){
            echo $errorMessage;
        }
        ?>


        <div class="content">
            <div class='container' id='edit-account'>
                <div class='text-center'><h2>your details</h2></div>
                    <form method='POST'>
                        <div class='form-group' id='edit-form'>
                            <div class='col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1 col text-center'>
                                <input value='<?= $userID ?>' type='hidden' name='sentid' class='form-control'>
                                <h3><label for='first-name'>first name</label></h3>

                                <input type='text' class='form-control' name='first-name' id='first-name' value='<?= $result['FirstName']?>' required disabled='disabled'>


                                <h3><label for='last-name'>surname</label></h3>

                                <input type='text' class='form-control' name='surname' id='surname' value='<?= $result['Surname']?>' required disabled='disabled'>


                                <h3><label for='email'>email address</label></h3>

                                <input type='email' class='form-control' name='email' id='email' value='<?= $result['Email']?>' required disabled='disabled'>



                                <h3><label for='password1'>password</label></h3>
                                
                                <input type='password' class='form-control' name='password' id='password' value='<?= openssl_decrypt($result['Password'],  'AES-128-ECB', 'userid');?>' required disabled='disabled'>
                                <div class='btn-group btn-group-justified' role='group' style='padding-top: 5px;'>
                                    <div class='btn-group ' role='group' aria-label='...'>
                                        <button type='button' class='btn btn-primary' id='edit' name='edit'>EDIT</button>
                                    </div>
                                    <div class='btn-group ' role='group' aria-label='...'>
                                        <button type='submit' class='btn btn-primary' id='details-submit' name='details-submit' disabled='disabled'>SUBMIT</button>


                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr>
        <div class='container content'>
            <div class='row'>
            <div class='col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 col-xs-10 col-xs-offset-1 col'>
                <h2 class='text-center'>your purchases</h2>
                <?php
                    // get previous baskets

                    $basketEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/?key={$_SESSION['userKey']}&userid={$_SESSION['activeUser']}&active=1";

                    $baskets = file_get_contents($basketEndpoint);
                    $baskets = json_decode($baskets, true);
                    foreach ($baskets as $basket){
                        echo "<blockquote>
                        <h3><strong>{$basket['date']}</strong></h3>";
                        $price = $basket['price'];
                        $itemCount = count($basket['contents']);
                        echo "<p> $itemCount ";
                        if ($itemCount > 1){
                            echo "items";
                        } else {
                            echo "item";
                        }  
                        echo "</p>
                        <span class='pull-right'><p>{$price}</p></span>";
                        echo "</blockquote>";
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
            <script src="script.js"></script>
    </body>

    </html>