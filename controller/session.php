<?php

require_once('../persistence/db.php');
require_once('../model/ResponseModel.php');

#region Try db connect
try {
    $writeDB = DB::connectWriteDB();
} catch (PDOException $e) {
    //0 = php error logfile
    error_log("Connection error - ".$e, 0);
    $response = new ResponseModel();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database connection error");
    $response->send();
    exit();
}
#endregion

if(array_key_exists("sessionId", $_GET)){
    #region get current accessToken
    $sessionId = $_GET['sessionId'];

    if($sessionId == '' || !is_numeric($sessionId)) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        ($sessionId == '' ? $response->addMessage("Session ID cannot be blank") : false);
        (!is_numeric($sessionId) ? $response->addMessage("Session ID must be numeric") : false);
        $response->send();
        exit();
    }

    if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1)
    {
        $response = new ResponseModel();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
        (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
        $response->send();
        exit();
    }

    $accessToken = $_SERVER['HTTP_AUTHORIZATION'];
    #endregion

    if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    #region handle delete as logout
        try {
            $query = $writeDB->prepare('DELETE FROM userSession WHERE id = :sessionId AND accessToken = :accessToken');
            $query->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
            $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
            $query->execute();
    
            // get row count
            $rowCount = $query->rowCount();
    
            if($rowCount === 0) {
            // set up response for unsuccessful log out response
            $response = new ResponseModel();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Failed to log out of this session using access token provided");
            $response->send();
            exit();
            }
            
            // build response data array which contains the session id that has been deleted (logged out)
            $returnData = array();
            $returnData['sessionId'] = intval($sessionId);
    
            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit();
        }
        catch(PDOException $e) {
            error_log("Database query error: $e, 0");
            $response = new ResponseModel();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Issue logging out. Please try again");
            $response->send();
            exit();
        }
    #endregion

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {

        #region validate JSON and input data
        if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
            $response = new ResponseModel();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content type header not set to JSON");
            $response->send();
            exit();
        }

        // get PATCH request body
        $inputData = file_get_contents('php://input');
        if(!$inputJson = json_decode($inputData)){
            $response = new ResponseModel();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Not valid JSON");
            $response->send();
            exit();
        }
        if(!isset($inputJson->refreshToken) || strlen($inputJson->refreshToken) < 1)  {
            $response = new ResponseModel();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            (!isset($inputJson->refreshToken) ? $response->addMessage("Refresh Token not supplied") : false);
            (strlen($inputJson->refreshToken) < 1 ? $response->addMessage("Refresh Token cannot be blank") : false);
            $response->send();
            exit();
        }
        #endregion

        try {
            $refreshToken = $inputJson->refreshToken;
            #region get needed data from db
            $query = $writeDB->prepare('SELECT userSession.id as sessionId, user.id as userId, accessToken, refreshToken, loginAttempts, accessTokenExpiry, refreshTokenExpiry FROM userSession, user WHERE user.id = userSession.userId AND userSession.id = :sessionId AND userSession.accessToken = :accessToken AND userSession.refreshToken = :refreshToken');
            $query->bindParam(':sessionId', $sessionId, PDO::PARAM_INT);
            $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
            $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();

            if($rowCount === 0) {
              $response = new ResponseModel();
              $response->setHttpStatusCode(401);
              $response->setSuccess(false);
              $response->addMessage("Incorrect token. Please log in again.");
              $response->send();
              exit();
            }

            $row = $query->fetch(PDO::FETCH_ASSOC);
  
            $fromDb_sessionId = $row['sessionId'];
            $fromDb_userId = $row['userId'];
            $fromDb_accessToken = $row['accessToken'];
            $fromDb_refreshToken = $row['refreshToken'];
            $fromDb_loginAttempts = $row['loginAttempts'];
            $fromDb_accessTokenExpiry = $row['accessTokenExpiry'];
            $fromDb_refreshTokenExpiry = $row['refreshTokenExpiry'];
            #endregion

            #region check if token is valid and if user locked out
            if($fromDb_loginAttempts >= 3) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("User account locked");
                $response->send();
                exit();
            }

            // check if refresh token has expired
            if(strtotime($fromDb_refreshTokenExpiry) < time()) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("Session expired. Please log in again");
                $response->send();
                exit();
            }
            #endregion

            #region create new access token, save to db and send response
            //generate random binary, convert to hex, and then into base64 to get valid caracters to use in HTTP header
            $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
            $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
            $accessTokenExpiry = 900; //15 minutes
            $refreshTokenExpiry = 86400; //24 hours

            $query = $writeDB->prepare('UPDATE userSession SET accessToken = :accessToken, accessTokenExpiry = date_add(NOW(), INTERVAL :accessTokenExpiry SECOND), refreshToken = :refreshToken, refreshTokenExpiry = date_add(NOW(), INTERVAL :refreshTokenExpiry SECOND) WHERE id = :sessionId AND userId = :userId AND accessToken = :oldAccessToken AND refreshtoken = :oldRefreshToken');

            $query->bindParam(':userId', $fromDb_userId, PDO::PARAM_INT);
            $query->bindParam(':sessionId', $fromDb_sessionId, PDO::PARAM_INT);
            $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
            $query->bindParam(':accessTokenExpiry', $accessTokenExpiry, PDO::PARAM_INT);
            $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
            $query->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, PDO::PARAM_INT);
            $query->bindParam(':oldAccessToken', $fromDb_accessToken, PDO::PARAM_STR);
            $query->bindParam(':oldRefreshToken', $fromDb_refreshToken, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
      
            if($rowCount === 0) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("Issue refreshing token. Log in again");
                $response->send();
                exit();
            }

            $returnData = array();
            $returnData['sessionId'] = $fromDb_sessionId;
            $returnData['accessToken'] = $accessToken;
            $returnData['accessTokenExpiry'] = $accessTokenExpiry;
            $returnData['refreshToken'] = $refreshToken;
            $returnData['refreshTokenExpiry'] = $refreshTokenExpiry;
      
            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->setData($returnData);
            $response->send();
            exit();
            #endregion

        } catch (PDOException $e) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Issue refreshing token. Log in again");
            $response->send();
            exit();
        }
        
    //if GET contains ID but is not PATCH or DELETE
    } else {
        $response = new ResponseModel();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit();
      } 

