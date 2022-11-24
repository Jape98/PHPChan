<?php

require_once('../persistence/db.php');
require_once('../model/ThreadModel.php');
require_once('../model/PostModel.php');
require_once('../model/ResponseModel.php');

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

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    #region GET single thread by ID and all of its messages 

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
            
            
            //insert query to thread and post models and return 200
            $i=true;
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
    #endregion

    #region GET all threads
    } elseif(empty($_GET)) {

        try {
            $query = $readDB->prepare('SELECT t.id AS threadId, u.username AS threadUsername, u.id AS threadUserId, t.content AS threadContent, DATE_FORMAT(t.createdAt, "%d.%m.%Y %H:%i") AS threadCreatedAt FROM thread t INNER JOIN user u ON t.userId = u.id');
            $query->execute();

            $threads = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {

                $thread = new Thread($row['threadId'], $row['threadUserId'], $row['threadUsername'], $row['threadContent'], $row['threadCreatedAt']);
                $threads[] = $thread->returnThreadAsArray();
            }
                
            $returnArray = array();
            $returnArray['threads'] = $threads;

            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($returnArray);
            $response->send();
            exit();

        } catch(ThreadException $e) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage($e->getMessage());
            $response->send();
            exit();
        }
        catch(PDOException $e) {
          error_log("Database Error: ".$e, 0);
          $response = new ResponseModel();
          $response->setHttpStatusCode(500);
          $response->setSuccess(false);
          $response->addMessage("Failed to get threads");
          $response->send();
          exit();
        }

    } else {
        $response = new ResponseModel();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Page not found");
        $response->send();
        exit();
    }
    #endregion
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    try {
        $query = $writeDB->prepare('DELETE FROM thread WHERE id = :threadid');
        $query->bindParam(':threadid', $Id, PDO::PARAM_INT);
        $query->execute();

        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $response = new ResponseModel();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Thread not found");
            $response->send();
            exit();
        }

        $response = new ResponseModel();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Thread deleted");
        $response->send();
        exit();

    } catch (PDOException $e) {
        $response = new ResponseModel();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Thread deleted");
        $response->send();
        exit();
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {

    exit();

} else {
    $response = new ResponseModel();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Request method not allowed");
    $response->send();
    exit();
}