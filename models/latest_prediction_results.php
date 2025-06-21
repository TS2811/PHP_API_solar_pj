<?php
class LatestPredictionResults
{
    private $connDB;

    public $message;

    public $RandomForest;
    public $KNN;
    public $GBR;
    public $NaiveBayes;
    public $DecisionTreet;
    public $senserTime;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

    public function getReport()
    {
        $sql = "SELECT MAX(CASE WHEN `modelAIname` = 'Random Forest' THEN report END) AS `RandomForest`,MAX(CASE WHEN `modelAIname` = 'GBR' THEN report END) AS `GBR`, MAX(CASE WHEN `modelAIname` = 'KNN' THEN report END) AS `KNN`, MAX(CASE WHEN `modelAIname` = 'Naive Bayes' THEN report END) AS `NaiveBayes`, MAX(CASE WHEN `modelAIname` = 'Decision Treet' THEN report END) AS `DecisionTreet`, MAX(`senserTime`) AS `senserTime` FROM `ai_report` WHERE `senserTime` = (SELECT MAX(`senserTime`) FROM `ai_report`);";
        $stmt = $this->connDB->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
    public function getAllReport()
    {
        $sql = "SELECT `senserTime`,MAX(CASE WHEN `modelAIname` = 'GBR' THEN report END) AS `GBR`, MAX(CASE WHEN `modelAIname` = 'Random Forest' THEN report END) AS `RandomForest`, MAX(CASE WHEN `modelAIname` = 'KNN' THEN report END) AS `KNN`, MAX(CASE WHEN `modelAIname` = 'Naive Bayes' THEN report END) AS `NaiveBayes`, MAX(CASE WHEN `modelAIname` = 'Decision Treet' THEN report END) AS `DecisionTreet` FROM `ai_report` GROUP BY `senserTime` ORDER BY `senserTime` DESC;";
        $stmt = $this->connDB->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}
