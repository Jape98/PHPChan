<?php

require_once('../../persistence/db.php');
require_once('../../model/ResponseModel.php');

try {
    $DBConnection = DB::connectDB();
    

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
    $user = "INSERT INTO user(username, email, password, loginAttempts) VALUES('TestUser', 'tester@test.com', 'test', 0)";
    $user2 = "INSERT INTO user(username, email, password, loginAttempts) VALUES('TestUser2', 'tester2@test.com', 'test', 0)";
    $thread = "INSERT INTO thread(userId, content) VALUES(1, 'Test thread.')";
    $thread2 = "INSERT INTO thread(userId, content) VALUES(2, 'Second test thread.')";
    $post = "INSERT INTO post(threadId, userId, content) VALUES(1, 1, 'Test post')";
    $post2 = "INSERT INTO post(threadId, userId, content) VALUES(1, 1, 'Test post2')";

    $DBConnection -> exec($user);
    $DBConnection -> exec($user2);
    $DBConnection -> exec($thread);
    $DBConnection -> exec($thread2);
    $DBConnection -> exec($post);
    $DBConnection -> exec($post2);

    $response = new ResponseModel();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->addMessage("Created some dummy data in db");
    $response->send();
    exit();

} catch(PDOException $e) {

    error_log("Database error - ".$e, 0);
    $response = new ResponseModel();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database write error: $e");
    $response->send();
    exit();
}
