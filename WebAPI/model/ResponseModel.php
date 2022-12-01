<?php

class ResponseModel {
    private $_success;
    private $_httpStatusCode;
    private $_infoMessage = array();
    private $_data;
    private $_toCache = false;
    private $_Data = array();

    public function setSuccess($success) {
        $this -> _success = $success;
    }
    public function setHttpStatusCode($httpStatusCode) {
        $this -> _httpStatusCode = $httpStatusCode;
    }
    public function addMessage($message) {
        $this -> _infoMessage[] = $message;
    }
    public function setData($data) {
        $this -> _data = $data;
    }
    public function toCache($toCache) {
        $this -> _toCache = $toCache;
    }

    public function send() {
        header('Content-type: application/json;charset=utf-8;');

        //FOR DEVELOPMENT!!!
        //TODO: REMOVE!
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");

        if($this->_toCache == true){
            header('Cache.control: max-age=60');
        } else {
            header('Cache-control: no-cache, no-store');
        }

        if(($this->_success !== false && $this->_success !== true) || (!is_numeric($this -> _httpStatusCode))) 
        {
            http_response_code(500);
            $this->_Data['statusCode'] = 500;
            $this->_Data['success'] = false;
            $this->addMessage("Error creating the ");
            $this->_Data['messages'] = $this -> _infoMessage;
        }
        else 
        {
            http_response_code($this -> _httpStatusCode);
            $this->_Data['statusCode'] = $this -> _httpStatusCode;
            $this->_Data['success'] = $this -> _success;
            $this->_Data['messages'] = $this -> _infoMessage;
            $this->_Data['data'] = $this -> _data;
        }

        //FOR DEVELOPMENT!!!
        //TODO: REMOVE!
        $debug = false;

        if ($debug) {
            //full data with message
            echo json_encode($this->_Data);

        } else {
            //only data if everywthing went ok, message if data is null.
            if($this->_data === null || $this->_data === ""){
                echo json_encode($this->_infoMessage);
            } else {
                echo json_encode($this->_data);
            }
        }  
    }
}