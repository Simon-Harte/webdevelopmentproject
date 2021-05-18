<?php
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

function adminCheck(&$conn, $key){
    $keyCheckSQL = "SELECT * FROM g_admin WHERE APIKey = ?";

    $keyCheckSQL = $conn->prepare($keyCheckSQL);
    $keyCheckSQL->bind_param("s", $key);
    $keyCheckSQL->execute();
    $result = $keyCheckSQL->get_result();
    
    return ($result->num_rows >0) ? TRUE : FALSE;
}

function userKeyCheck(&$conn, $key, $userID){
    $keyCheckSQL = "SELECT * FROM g_auth_api WHERE APIKey = ? AND UserID = ?";

    $keyCheckSQL = $conn->prepare($keyCheckSQL);
    $keyCheckSQL->bind_param("si", $key, $userID);
    $keyCheckSQL->execute();
    $result = $keyCheckSQL->get_result();

    return ($result->num_rows >0) ? TRUE : FALSE;
}

function keyCheck(&$conn, $key){
    $keyCheckSQL = "SELECT * FROM g_auth_api WHERE APIKey = ?";

    $keyCheckSQL = $conn->prepare($keyCheckSQL);
    $keyCheckSQL->bind_param("s", $key);
    $keyCheckSQL->execute();
    $result = $keyCheckSQL->get_result();

    return ($result->num_rows >0) ? TRUE : FALSE;
}


?>