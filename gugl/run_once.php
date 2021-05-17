<?php

include("connection.php");

$appsEndpoint = "data/SmallStore.csv";

$appContents = fopen($appsEndpoint, "r");

// generates random string for app description
function getDescript($n = 500) {
    $characters = '0123 456 789abcdef ghijklmn opqrstuvwxy zABCDE FGHIJKLM NOPQR STUVWXYZ ';
    $randomString = '';
  
    for ($i = 0; $i < $n; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
  
    return $randomString;
}

// generate age restrict
function ageRestrict(){
    $num = rand(0,9);
    if ($num <= 3){
        return 0;
    } else {
        return 1;
    }
}

function generatePrice(){
    $num = rand(0, 9);
    if ($num <= 2){
        return 0.99;
    } elseif ($num >2 && $num <= 5) {
        return 0.79;
    } elseif ($num > 5){
        return 0;
    }
}

function generateImageURL(){

    do{
        // randomise an integer
        $random = rand(0, 3000);
        // the template URL to use - 200px square
        $endpoint = "https://picsum.photos/id/{$random}/200.jpg";

        // retrieve from the endpoint
        $contents = @file_get_contents($endpoint);

        // if the URL doesnt exist skip and do it again
        if (!$contents){
            continue;
        }

    } while (!$contents);
      
    return $endpoint;
}

while ( ($row = fgetcsv($appContents)) !== FALSE){

    

    if ($row[0] == "id"){
        continue;
    }

    $description = getDescript();
    $genre = rand(1,6);
    $rating = round($row[3]);
    $age = ageRestrict();
    $price = generatePrice();
    $imageURL = generateImageURL();
   //id,app_name,genre,rating,reviews,cost_label,rate_5_pc,rate_4_pc,rate_3_pc,rate_2_pc,rate_1_pc,updated,size,installs,current_version,requires_android,content_rating,in_app_products,offered_by
    //[0]  [1]      [2]  [3]    [4]     [5]         [6]         [7]     [8]         [9]     [10]    [11]    [12]    [13]        [14]        [15]            [16]            [17]            [18]     
    $appQuery = " INSERT INTO g_app (Name,  Description,  GenreID,     Rating,  Publisher,   AgeRestrict, Price, ImageURL) 
                            VALUES  ('{$row[1]}', '{$description}',  {$genre} ,    {$rating}     , '{$row[18]}',{$age},{$price},'{$imageURL}')";  

    //echo $appQuery;
    
    $appInsertResult = $conn->query($appQuery);

    if (!$appInsertResult){
        echo $conn->error;
    }

}



$reviewsEndpoint = "data/SmallReviews.csv";

$reviewsContents = fopen($reviewsEndpoint, "r");

while ( ($row = fgetcsv($reviewsContents)) !== FALSE){
    if ($row[0] == "app_id"){
        continue;
    }
    $reviewContent = $conn->real_escape_string($row[1]);
    $reviewQuery = "INSERT INTO g_reviews (AppID, Content, Rating)
                        VALUES    ($row[0], '{$reviewContent}', $row[2])";
    $reviewInsertResult = $conn->query($reviewQuery);

    if (!$reviewInsertResult){
        echo $conn->error;
    }
}
echo "done";
?>