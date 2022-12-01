<?php
require_once('../persistence/db.php');
require_once('../model/ResponseModel.php');

$DBConnection = DB::connectDB();
    
#region Validate JSON input and input fields
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = new ResponseModel();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit();
}

if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    $response = new ResponseModel();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Content type header not set to JSON");
    $response->send();
    exit();
}

$rawPostData = file_get_contents('php://input');

if(!$jsonData = json_decode($rawPostData)) {
    $response = new ResponseModel();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Request body is not valid JSON");
    $response->send();
    exit();
}

if(!isset($jsonData->username) || !isset($jsonData->email) || !isset($jsonData->password)){
    $response = new ResponseModel();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (!isset($jsonData->username) ? $response->addMessage("Please provide username") : false);
    (!isset($jsonData->email) ? $response->addMessage("Please provide email") : false);
    (!isset($jsonData->password) ? $response->addMessage("Please provide password") : false);
    $response->send();
    exit();
}

if(strlen($jsonData->username) < 1 || strlen($jsonData->username) > 255
|| strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255
|| strlen($jsonData->email) < 1 || strlen($jsonData->email) > 255)
{
    $response = new ResponseModel();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (strlen($jsonData->username) < 1 ? $response->addMessage("Username cannot be blank") : false);
    (strlen($jsonData->username) > 255 ? $response->addMessage("Username cannot be longer than 255 characters") : false);
    (strlen($jsonData->password) < 1 ? $response->addMessage("Password cannot be blank") : false);
    (strlen($jsonData->password) > 255 ? $response->addMessage("Password cannot be longer than 255 characters") : false);
    (strlen($jsonData->email) < 1 ? $response->addMessage("Email cannot be blank") : false);
    (strlen($jsonData->email) > 255 ? $response->addMessage("Email cannot be longer than 255 characters") : false);
    $response->send();
    exit();
}
#endregion

//no trim for password because blank is valid char
$username = trim($jsonData->username);
$email = trim($jsonData->email);
$password = $jsonData->password;

try {
    #region Check if user/email already in database

    $query = $DBConnection->prepare('SELECT id FROM user WHERE username = :username');
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();
    if($rowCount !== 0){
        $response = new ResponseModel();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("Username already exists");
        $response->send();
        exit();
    }

    $query = $DBConnection->prepare('SELECT id FROM user WHERE email = :email');
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();
    if($rowCount !== 0){
        $response = new ResponseModel();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("Email already exists");
        $response->send();
        exit();
    }
    #endregion

    #region Insert new user to db
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = $DBConnection->prepare('INSERT INTO user (username, email, password, loginAttempts) VALUES (:username, :email, :pw, 0)');
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':pw', $hashed_password, PDO::PARAM_STR);
    $query->execute();
    #region
    $rowCount = $query->rowCount();
    if($rowCount == 0) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Issue creating user account. Please try again.");
        $response->send();
        exit();
    }
    #endregion

    #region Send created user info back to the client
    $lastUserId = $DBConnection->lastInsertId();
    $returnData = array();
    $returnData['user_id'] = $lastUserId;
    $returnData['username'] = $username;
    $returnData['email'] = $email;

    $response = new ResponseModel();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->addMessage("User created successfully");
    $response->setData($returnData);
    $response->send();
    exit();
    #endregion

} catch (PDOException $e) {
    error_log("Database query error: $e, 0");
    $response = new ResponseModel();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Issue creating user account. Please try again.");
    $response->send();
    exit();
}