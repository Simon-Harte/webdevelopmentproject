<?php

// determine if admin is logged in
$adminLoggedIn = (isset($_SESSION['adminLoggedIn']));

// determine if user is logged in
$loggedIn = isset($_SESSION['activeUser']);
if ($loggedIn){
    $userQuery = "SELECT * FROM g_user WHERE UserID = {$_SESSION['activeUser']}";
    $results = $conn ->query($userQuery);

    if (!$results){
        echo $conn->error;
    }
    $results = $results->fetch_assoc();

}

// get all basket information 

if ($loggedIn){
    // get current basket information from the API
    $basketEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/?key={$_SESSION['userKey']}&userid={$_SESSION['activeUser']}&active=0";
    // gets the contents of the API response
    $basketResult = file_get_contents($basketEndpoint);
    if (!$basketResult) {
        echo $conn->error;
    }
    // decode result and check the basket
    $basketResult = json_decode($basketResult, true);
    if ($basketResult['basket']) {
        $_SESSION['basket'] = $basketResult;
    } else {
        createBasket($conn);
    }
}

// this function creates a new basket after checkout

function createBasket($conn){
    $newBasketEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/baskets/?new";

    $data = array(
        'user' => $_SESSION['activeUser']
    );

    $options = array(
        'http' => array(
            'header'=> "Content-type: application/x-www-form-urlencoded",
            'method' => 'POST',
            'content' => json_encode($data)
        )
    );

    $context = stream_context_create($options);

    $result = file_get_contents($newBasketEndpoint, false, $context);
    $result = json_decode($result, true);
    if (!$result['newBasket']){
        echo "basket creation problem";
    } 
}
// this function takes a string and cleans it of harmful characters
function cleanText(&$conn, $text){
    $text = htmlentities($text);
    $text = $conn->real_escape_string($text);
    return $text;
}

// this takes the $conn object and a genre as a string and returns the ID from the g_genre table
function getGenreID(&$conn, $genre){
    $genreQuery = "SELECT GenreID FROM g_genre WHERE GenreName = '$genre'";
    $result = $conn->query($genreQuery);

    if (!$result){
        echo $conn->error;
    }
    
    $genreID = $result->fetch_assoc();
    $genreID = $genreID['GenreID'];
    return $genreID;
}

function getGenre(&$conn, $genreID){
    $genreQuery = "SELECT GenreName FROM g_genre WHERE GenreID = '$genreID'";
    $result = $conn->query($genreQuery);

    if (!$result){
        echo $conn->error;
    }
    
    $genreName = $result->fetch_assoc();
    $genreName = $genreName['GenreName'];
    return $genreName;
}

function getTrueRating($reviewDetails){
    
    $count = 0;
    $total = 0;

    foreach ($reviewDetails as $review){
        $total += $review['rating'];
        $count++;
    }

    
    return !($count == 0) ? round(($total / $count), 2) : 0;
}



if (isset($_GET['search-submit'])){
    $submittedSearch = $_GET['search'];
    $searchTerm = urlencode(htmlentities($submittedSearch));
    addSearch($conn, $searchTerm);
    
    $searchEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?format=json&name=";
    $appsJSON = file_get_contents($searchEndpoint.$searchTerm);
    $amountOfResults = count(json_decode($appsJSON, true));
    
}


// this function inserts the search results into the search tables
// for analysis at a later date. It also records the user id (if present)
function addSearch(&$conn, $search){
    $searchInsert = $conn->prepare("INSERT INTO g_searches (UserID, SearchTerm, Date, Time) 
                                    VALUES (?,?, CURDATE(), CURTIME())");
    $user = (isset($_SESSION['loggedIn'])) ? $_SESSION['activeUser'] : NULL ;
    $searchInsert->bind_param("is", $user, $search);
    $searchInsert->execute();
    if (!$searchInsert){
        echo $conn->error;
    }
    $searchInsert->close();
}

// download a file as an "app"

/*
    This ended up not working for multiple files unfortunately
*/

function createAppFile($appID){

    $appEndpoint = "http://sharte14.lampt.eeecs.qub.ac.uk/GUGL_API/apps/?id=".intval($appID);

    $appRequest = file_get_contents($appEndpoint);
    $appInfo = json_decode($appRequest, true)[0];

    $file = $appInfo['name'].".txt";
    $txt = fopen($file, "w") or die("unable to open file");
    $name = htmlentities($appInfo['name']);
    fwrite($txt, "Name:\t{$name}\nPublisher:\t{$appInfo['publisher']}\nGenre:\t{$appInfo['genre']}\nDescription:\t{$appInfo['description']}\nPrice:\t{$appInfo['price']}\n");
    fclose($txt);

    return $txt;

}

// global vars

$DEFAULT_APP_NAME = "AppName";
$DEFAULT_PUBLISHER = "Publisher";
$DEFAULT_PRICE = 0.79;
$DEFAULT_DESCRIPTION = "Description";


?>