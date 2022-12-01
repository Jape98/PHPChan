<?php
#region depencies
require_once('../persistence/db.php');
require_once('../model/ThreadModel.php');
require_once('../model/PostModel.php');
require_once('../model/ResponseModel.php');
require_once('../authentication/currentUser.php');
#endregion

$DBConnection = DB::connectDB();

if(empty($_GET)) {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {

        #region GET all threads
        try {
            $query = $DBConnection->prepare('SELECT t.id AS threadId, u.username AS threadUsername, u.id AS threadUserId, t.content AS threadContent, DATE_FORMAT(t.createdAt, "%d.%m.%Y %H:%i") AS threadCreatedAt FROM thread t LEFT JOIN user u ON t.userId = u.id');
            $query->execute();

            $threads = array();
            while($row = $query->fetch(PDO::FETCH_ASSOC)) {

                $thread = new Thread($row['threadUserId'], $row['threadContent']);
                $thread->setId($row['threadId']);
                $thread->setUserName($row['threadUsername']);
                $thread->setCreatedAt($row['threadCreatedAt']);
                $threads[] = $thread->returnThreadAsArray();
            }

            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($threads);
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
    #endregion

    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {

        #region create a new thread
        try {
            if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
               $response = new ResponseModel();
               $response->setHttpStatusCode(400);
               $response->setSuccess(false);
               $response->addMessage("Content type header not set to JSON");
               $response->send();
               exit();
            }
           
            $rawPostData = file_get_contents('php://input');
            if(!$inputJson = json_decode($rawPostData)) {
               $response = new ResponseModel();
               $response->setHttpStatusCode(400);
               $response->setSuccess(false);
               $response->addMessage("Request body not valid JSON");
               $response->send();
               exit();
            }

            if(!isset($inputJson->content)) {
               $response = new ResponseModel();
               $response->setHttpStatusCode(400);
               $response->setSuccess(false);
               $response->addMessage("Thread can not be empty!");
               $response->send();
               exit();
            }

            $newThread = new Thread($fromDB_userId, $inputJson->content);
            $newUserId = $newThread->getUserId();
            $newContent = $newThread->getContent();

            $query = $DBConnection->prepare("INSERT INTO thread(userId, content) VALUES(:userId, :content)");
            $query->bindParam(":userId", $newUserId, PDO::PARAM_INT);
            $query->bindParam(":content", $newContent, PDO::PARAM_STR);
            $query->execute();

            $rowCount = $query->rowCount();
            if ($rowCount === 0) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(500);
                $response->setSuccess(false);
                $response->addMessage("Error creating a new thread. Please try again");
                $response->send();
                exit();
            }
            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->addMessage("Thread created successfully");
            $response->send();
            exit();
            
        } catch(ThreadException $e) {
           $response = new ResponseModel();
           $response->setHttpStatusCode(400);
           $response->setSuccess(false);
           $response->addMessage($e->getMessage());
           $response->send();
           exit();

        } catch(PDOException $e) {
           error_log("Database Query Error: ".$e, 0);
           $response = new ResponseModel();
           $response->setHttpStatusCode(500);
           $response->setSuccess(false);
           $response->addMessage("Failed to create new thread");
           $response->send();
           exit();
        }
       #endregion

    } else {

        #region other methods not allowed
        $response = new ResponseModel();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Method not allowed");
        $response->send();
        exit();
        #endregion
    
    } 

} elseif(array_key_exists("id", $_GET)) {

    #region Check if id is valid
    $Id = $_GET['id'];   
    if ($Id == '' | !is_numeric($Id)){
        $response = new ResponseModel();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Invalid thread id");
        $response->send();
        exit();
    }
    #endregion

    if ($_SERVER['REQUEST_METHOD'] === 'GET'&& isLoggedIn()) {

        #region GET single thread by ID and all of its messages 
        try {
            //query to get thread by id,
            $query = $DBConnection->prepare('SELECT t.id AS threadId, u.username AS threadUsername, u.id AS threadUserId, t.content AS threadContent, DATE_FORMAT(t.createdAt, "%d.%m.%Y %H:%i") AS threadCreatedAt, p.id AS postId , p.content AS postContent, DATE_FORMAT(p.createdAt, "%d.%m.%Y %H:%i") AS postCreatedAt, pu.id AS postUserId, pu.username AS postUsername FROM thread t LEFT JOIN post p ON t.id = p.threadId LEFT JOIN  user u ON t.userId = u.id LEFT JOIN  user pu ON p.userId = pu.id WHERE t.id = :threadid');
            $query->bindParam(':threadid', $Id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();

            //if query is empty, return 404
            if($rowCount === 0 || $query === null) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(404);
                $response->setSuccess(false);
                $response->addMessage("Thread not found");
                $response->send();
                exit();
            }
            $i=true;
            while($row = $query ->fetch(PDO::FETCH_ASSOC)) {

                if($i==true){
                    $thread = new Thread($row['threadUserId'], $row['threadContent']);
                    $thread->setId($row['threadId']);
                    $thread->setUserName($row['threadUsername']);
                    $thread->setCreatedAt($row['threadCreatedAt']);
                    $i=false;
                }

                if ($row['postId'] !== null) {
                    $post = new Post($row['postId'], $row['postCreatedAt'], $row['postUserId'], $row['postUsername'], $row['postContent']);
                    $thread->addPost($post->returnPostAsArray());
                }
            }
            
            $threads[] = $thread->returnThreadAsArray();
            $response = new ResponseModel();
            $response->setHttpStatusCode(200);
            $response->setSuccess(true);
            $response->toCache(true);
            $response->setData($threads);
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

    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isLoggedIn()) {

        #region Check if user is authorized to delete thread
        try{
            $query = $DBConnection->prepare('SELECT u.id FROM user u INNER JOIN thread t WHERE u.id = t.userId AND t.id = :threadid');
            $query->bindParam(':threadid', $Id, PDO::PARAM_INT);
            $query->execute();

            $rowCount = $query->rowCount();
            $row = $query ->fetch(PDO::FETCH_ASSOC);

            if($rowCount === 0 || $query === null || ($row['id'] !== $fromDB_userId)) {
                $response = new ResponseModel();
                $response->setHttpStatusCode(401);
                $response->setSuccess(false);
                $response->addMessage("You are not authorized to do that!");
                $response->send();
                exit();
            }

        } catch (PDOException $e) {
            error_log("Connection error - ".$e, 0);
            $response = new ResponseModel();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to delete thread, please try again.");
            $response->send();
            exit();
        }
        #endregion

        #region DELETE single thread by ID
        try {
            $query = $DBConnection->prepare('DELETE FROM thread WHERE id = :threadid');
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
            $response->addMessage("Failed to delete thread, please try again.");
            $response->send();
            exit();
        }
        #endregion

    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isLoggedIn()) {

        //TODO: tää
        #region Update thread
        #endregion

    } else {

        #region other methods not allowed
        $response = new ResponseModel();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Method not allowed");
        $response->send();
        exit();
        #endregion
    
    }

} else {
    $response = new ResponseModel();
    $response->setHttpStatusCode(404);
    $response->setSuccess(false);
    $response->addMessage("Endpoint not found");
    $response->send();
    exit();
}