<?php

/*
Apps endpoint

TODO:

Replace admin key checks with new function
 */

include "../connection.php";
include "../utilities.php";

// ensure returns JSON format
header('Content-Type: application/json');

if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set('serialize_precision', -1);
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // handle GET requests
    case 'GET':

        // SELECT g_baskets.BasketID, g_baskets.AppID FROM g_baskets INNER JOIN g_userbasket ON g_baskets.BasketID = g_userbasket.BasketID WHERE g_userbasket.UserID = 6;
        $response = array();
        if (isset($_GET['key'])) {

            $key = $_GET['key'];
            $userID = $_GET['userid'];
            if (userKeyCheck($conn, $key, $userID)) {

                if (isset($_GET['active'])) {

                    // 0 for active, 1 for inactive

                    $active = ($_GET['active'] == 0) ? 0 : 1;

                    if ($active == 0) {

                        $basketSQL = "SELECT * FROM g_userbasket WHERE UserID = ? AND Inactive = ?";

                        $basketSQL = $conn->prepare($basketSQL);
                        if (!$basketSQL) {
                            echo "preparation error " . $conn->error;
                        }
                        $basketSQL->bind_param("ii", $userID, $active);
                        if (!$basketSQL) {
                            echo "binding error";
                        }
                        $basketSQL->execute();
                        if (!$basketSQL) {
                            echo "execution error";
                        }

                        $basketID = $basketSQL->get_result()->fetch_assoc()['BasketID'];
                        if ($basketID) {
                            $contents = array();
                            $basketContentsSQL = "SELECT * FROM g_baskets WHERE BasketID = $basketID";

                            $basketContents = $conn->prepare($basketContentsSQL);
                            $basketContents->execute();
                            $basketContents = $basketContents->get_result();
                            while ($row = $basketContents->fetch_assoc()) {
                                $item = array(
                                    'id' => $row['AppID'],
                                );
                                array_push($contents, $item);
                            }
                            $response = array(
                                'basket' => true,
                                'basketid' => $basketID,
                                'contents' => $contents,
                            );

                        } else {
                            $response = array(
                                'basket' => false,
                                'message' => 'No active basket found',
                            );
                        }
                    } else {
                        // get inactive (already purchased) baskets
                        $basketSQL = "SELECT * FROM g_userbasket WHERE UserID = ? AND Inactive = ?";

                        $basketSQL = $conn->prepare($basketSQL);
                        if (!$basketSQL) {
                            echo "preparation error " . $conn->error;
                        }
                        $basketSQL->bind_param("ii", $userID, $active);
                        if (!$basketSQL) {
                            echo "binding error";
                        }
                        $basketSQL->execute();
                        if (!$basketSQL) {
                            echo "execution error";
                        }

                        $baskets = $basketSQL->get_result();

                    

                        while ($basket = $baskets->fetch_assoc()){
                            
                            $priceSQL = "SELECT Amount, Date, Time FROM g_transactions WHERE BasketID = {$basket['BasketID']}";
                            $priceSQL = $conn->prepare($priceSQL);
                            $priceSQL->execute();
                            $price = $priceSQL->get_result();
                            $price = $price->fetch_assoc();
                            $cost = $price['Amount'];
                            $date = $price['Date'];
                            $time = $price['Time'];
                            $basketContentsSQL = "SELECT * FROM g_baskets WHERE BasketID = {$basket['BasketID']}";
                            
                            $basketContents = $conn->prepare($basketContentsSQL);
                            $basketContents->execute();
                            $basketContents = $basketContents->get_result();
                            $contents = array();
                            while ($row = $basketContents->fetch_assoc()) {
                                $item = array(
                                    'id' => $row['AppID'],
                                );
                                array_push($contents, $item);
                            }
                            $baskCont = array(
                                'basketid'=>$basket['BasketID'],
                                'price'=>$cost,
                                'contents'=>$contents,
                                'date'=>$date,
                                'time'=>$time
                            );
                            array_push($response, $baskCont);
                        }
                    }

                } else {
                    $response = array(
                        'basket' => false,
                        'message' => 'No active setting',
                    );
                }

            } else {
                $response = array(
                    'basket' => false,
                    'message' => 'Access forbidden',
                );
            }

        } else {
            $response = array(
                'basket' => false,
                'message' => 'No key supplied',
            );
        }
        echo json_encode($response);
        /*
        while ($row = $result->fetch_assoc()){

        $basketItem = array(
        'appid'=>$row['AppID']
        );
        array_push($response, $basketItem);
        }

        echo json_encode($response);
         */
        break;

    // handle put requests
    case 'PUT':

        break;
    // handle POST requests
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($_GET['item'])) {
            $key = $input['key'];
            if (!keyCheck($conn, $key)) {
                $response = array(
                    'add' => false,
                    'message' => 'invalid key',
                );
            } else {

                $basket = $input['basket'];
                $item = $input['item'];

                $addItemSQL = "INSERT INTO g_baskets (BasketID, AppID)
                                            VALUES    ($basket, $item)";
                $addItemSQL = $conn->prepare($addItemSQL);
                $addItemSQL->execute();
                $addItemSQL->close();
                $response = array(
                    'add' => true,
                    'message' => 'item successfully added to basket',
                    'item' => $item,
                );
            }
            echo json_encode($response);
        }

        if (isset($_GET['checkout'])) {
            // get user key
            $key = $input['key'];
            if (!userKeyCheck($conn, $key, $input['user'])) {
                $response = array(
                    'checkout' => false,
                    'message' => 'Invalid key',
                );
            } else {
                $userID = $input['user'];
                $basketID = $input['basket'];

                // grab price from the db
                $priceSQL = "SELECT SUM(g_app.Price) AS Total FROM g_app INNER JOIN g_baskets ON g_app.AppID = g_baskets.AppID WHERE g_baskets.BasketID = $basketID";

                $priceSQL = $conn->prepare($priceSQL);
                $priceSQL->execute();
                $priceSQL = $priceSQL->get_result()->fetch_assoc();
                $total = $priceSQL['Total'];

                // add the transaction record
                $transactionSQL = "INSERT INTO g_transactions (UserID, BasketID, Amount, Date, Time)
                                                        VALUES ($userID, $basketID, $total, CURDATE(), CURTIME())";
                $transaction = $conn->prepare($transactionSQL);
                $transaction->execute();
                $transactionID = $transaction->insert_id;
                $transaction->close();

                // close the basket record
                $closeBasketSQL = "UPDATE g_userbasket SET Inactive = 1 WHERE BasketID = $basketID";
                $closeBasket = $conn->prepare($closeBasketSQL);
                $closeBasket->execute();

                $response = array(
                    'checkout' => true,
                    'message' => 'Checkout successful',
                    'transactionid' => $transactionID,
                );

            }
            echo json_encode($response);
        }

        if (isset($_GET['new'])) {
            $userID = $input['user'];

            $newBasketSQL = "INSERT INTO g_userbasket (UserID, Inactive) VALUES ($userID, 0)";
            $newBasket = $conn->prepare($newBasketSQL);
            if (!$newBasket) {
                echo "preparation error " . $conn->error;
            }
            $newBasket->execute();
            if (!$newBasket) {
                echo "execution error " . $conn->error;
            }
            $response = array(
                'newBasket' => true,
                'message' => 'Basket created successfully',
            );

            echo json_encode($response);
        }
        break;
    case 'DELETE':

        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($_GET['item'])) {
            $key = $input['key'];
            if (!keyCheck($conn, $key)) {
                $response = array(
                    'remove' => false,
                    'message' => 'invalid key',
                );
            } else {

                $basket = $input['basket'];
                $item = $input['item'];
                $removeItemSQL = "DELETE FROM g_baskets WHERE BasketID = $basket AND AppID = $item";
                $removeItemSQL = $conn->prepare($removeItemSQL);
                if (!$removeItemSQL) {
                    echo "preparation error " . $conn->error;
                }
                $removeItemSQL->execute();
                if (!$removeItemSQL) {
                    echo "execution error " . $conn->error;
                }
                $response = array(
                    'remove' => true,
                    'message' => 'item removed',
                );
            }
            echo json_encode($response);
        }

        break;
    default:
        echo null;
        break;
}
