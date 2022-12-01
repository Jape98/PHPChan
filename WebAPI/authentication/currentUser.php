<?php
#region depencies
require_once('../persistence/db.php');
require_once('../model/ResponseModel.php');
require_once('../controller/thread.php');
#endregion

function isLoggedIn() {
    if(!isset($_SERVER['HTTP_AUTHORIZATION']) || (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1))
    {
        $response = new ResponseModel();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("No authorization token provided");
        $response->send();
        exit();
    }
    $refreshToken = $_SERVER['HTTP_AUTHORIZATION'];
    try {
        $query = $GLOBALS['DBConnection']-> prepare('SELECT us.refreshTokenExpiry, u.loginAttempts, us.userId, us.refreshToken FROM userSession us INNER JOIN user u WHERE us.userId = u.id AND us.refreshToken = :refreshToken');
        $query->bindParam(":refreshToken", $refreshToken, PDO::PARAM_STR);
        $query->execute();
        $rowCount = $query->rowCount();
        if($rowCount === 0)
        {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("You need to log in to view this page");
            $response->send();
            exit();
        }
        
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $GLOBALS['fromDB_userId'] = $row['userId'];
        $GLOBALS['fromDB_refreshToken'] = $row['refreshToken'];
        $fromDB_loginAttempts = $row['loginAttempts'];
        $fromDB_refreshTokenExpiry = $row['refreshTokenExpiry'];
        
        // check if user is locked out
        if($fromDB_loginAttempts >= 3) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account locked");
            $response->send();
            exit();
        }
        // check if refresh token has expired
        if(strtotime($fromDB_refreshTokenExpiry) < time()) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Session expired. Please log in again");
            $response->send();
            exit();
        }

        return true;

    } catch(PDOException $e) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Invalid authentication token. Log in again.");
        $response->send();
        exit();
    }	
}


function userInfo() {
    //$DBConnection = DB::connectDB();
    if(!isset($_SERVER['HTTP_AUTHORIZATION']) || (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1))
    {
        $response = new ResponseModel();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("No authorization token provided");
        $response->send();
        exit();
    }
    $refreshToken = $_SERVER['HTTP_AUTHORIZATION'];
    try {
        $query = $GLOBALS['DBConnection']-> prepare('SELECT us.userId, us.refreshToken, us.refreshTokenExpiry, u.loginAttempts FROM userSession us INNER JOIN user u WHERE us.userId = u.id AND us.refreshToken = :refreshToken');
        $query->bindParam(":refreshToken", $refreshToken, PDO::PARAM_STR);
        $query->execute();
        $rowCount = $query->rowCount();
        if($rowCount === 0)
        {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("You need to log in to view this page");
            $response->send();
            exit();
        }
        
        $row = $query->fetch(PDO::FETCH_ASSOC);
        $fromDB_userId = $row['userId'];
        $fromDB_refreshToken = $row['refreshToken'];
        $fromDB_loginAttempts = $row['loginAttempts'];
        $fromDB_refreshTokenExpiry = $row['refreshTokenExpiry'];
        
        // check if user is locked out
        if($fromDB_loginAttempts >= 3) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("User account locked");
            $response->send();
            exit();
        }
        // check if refresh token has expired
        if(strtotime($fromDB_refreshTokenExpiry) < time()) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(401);
            $response->setSuccess(false);
            $response->addMessage("Session expired. Please log in again");
            $response->send();
            exit();
        }
        return true;
    } catch(PDOException $e) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Invalid authentication token. Log in again.");
        $response->send();
        exit();
    }	
}