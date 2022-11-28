<?php

class ThreadException extends Exception {}

class Thread {

    private $_id;
    private $_userId;
    private $_userName;
    private $_content;
    private $_createdAt;
    private $_posts = array();

    public function __construct($id, $userId, $userName, $content, $createdAt){
        $this->setId($id);
        $this->setUserId($userId);
        $this->setUserName($userName);
        $this->setContent($content);
        $this->setCreatedAt($createdAt);
    }

    public function getId() {
        return $this->_id;
    }
    public function getUserId() {
        return $this->_userId;
    }
    public function getUserName() {
        return $this->_userName;
    }
    public function getContent() {
        return $this->_content;
    }
    public function getCreatedAt() {
        return $this->_createdAt;
    }
    public function getPosts() {
        return $this->_posts;
    }

    public function setContent($content) {

        if(($content !== null) && (strlen($content) < 0 || strlen($content) > 5000)) {
            throw new ThreadException("Content error");
        }
        $this -> _content = $content;
    }
    public function setId($id) {

        if(($id !== null) &&
        (!is_numeric($id) || $id <= 0 || $id > 9223372036854775807 || $this -> _id !== null)) {
            throw new ThreadException("Thread id error");
        }
        $this->_id = $id;
    }
    public function setUserId($userId) {

        if(($userId !== null) &&
        (!is_numeric($userId) || $userId <= 0 || $userId > 9223372036854775807 || $this -> _userId !== null)) {
            throw new ThreadException("User id error");
        }
        $this->_userId = $userId;
    }
    public function setUserName($userName) {

        if(($userName !== null) &&( strlen($userName) < 1 )) {
            throw new ThreadException("User name error");
        }
        $this->_userName = $userName;
    }
    public function setCreatedAt($createdAt) {
        //make sure that passed in variable is same format as the conversion
        if($createdAt !== null && date_format(date_create_from_format('d.m.Y H:i', $createdAt), "d.m.Y H:i") != $createdAt){
            throw new ThreadException("Timestamp error");
        }
        $this -> _createdAt = $createdAt;
    }
    public function addPost($post) {
        $this -> _posts[] = $post;
    }

    public function returnThreadAsArray() {
        $thread = array();
        $thread['id'] = $this->getId();
        $thread['userId'] = $this->GetUserId();
        $thread['userName'] = $this->GetUserName();
        $thread['content'] = $this->getContent();
        $thread['createdAt'] = $this->getCreatedAt();
        $thread['posts'] = $this->getPosts();

        return $thread;
    }
}