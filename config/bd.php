<?php

class bd {
    private $host;
    private $dbname;
    private $user;
    private $password;

    function __construct(){
        $this->host = "localhost";
        $this->dbname = "bakersoft";
        $this->user = "root";
        $this->password = "";
    }

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