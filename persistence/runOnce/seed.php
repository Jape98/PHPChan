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
    $user = "INSERT INTO user(username, email, password) VALUES('TestUser', 'tester2@test.com', 'test')";
    $user2 = "INSERT INTO user(username, email, password) VALUES('TestUser2', 'tester@test.com', 'test')";
    $thread = "INSERT INTO thread(userId, content) VALUES(1, 'Test thread.')";
    $thread2 = "INSERT INTO thread(userId, content) VALUES(2, 'Second test thread.')";
    $post = "INSERT INTO post(threadId, userId, content) VALUES(1, 1, 'Test post')";
    $post2 = "INSERT INTO post(threadId, userId, content) VALUES(1, 1, 'Test post2')";

    $writeDB -> exec($user);
    $writeDB -> exec($user2);
    $writeDB -> exec($thread);
    $writeDB -> exec($thread2);
    $writeDB -> exec($post);
    $writeDB -> exec($post2);

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
    $response->addMessage("Database write error");
    $response->send();
    exit();
}
