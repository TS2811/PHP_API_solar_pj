<?php
class equipment
{
    private $connDB;

    public $message;

    public $eqpID;
    public $eqpName;
    public $status;
    public $createDateTime;
    public $updated_at;
    public $relationshipESPID;
    public $userID;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

}