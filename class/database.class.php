<?php
require 'vendor/autoload.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

class Database{
    function __construct(){
        $this->client = new MongoDB\Client(
            '<MONGODB-STRING-CONNECTION>'
        );
    
        $this->db = $this->client->test;
    }

    public function insert($data){
        if($this->db->players->insertOne($data)){
            return true;
        }
    }
}