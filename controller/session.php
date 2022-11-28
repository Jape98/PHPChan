<?php

require_once('../persistence/db.php');
require_once('../model/ResponseModel.php');

#region Try db connect
try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();

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
        $return_id = $row['id'];
        $return_email = $row['email'];
        $return_username = $row['username'];
        $return_password = $row['password'];
        $return_loginAttempts = $row['loginAttempts'];

        if($return_loginAttempts >= 5) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account is locked");
            $response->send();
            exit();
        }

        if(!password_verify($password, $return_password)){
            //add 1 to loginAttempts on fail
            $query = $writeDB->prepare('UPDATE user SET loginAttempts = loginAttempts + 1 where id = :id');
            $query->bindParam(':id', $return_id, PDO::PARAM_INT);
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
        //generaterandom binary, convert to hex, and then into base64 to get valid caracters to use in HTTP header
        $accessToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $refreshToken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))).time();
        $accessTokenExpiry = 900; //15 minutes
        $refreshTokenExpiry = 86400; //24 hours

        //Reset login attempts back to 0
        $writeDB->beginTransaction();
        $query = $writeDB->prepare('UPDATE user SET loginAttempts = 0 WHERE id = :id');
        $query->bindParam(':id', $return_id, PDO::PARAM_INT);

        //for dates use sql date_add(NOW()) and then add seconds from variable
        $query = $writeDB->prepare('INSERT INTO userSession (userId, accessToken, accessTokenExpiry, refreshToken, refreshTokenExpiry) VALUES (:userId, :accessToken, date_add(NOW(), INTERVAL :accessTokenExpiry SECOND), :refreshToken, date_add(NOW(), INTERVAL :refreshTokenExpiry SECOND))');
        $query->bindParam(':userId', $return_id, PDO::PARAM_INT);
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