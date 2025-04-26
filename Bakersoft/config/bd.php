<?php

class bd {
    private $host = "localhost";
    private $dbname = "bakersoft";
    private $user = "root";
    private $password = "";

    public function conexion(){
    try{
        $PDO = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->user,$this->password);
        return $PDO;
    }catch(PDOexception $ex){
        return $ex->getMessage();
    }
    }
}
?>