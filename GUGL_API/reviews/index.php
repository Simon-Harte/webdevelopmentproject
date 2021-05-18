<?php

/*
Reviews endpoint
*/

include("../connection.php");
include("../utilities.php");

// ensure returns JSON format
header('Content-Type: application/json');
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );
}


$method = $_SERVER['REQUEST_METHOD'];

switch ($method){
    case 'GET':
       
        //get the reviews for each app
        $reviewQuery = "SELECT * FROM g_reviews";

        // if the app id is set ...
        if (isset($_GET['app_id'])){
            $appID = $_GET['app_id'];
            // ... append the relevant SQL onto the main query
            $reviewQuery = $reviewQuery." WHERE AppID = {$appID}";
        }

        $reviewQuery = $conn->prepare($reviewQuery);
        $reviewQuery->execute();
        $reviewQuery = $reviewQuery->get_result();
        // if there arent reviews its null
        if (!$reviewQuery){
            echo null;
        } else {
            // otherwise populate the review array
            $reviews = array();
            while($review = $reviewQuery->fetch_assoc()){
                $review = array('id'=>$review['ReviewID'],
                                'content'=>$review['Content'], 
                                'userid'=>$review['UserID'],
                                'rating'=>$review['Rating'],
                                'appid'=>$review['AppID']);
            
                array_push($reviews, $review);
            }
            echo json_encode($reviews);
        }
    
        break;
    case 'PUT':
        echo "put method";
        break;
    case 'POST':
        // decode the input
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['key'])){
            $response = array(
                'review'=>FALSE,
                'message'=>'No key provided'
            );
        } else {
            $key = $input['key'];
            if (!keyCheck($conn, $key)) {
                $response = array(
                    'review'=>FALSE,
                    'message'=>'Key not recognised'
                );
            } else {
                
                
                $user = $input['user'];
                $app = $input['app'];
                // filter content to prevent XSS

                $content = (strlen($input['content']) > 0) ? htmlentities($input['content']) : "No content";
                
                $rating = $input['rating'];
                
                $newReviewSQL = "INSERT INTO g_reviews (UserID, AppID, Content, Rating) VALUES (?, ?, ?, ?)";
                $newReviewSQL = $conn->prepare($newReviewSQL);
                if (!$newReviewSQL){
                    echo "preparation error ".$conn->error;
                } 
                
                $newReviewSQL->bind_param("iisi", $user, $app, $content, $rating);
                if (!$newReviewSQL){
                    echo "binding error ".$conn->error;
                } 
                $newReviewSQL->execute();
                if (!$newReviewSQL){
                    echo "execution error ".$conn->error;
                } 
                
                $reviewID = $newReviewSQL->insert_id;
                
                $newReviewSQL->close();
                $response = array(
                    'review'=>TRUE,
                    'reviewid'=>$reviewID,
                    'message'=>'Review added successfully'
                );
            }
    
        }
        echo json_encode($response);
        break;
    case 'DELETE':
        echo "delete method";
        break;
    default:
        echo null;
        break;
}






?>