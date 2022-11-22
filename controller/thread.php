<?php

require_once('../persistence/db.php');
require_once('../model/ThreadModel.php');
require_once('../model/PostModel.php');
require_once('../model/ResponseModel.php');

try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();

//throw 500 if failed
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

if(array_key_exists("id", $_GET)) {

    $Id = $_GET['id'];
    //if id is invalid, return 400
    if ($Id == '' || !is_numeric($Id)){
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Invalid thread id");
        $response->send();
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {
        //query to get thread by id,
        $query = $readDB->prepare('SELECT t.id AS threadId, u.username AS threadUsername, u.id AS threadUserId, t.content AS threadContent, DATE_FORMAT(t.createdAt, "%d.%m.%Y %H:%i") AS threadCreatedAt, p.id AS postId , p.content AS postContent, DATE_FORMAT(p.createdAt, "%d.%m.%Y %H:%i") AS postCreatedAt, pu.id AS postUserId, pu.username AS postUsername FROM thread t INNER JOIN post p ON t.id = p.threadId INNER JOIN user u ON t.userId = u.id INNER JOIN user pu ON p.userId = pu.id WHERE t.id = :threadid');
        $query->bindParam(':threadid', $Id, PDO::PARAM_INT);
        $query->execute();
        $rowCount = $query->rowCount();

        //if query is empty, return 404
        if($rowCount === 0) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Thread not found");
            $response->send();
            exit();
        }
        
        $i=true;
        //insert query to thread and post models and return 200
        while($row = $query ->fetch(PDO::FETCH_ASSOC)) {

            if($i==true){
                $thread = new Thread($row['threadId'], $row['threadUserId'], $row['threadUsername'], $row['threadContent'], $row['threadCreatedAt']);
                $i=false;
            }
            $post = new Post($row['postId'], $row['postCreatedAt'], $row['postUserId'], $row['postUsername'], $row['postContent']);
            $thread->addPost($post->returnPostAsArray());
        }
            $threads[] = $thread->returnThreadAsArray();
            $returnArray = array();
            $returnArray['rows_returned'] = $rowCount;
            $returnArray['threads'] = $threads;

            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnArray);
            $response->send();
            exit();
            
        }
    //return 500 if problem with php
    catch (ThreadException $e){
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage($e->getMessage());
        $response->send();
        exit();
    }
    //return 500 if problem database
    catch (PDOException $e) {
        error_log("Database query error - ".$e, 0);
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Failed to get the thread");
        $response->send();
        exit();
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    exit();
}
else {
    $response = new ResponseModel();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit();
}