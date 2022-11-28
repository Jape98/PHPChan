<?php

require_once("../persistence/db.php");
require_once("../model/ResponseModel.php");

try {
    $writeDB = DB::connectWriteDB();
    $readDB = DB::connectReadDB();

    header('Content-type: application/json;charset=utf-8;');
    $response = new ResponseModel();
    $response->setSuccess(true);
    $response->setHttpStatusCode(200);
    $response->addMessage("Test");
    $response->send();
    exit;

} catch (PDOException $e) {
    header('Content-type: application/json;charset=utf-8;');
    $response = new ResponseModel();
    $response->setSuccess(false);
    $response->setHttpStatusCode(500);
    $response->addMessage("Database Connection error");
    $response->send();
    exit;
}