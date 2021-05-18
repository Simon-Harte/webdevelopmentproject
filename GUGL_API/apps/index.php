<?php

/*
Apps endpoint

TODO:
Replace admin key checks with new function
 */

 // include relevant files
include "../connection.php";
include "../utilities.php";

// ensure returns JSON format
header('Content-Type: application/json');

// this ensures correct decimal precision from floats
// https://stackoverflow.com/questions/42981409/php7-1-json-encode-float-issue
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set('serialize_precision', -1);
}

// find the method of the http request
$method = $_SERVER['REQUEST_METHOD'];

// switch upon it
switch ($method) {
    
    // handle GET requests
    case 'GET':


        //prepare the array
        $response = array();
        // prepare the SQL
        $sql = "SELECT * FROM g_app ";
        // if the name search aspect is set
        if (isset($_GET['name'])) {
            $name = urldecode(htmlentities($_GET['name']));
            // append the name query
            $sql = $sql . "WHERE Name LIKE '%" . $name . "%' OR Description LIKE '%" . $name . "%'";
        }
        if (isset($_GET['id'])) {
            $appID = $_GET['id'];
            $sql = $sql . " WHERE AppID = {$appID}";
        }
        if (isset($_GET['genreid'])) {
            $genreID = $_GET['genreid'];
            $sql = $sql . " WHERE GenreID = {$genreID}";
        }
        if (isset($_GET['genre'])) {
            $genre = htmlentities($_GET['genre']);
            $genrequery = "SELECT GenreID FROM g_genre WHERE GenreName = '{$genre}'";
            $result = $conn->query($genrequery);
            $result = $result->fetch_assoc();
            $sql = $sql . " WHERE GenreID = {$result['GenreID']}";
        }
        // get the results
        $appResult = $conn->prepare($sql);
        $appResult->execute();
        $appResult = $appResult->get_result();
        // check the result
        if (!$appResult) {
            echo null;
        } else {
            while ($row = $appResult->fetch_assoc()) {
                $AppID = $row['AppID'];
                // get reviews for each app
                $reviewQuery = "SELECT * FROM g_reviews WHERE AppID = {$AppID}";
                $reviewQuery = $conn->query($reviewQuery);
                // if there arent reviews its null
                $reviews = array();
                $ratingTotal = 0;
                $reviewCount = 0;
                if (!$reviewQuery) {
                    $reviews = null;
                } else {
                    // otherwise populate the review array
                    $reviews = array();
                    while ($reviewRow = $reviewQuery->fetch_assoc()) {
                        $review = array('content' => $reviewRow['Content'],
                            'userid' => $reviewRow['UserID'],
                            'rating' => (int) $reviewRow['Rating']);
                        $ratingTotal += intval($reviewRow['Rating']);
                        $reviewCount++;
                        array_push($reviews, $review);
                    }
                }
                $name = htmlentities($row['Name']);
                $publisher = htmlentities($row['Publisher']);
                $genre = getGenre($conn, $row['GenreID']);
                $rating = (!($reviewCount == 0)) ? number_format(round(($ratingTotal / $reviewCount), 2), 2, '.', ',') : 0;
                $array = array('id' => (int) $row['AppID'],
                    'name' => $name,
                    'genreID' => (int) $row['GenreID'],
                    'genre' => $genre,
                    'description' => $row['Description'],
                    'publisher' => $publisher,
                    'ageRestrict' => (int) $row['AgeRestrict'],
                    'price' => round(floatval($row['Price']), 2),
                    'image' => $row['ImageURL'],
                    'rating' => round(floatval($rating), 2),
                    'reviews' => $reviews);

                array_push($response, $array);
            }

            echo json_encode($response);
        }

        if (isset($_GET['reviews'])) {

            //get the reviews for each app
            $reviewQuery = "SELECT * FROM g_reviews";
            if (isset($_GET['app_id'])) {
                $appID = $_GET['app_id'];

                $reviewQuery = $reviewQuery . " WHERE AppID = {$appID}";
            }

            $reviewQuery = $conn->query($reviewQuery);
            // if there arent reviews its null
            if (!$reviewQuery) {
                echo null;
            } else {
                // otherwise populate the review array
                $reviews = array();
                while ($review = $reviewQuery->fetch_assoc()) {
                    $review = array('content' => $review['Content'],
                        'userid' => $review['UserID'],
                        'rating' => $review['Rating'],
                        'appid' => $review['AppID']);

                    array_push($reviews, $review);
                }
                echo json_encode($reviews);
            }

        }
        break;
    

    // handle put requests
    case 'PUT':

        $input = json_decode(file_get_contents('php://input'), true);
        $validKey = false;


        if (!isset($input['key'])) {
            echo json_encode(array('message'=>'Key Required'));
        } else {
            $key = $input['key'];

            $keyCheck = "SELECT * FROM g_admin WHERE APIKey = ?";

            $keyCheck = $conn->prepare($keyCheck);
            if (!$keyCheck) {
                echo "preparation error " . $conn->error;
            }
            $keyCheck->bind_param("s", $key);
            if (!$keyCheck) {
                echo "binding error " . $conn->error;
            }
            $keyCheck->execute();

            if (!$keyCheck) {
                echo "execution error " . $conn->error;
            }

            $result = $keyCheck->get_result();
            
            if ($result->num_rows > 0) {
                $newName = htmlentities($input['title']);
                $newPub = htmlentities($input['publisher']);
                $newDesc = htmlentities($input['description']);
                $newPrice = htmlentities($input['price']);
                $appID = openssl_decrypt(htmlentities($input['id']), 'AES-128-ECB', 'appsid');
                //$appID = htmlentities($input['id']);

                $updateApp = "UPDATE g_app SET  Name = ?,
                                                Publisher = ?,
                                                Description = ?,
                                                Price = ?
                                                WHERE AppID = ?";

                $update = $conn->prepare($updateApp);

                $update->bind_param("sssdi", $newName, $newPub, $newDesc, $newPrice, $appID);

                $update->execute();

                if ($update) {
                    $response = array(
                        "update" => true,
                        "id" => $appID,
                    );
                } else {
                    $response = array(
                        "update" => false,
                    );
                }

                echo json_encode($response);
            } else {
                $response = array(
                    "update" => false,
                );
                echo json_encode($response);
            }

        }

        break;

    // handle POST requests
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['key'])){
            echo json_encode(array('message'=>'Key Required'));
        } else {
            if(!adminCheck($conn, $input['key'])){
                echo json_encode(array('message'=>'Invalid Key'));
            } else {
                
                $newAppQuery = "INSERT INTO g_app (Name, Description, GenreID, Publisher, AgeRestrict, ImageURL, Price)
                VALUES          ( ?,       ?,           ?,        ?,         ?,          ?,       ?)";
                $newAppQuery = $conn->prepare($newAppQuery);
                
                // clean the variables coming in
            
                $newTitle = htmlentities($input['title']);
                $newDesc = htmlentities($input['description']);
                $newGenre = htmlentities($input['genre']);
                $newPub = htmlentities($input['publisher']);
                $ageRestrict = htmlentities($input['age-restrict']);
                $newURL = htmlentities($input['image']);
                $newPrice = htmlentities($input['price']);
                
                // bind the variables into the SQL statement
            
                $newAppQuery->bind_param("ssisisd", $newTitle, $newDesc, $newGenre, $newPub, $ageRestrict, $newURL, $newPrice);
                if (!$newAppQuery){
                    echo "bind error ".$conn->error;
                } 
                $newAppQuery->execute();
                if (!$newAppQuery){
                    echo "execution error ".$conn->error;
                } 
                $newAppID = $newAppQuery->insert_id;
                if (!$newAppQuery){
                    $response = array(
                        'create'=>FALSE,
                        'message'=>'App creation failed'
                    );
                } else {
                    $response = array(
                        'create'=>TRUE,
                        'message'=>'App created successfully',
                        'id'=>$newAppID
                    );
                }
                echo json_encode($response);
                $newAppQuery->close();
                $conn->close();
                
            }
        }
        
        
        break;
    // handle DELETE requests
    case 'DELETE':
       
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        $validKey = false;
        if (!isset($input['key'])) {
            echo json_encode(array('message'=>'Key Required'));
        } else {
            if (!adminCheck($conn, $input['key'])) {
                echo json_encode(array('message'=>'Invalid Key'));
            } else {
                
                $appID = openssl_decrypt($input['id'], 'AES-128-ECB', 'appsid');

                $deleteReviews = "DELETE FROM g_reviews WHERE AppID = ?";
                $deleteReviews = $conn->prepare($deleteReviews);
                if (!$deleteReviews) {
                    echo "preparation error " . $conn->error;
                } 
                $deleteReviews->bind_param("i", $appID);
                if (!$deleteReviews) {
                    echo "bind error " . $conn->error;
                } 
                
                $deleteReviews->execute();
                if (!$deleteReviews) {
                    echo "execution error " . $conn->error;
                } 
                $deleteReviews->close();
                $deleteApp = "DELETE FROM g_app WHERE AppID = ?";
                

                
                $deleteApp = $conn->prepare($deleteApp);
                if (!$deleteApp) {
                    echo "preparation error " . $conn->error;
                } 

                $deleteApp->bind_param("i", $appID);
                if (!$deleteApp) {
                    echo "binding error " . $conn->error;
                } 

                $deleteApp->execute();
                if (!$deleteApp) {
                    echo json_encode(array('delete'=>FALSE, 'message'=>'Delete failed'));
                } else {
                    echo json_encode(array('delete'=>TRUE, 'message'=>'Delete Successful'));
                }
                
                
            }
        }
        break;
    default:
        echo null;
        break;
}
