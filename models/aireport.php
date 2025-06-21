<?php
class ai_report
{
    private $connDB;

    public $message;

    public $No_AIreport;
    public $report;
    public $modelAIname;
    public $senserTime;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

    public function addReport($report, $modelAIname, $senserTime)
    {
        $sql = "INSERT INTO ai_report (report, modelAIname, senserTime) 
            VALUES (:report, :modelAIname, :senserTime)";

        $report = htmlspecialchars(strip_tags($report));
        $modelAIname = htmlspecialchars(strip_tags($modelAIname));
        $senserTime = htmlspecialchars(strip_tags($senserTime));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":report", $report);
        $stmt->bindParam(":modelAIname", $modelAIname);
        $stmt->bindParam(":senserTime", $senserTime);
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }
}