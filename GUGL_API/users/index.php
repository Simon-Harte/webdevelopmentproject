<?php

/*
Reviews endpoint
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
    case 'PUT':

        $input = json_decode(file_get_contents('php://input'), true);

        if ($input['process'] == 'update') {

            if (!isset($input['key'])) {
                $response = array(
                    'update'=>FALSE,
                    'message'=>'No API key supplied'
                );
            } else {
                $key = $input['key'];

                // must unencrypt the ID
                $rowID = openssl_decrypt($input['id'], 'AES-128-ECB', 'userid');
                //$rowID = $input['id'];
                // check key exists and matches the user ID

                $keyCheckSQL = "SELECT g_user.UserID, g_auth_api.APIKey
                                FROM g_user
                                INNER JOIN g_auth_api
                                ON g_user.UserID = g_auth_api.UserID
                                WHERE g_auth_api.APIKey = ?";

                $keyCheckSQL = $conn->prepare($keyCheckSQL);

                $keyCheckSQL->bind_param("s", $key);

                $keyCheckSQL->execute();

                $result = $keyCheckSQL->get_result()->fetch_assoc();

                if ($rowID != $result['UserID']) {
                    $response = array(
                        'update'=>FALSE,
                        'message' => 'UserID and API key do not match'
                    );

                } else {
                    $userID = $input['id'];
                    $firstName = $input['first-name'];

                    $lastName = $input['surname'];

                    $email = $input['email'];
                    

                    $password = $input['password'];

                    $updateUserQuery = $conn->prepare("UPDATE g_user
                                SET FirstName = ?,
                                Surname = ?,
                                Email = ?,
                                Password = ?
                                WHERE UserID = $rowID");


                    
                    $updateUserQuery->bind_param("ssss", $firstName, $lastName, $email, $password);
                    $updateUserQuery->execute();

                    if (!$updateUserQuery) {
                        $response = array(
                            'update'=>FALSE,
                            'message'=>'Update Unuccessful'
                        );
                    } else {
                        $response = array(
                            'update'=>TRUE,
                            'message'=>'Update Successful'
                        );
                    }
                    $updateUserQuery->close();
                    $conn->close();

                }
            }
            echo json_encode($response);
        }
        break;
    case 'POST':

        $input = json_decode(file_get_contents('php://input'), true);

        if ($input['process'] == 'login') {

            $enteredEmail = htmlentities($input['email']);
            $enteredPassword = htmlentities($input['password']);

            $userQuery = "SELECT * FROM g_user WHERE Email = '{$enteredEmail}'";

            $userQuery = $conn->prepare($userQuery);
            if (!$userQuery) {
                echo "preparation error " . $conn->error;
            }

            $userQuery->execute();
            if (!$userQuery) {
                echo "execution error " . $conn->error;
            }
            $userQuery = $userQuery->get_result();
            if (!$userQuery) {
                echo "get result error " . $conn->error;
            }
            // if there arent reviews its null
            if ($userQuery->num_rows == 0) {
                echo "no user";
            } else {

                // otherwise populate the user array

                $user = $userQuery->fetch_assoc();

                if ($enteredEmail == $user['Email'] && $enteredPassword == $user['Password']) {

                    // get API key

                    // write statement
                    $APIRetrieval = "SELECT APIKey FROM g_auth_api WHERE UserID = {$user['UserID']}";

                    $APIRetrieval = $conn->prepare($APIRetrieval);

                    $APIRetrieval->execute();
                    $result = $APIRetrieval->get_result()->fetch_assoc();
                    $key = $result['APIKey'];
                    $response = array(
                        'login' => true,
                        'userid' => $user['UserID'],
                        'message' => 'Login successful',
                        'key' => $key,
                    );
                    echo json_encode($response);
                } else {
                    $response = array(
                        'login' => false,
                        'message' => 'There was a problem logging you in',
                    );
                    echo json_encode($response);
                }
                //echo json_encode($user);

            }
            $userQuery->close();
        }

        if ($input['process'] == 'signup') {

            // check the user doesn't already exist
            $enteredEmail = htmlentities($input['email']);

            $userCheck = "SELECT * FROM g_user WHERE Email = '{$enteredEmail}'";

            $userCheck = $conn->prepare($userCheck);
            if (!$userCheck) {
                echo "preparation error " . $conn->error;
            }

            $userCheck->execute();
            if (!$userCheck) {
                echo "execution error " . $conn->error;
            }

            $userCheck = $userCheck->get_result();

            if (!$userCheck) {
                echo "get result error " . $conn->error;
            }

            if ($userCheck->num_rows > 0) {
                $response = array(
                    'signup' => false,
                    'message' => 'user exists',
                );

                echo json_encode($response);
            } else {

                // otherwise create user!

                $firstName = htmlentities($input['firstname']);
                $surname = htmlentities($input['surname']);
                $email = htmlentities($input['email']);
                $password = htmlentities($input['password']);
                $newUser = "INSERT INTO g_user (FirstName, Surname, Email, Password)
                                            VALUES(?, ?, ?, ?)";
                $newUser = $conn->prepare($newUser);
                if (!$newUser) {
                    echo "preparation error " . $conn->error;
                }
                $newUser->bind_param("ssss", $firstName, $surname, $email, $password);
                if (!$newUser) {
                    echo "binding error " . $conn->error;
                }
                $newUser->execute();

                if (!$newUser) {
                    echo "execution error " . $conn->error;
                }

                $userID = $newUser->insert_id;
                $newUser->close();
                // give the user an API key so they can add/modify certain aspects
                $newAPIKey = md5(rand());

                $newKeySQL = "INSERT INTO g_auth_api (UserID, APIKey) VALUES ( ?, ? )";

                $newKeySQL = $conn->prepare($newKeySQL);
                $newKeySQL->bind_param("is", $userID, $newAPIKey);
                $newKeySQL->execute();

                $response = array(
                    'signup' => true,
                    'message' => 'user created',
                );
                echo json_encode($response);

            }
            $userCheck->close();

        }
        break;

    default:
        echo "invalid";
        break;
}
