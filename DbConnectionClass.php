<?php
class DbConnection {

    protected $dbServer = "db148a.pair.com";
    protected $databaseName = "ksinton_fitness";
    protected $dbUserName = "ksinton_26";
    protected $dbPassword = "dragon99";
    
    public $connection = null;

    public function getDbConnection() {

        $this->connection = new mysqli($this->dbServer, $this->dbUserName, $this->dbPassword, $this->databaseName);

        if($this->connection->connect_errno > 0){
            die('Unable to connect to database [' . $this->connection->connect_errno . ']');
        }


        return $this->connection;
    }
}