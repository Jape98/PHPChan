<?php

require_once('../../persistence/db.php');
require_once('../../model/ResponseModel.php');

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

try {
    $user = "CREATE TABLE user (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL COLLATE utf8_bin,
        loginAttempts BIGINT UNSIGNED NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $writeDB -> exec($user);

    $thread = "CREATE TABLE thread (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        userId BIGINT UNSIGNED NOT NULL,
        content VARCHAR(5000) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fkThreadUserId FOREIGN KEY (userId) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
      )";
    $writeDB -> exec($thread);

    $post = "CREATE TABLE post(
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        threadId BIGINT UNSIGNED NOT NULL, 
        userId BIGINT UNSIGNED NOT NULL,
        content VARCHAR(5000) NOT NULL,
        createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        CONSTRAINT fkPostUserId FOREIGN KEY (userId) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fkThread FOREIGN KEY (threadId) REFERENCES thread(id) ON DELETE CASCADE ON UPDATE CASCADE
    )";
    $writeDB -> exec($post);
    
    $userSession = "CREATE TABLE userSession(
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        userId BIGINT UNSIGNED NOT NULL, 
        refreshToken VARCHAR(100) COLLATE utf8_bin,
        refreshTokenExpiry DATETIME,
        CONSTRAINT fkSessionUserId FOREIGN KEY (userId) REFERENCES user(id) ON DELETE RESTRICT ON UPDATE RESTRICT
    )";
    $writeDB -> exec($userSession);
    
    echo "Tables created successfully";

} catch(PDOException $e) {

    error_log("Database error - ".$e, 0);
    $response = new ResponseModel();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database write error");
    $response->send();
    exit();
}

$conn = null;


