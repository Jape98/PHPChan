<?php
require_once("../model/ResponseModel.php");

$response = new ResponseModel();
$response->setSuccess(true);
$response->setHttpStatusCode(200);
$response->addMessage("Test");
$response->send();