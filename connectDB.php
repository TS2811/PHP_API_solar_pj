<?php
class connectDB extends mysqli
{
    public $connDB;

    // private $hostDB = "localhost";
    // private $userDB = "root";
    // private $passDB = "";
    // private $dbName = "solarpanel_db";
    
    private $hostDB = "153.92.15.29";
    private $userDB = "u760396085_solarpanel";
    private $passDB = "Solar@2024";
    private $dbName = "u760396085_solarpanel_db";
    private $portDB = 3306;


    public function getConnectionDB()
    {
        $this->connDB = null;

        try {
            $this->connDB = new PDO(
                "mysql:host=" . $this->hostDB .
                    ";port=" . $this->portDB .
                    ";dbname=" . $this->dbName,
                $this->userDB,
                $this->passDB
            );

            $this->connDB->exec("set names utf8");
            // $this->connDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        return $this->connDB;
    }
}