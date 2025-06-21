<?php
class cleaning_schedule
{
    private $connDB;

    public $message;

    public $scheduleID;
    public $cleaningDate;
    public $eqpID;
    public $OrderedBy;


    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

    public function getAllCleaningSchedule()
    {
        $query = "SELECT * FROM cleaning_schedule ORDER BY `cleaningDate` desc";
        $stmt = $this->connDB->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function addCleaningSchedule($cleaningDate, $eqpID, $OrderedBy)
    {
        $query = "INSERT INTO cleaning_schedule (cleaningDate, eqpID, OrderedBy) VALUES (:cleaningDate, :eqpID , :OrderedBy)";

        $cleaningDate = htmlspecialchars(strip_tags($cleaningDate));
        $eqpID = htmlspecialchars(strip_tags($eqpID));
        $OrderedBy = htmlspecialchars(strip_tags($OrderedBy));

        $stmt = $this->connDB->prepare($query);

        $stmt->bindParam(':cleaningDate', $cleaningDate);
        $stmt->bindParam(':eqpID', $eqpID);
        $stmt->bindParam(':OrderedBy', $OrderedBy);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