// if GET empty = login
} elseif (empty($_GET)) {
    #region validate JSON
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = new ResponseModel();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit();
    }
    if($_SERVER['CONTENT_TYPE'] !== 'application/json'){
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content type header not set to JSON");
        $response->send();
        exit();
    }

     $inputData = file_get_contents('php://input');
    if(!$inputJson = json_decode($inputData)){
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Not valid JSON");
        $response->send();
        exit();
    }
    #endregion

    #region validate input data
    if(!isset($inputJson->email) || !isset($inputJson->password)) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (!isset($inputJson->email) ? $response->addMessage("Email cant be empty") : false);
        (!isset($inputJson->password) ? $response->addMessage("Password cant be empty") : false);
        $response->send();
        exit();
    }
    if (strlen($inputJson -> email) < 1 || strlen($inputJson -> email) > 255
    || strlen($inputJson -> password) < 1 || strlen($inputJson -> password) > 255) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        (strlen($inputJson -> email) < 1 ? $response->addMessage("Email cant be blank") : false);
        (strlen($inputJson -> password) < 1 ? $response->addMessage("Password cant be empty") : false);
        (strlen($inputJson -> email) > 255 ? $response->addMessage("Email cant be longer than 255 characters") : false);
        (strlen($inputJson -> password) > 255 ? $response->addMessage("Password cant be longer than 255 characters") : false);
        $response->send();
        exit();
    }
    #endregion

     #region attempt login
    try {
        $email = $inputJson->email;
        $password = $inputJson->password;

        $query = $writeDB->prepare('SELECT id, email, username, loginAttempts, password FROM user WHERE email = :email');
        $query -> bindParam(':email', $email, PDO::PARAM_STR);
        $query -> execute();
        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Check your unsername and password");
            $response->send();
            exit();
        }

        $row = $query->fetch(PDO::FETCH_ASSOC);
        $fromDb_id = $row['id'];
        $fromDb_email = $row['email'];
        $fromDb_username = $row['username'];
        $fromDb_password = $row['password'];
        $fromDb_loginAttempts = $row['loginAttempts'];

        if($fromDb_loginAttempts >= 5) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account is locked");
            $response->send();
            exit();
        }

        if(!password_verify($password, $fromDb_password)){
            //add 1 to loginAttempts on fail
            $query = $writeDB->prepare('UPDATE user SET loginAttempts = loginAttempts + 1 where id = :id');
            $query->bindParam(':id', $fromDb_id, PDO::PARAM_INT);
            $query->execute();

            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Check your unsername and password");
            $response->send();
            exit();
        }

     } catch (PDOException $e) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(504);
        $response->setSuccess(false);
        $response->addMessage("Error logging in. Please try again.");
        $response->send();
        exit();
    }
    #endregion

     #region on successful login
    try {
        //if login is successful, create tokens
        //generate random binary, convert to hex, and then into base64 to get valid caracters to use in HTTP header
        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $accessTokenExpiry = 900; //15 minutes
        $refreshTokenExpiry = 86400; //24 hours

         //Reset login attempts back to 0
        $writeDB->beginTransaction();
        $query = $writeDB->prepare('UPDATE user SET loginAttempts = 0 WHERE id = :id');
        $query->bindParam(':id', $fromDb_id, PDO::PARAM_INT);

         //for dates use sql date_add(NOW()) and then add seconds from variable
        $query = $writeDB->prepare('INSERT INTO userSession (userId, accessToken, accessTokenExpiry, refreshToken, refreshTokenExpiry) VALUES (:userId, :accessToken, date_add(NOW(), INTERVAL :accessTokenExpiry SECOND), :refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpiry SECOND))');
        $query->bindParam(':userId', $fromDb_id, PDO::PARAM_INT);
        $query->bindParam(':accessToken', $accessToken, PDO::PARAM_STR);
        $query->bindParam(':accessTokenExpiry', $accessTokenExpiry, PDO::PARAM_INT);
        $query->bindParam(':refreshToken', $refreshToken, PDO::PARAM_STR);
        $query->bindParam(':refreshTokenExpiry', $refreshTokenExpiry, PDO::PARAM_INT);
        $query->execute();

        $lastSessionId = $writeDB->lastInsertId();
        $writeDB->commit();

        $returnData = [];
        $returnData['sessionId'] = intval($lastSessionId);
        $returnData['accessToken'] = $accessToken;
        $returnData['accessTokenExpires'] = $accessTokenExpiry;
        $returnData['refreshToken'] = $refreshToken;
        $returnData['refreshTokenExpires'] = $refreshTokenExpiry;

        $response = new ResponseModel();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("Succesfully logged in");
        $response->setData($returnData);
        $response->send();
        exit(); 

    } catch (PDOException $e) {
        //rollback to before beginTransaction() if there's and error
        $writeDB -> rollBack();
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error logging in. Please try again.");
        $response->send();
        exit(); 
    }
    #endregion

} else {
    $response = new ResponseModel();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit();
}