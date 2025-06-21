<?php
class report_log
{
    private $connDB;

    public $message;

    public $reportID;
    public $reportContent;
    public $logDate;
    public $note;
    public $eqpID;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

    public function add_error_report($reportContent, $logDate, $note, $eqpID)
    {
        $sql = "INSERT INTO report_log (reportContent, logDate, note, eqpID) 
            VALUES (:reportContent, :logDate, :note, :eqpID)";

        $reportContent = htmlspecialchars(strip_tags($reportContent));
        $logDate = htmlspecialchars(strip_tags($logDate));
        $note = htmlspecialchars(strip_tags($note));
        $eqpID = htmlspecialchars(strip_tags($eqpID));

        $stmt = $this->connDB->prepare($sql);

        // ใช้พารามิเตอร์ที่รับเข้ามาในฟังก์ชัน
        $stmt->bindParam(":reportContent", $reportContent);
        $stmt->bindParam(":logDate", $logDate);
        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":eqpID", $eqpID);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function update_report($reportID, $note)
    {
        $sql = "UPDATE report_log SET `note` = :note WHERE `reportID` = :reportID";

        $note = htmlspecialchars(strip_tags($note));
        $reportID = htmlspecialchars(strip_tags($reportID));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":note", $note);
        $stmt->bindParam(":reportID", $reportID);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function check_report($logDate)
    {
        $sql = "SELECT * FROM report_log WHERE `logDate` = :logDate LIMIT 1";

        $logDate = htmlspecialchars(strip_tags($logDate));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":logDate", $logDate);

        $stmt->execute();

        return $stmt;
    }
}
