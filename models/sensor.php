<?php
class sensor
{
    private $connDB;

    public $message;

    public $current;
    public $voltage;
    public $Temperature;
    public $Humidity;
    public $Light1;
    public $Light2;
    public $Light3;
    public $SendDateTime;
    public $avg_time;
    public $eqpID;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }

    public function sendData($current, $voltage, $Temperature, $Humidity, $Light1, $Light2, $Light3, $SendDateTime, $eqpID)
    {
        $sql = "INSERT INTO sensor (current, voltage, Temperature, Humidity, Light1, Light2, Light3, SendDateTime, eqpID) 
            VALUES (:current, :voltage, :Temperature, :Humidity, :Light1, :Light2, :Light3, :SendDateTime, :eqpID)";

        $current = htmlspecialchars(strip_tags($current));
        $voltage = htmlspecialchars(strip_tags($voltage));
        $Temperature = htmlspecialchars(strip_tags($Temperature));
        $Humidity = htmlspecialchars(strip_tags($Humidity));
        $Light1 = htmlspecialchars(strip_tags($Light1));
        $Light2 = htmlspecialchars(strip_tags($Light2));
        $Light3 = htmlspecialchars(strip_tags($Light3));
        $SendDateTime = htmlspecialchars(strip_tags($SendDateTime));
        $eqpID = htmlspecialchars(strip_tags($eqpID));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":current", $current);
        $stmt->bindParam(":voltage", $voltage);
        $stmt->bindParam(":Temperature", $Temperature);
        $stmt->bindParam(":Humidity", $Humidity);
        $stmt->bindParam(":Light1", $Light1);
        $stmt->bindParam(":Light2", $Light2);
        $stmt->bindParam(":Light3", $Light3);
        $stmt->bindParam(":SendDateTime", $SendDateTime);
        $stmt->bindParam(":eqpID", $eqpID);

        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllData($eqpID)
    {
        // $sql = "SELECT * FROM sensor WHERE eqpID = :eqpID";
        $sql = "SELECT * FROM sensor ORDER BY `SendDateTime` desc LIMIT 60";

        $stmt = $this->connDB->prepare($sql);

        // $stmt->bindParam(":eqpID", $eqpID);

        $stmt->execute();

        return $stmt;
    }

    public function getAllDataWhereDateToDateThousand($startDate, $endDate)
{
    $sql = "WITH TotalRows AS (
        SELECT COUNT(*) AS total FROM `sensor`
        WHERE SendDateTime BETWEEN :startDate AND :endDate
    ),
    RankedData AS (
        SELECT 
            current, voltage, Temperature, Humidity, Light1, Light2, Light3, SendDateTime,
            CASE 
                WHEN (SELECT total FROM TotalRows) >= 1000 
                THEN NTILE(1000) OVER (ORDER BY SendDateTime)
                ELSE ROW_NUMBER() OVER (ORDER BY SendDateTime)
            END AS GroupNum
        FROM `sensor`
        WHERE SendDateTime BETWEEN :startDate AND :endDate
    )
    SELECT 
        GroupNum,
        AVG(current) AS current,
        AVG(voltage) AS voltage,
        AVG(Temperature) AS Temperature,
        AVG(Humidity) AS Humidity,
        AVG(Light1) AS Light1,
        AVG(Light2) AS Light2,
        AVG(Light3) AS Light3,
        MIN(SendDateTime) AS start_time,
        FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(SendDateTime))) AS avg_time
    FROM RankedData
    GROUP BY GroupNum
    ORDER BY start_time;";

    $startDate = htmlspecialchars(strip_tags($startDate));
    $endDate = htmlspecialchars(strip_tags($endDate));

    $stmt = $this->connDB->prepare($sql);
    $stmt->bindParam(":startDate", $startDate);
    $stmt->bindParam(":endDate", $endDate);
    $stmt->execute();

    return $stmt;
}

    public function getAllDataWhereDateToDate($startDate, $endDate)
    {
        $sql = "SELECT * FROM sensor WHERE `SendDateTime` BETWEEN :startDate AND :endDate ORDER BY `SendDateTime` DESC";

        $startDate = htmlspecialchars(strip_tags($startDate)) . " 00:00:00";
        $endDate = htmlspecialchars(strip_tags($endDate)) . " 23:59:59";

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":startDate", $startDate);
        $stmt->bindParam(":endDate", $endDate);

        $stmt->execute();

        return $stmt;
    }


    public function getOneData()
    {
        $sql = "SELECT * FROM sensor ORDER BY `SendDateTime` desc LIMIT 1";

        $stmt = $this->connDB->prepare($sql);

        // $stmt->bindParam(":eqpID", $eqpID);

        $stmt->execute();

        return $stmt;
    }

    public function getBefore($dateTime)
    {
        $sql = "SELECT * FROM sensor WHERE SendDateTime < :dateTime AND current IS NOT NULL AND current > 0 AND voltage IS NOT NULL AND voltage > 0 AND Light1 IS NOT NULL AND Light1 > 0 AND Light2 IS NOT NULL AND Light2 > 0 AND Light3 IS NOT NULL AND Light3 > 0 ORDER BY SendDateTime DESC LIMIT 1";

        $dateTime = htmlspecialchars(strip_tags($dateTime));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":dateTime", $dateTime);

        $stmt->execute();

        return $stmt;
    }
    public function getAfter($dateTime)
    {
        $sql = "SELECT * FROM sensor WHERE SendDateTime > :dateTime AND current IS NOT NULL AND current > 0 AND voltage IS NOT NULL AND voltage > 0 AND Light1 IS NOT NULL AND Light1 > 0 AND Light2 IS NOT NULL AND Light2 > 0 AND Light3 IS NOT NULL AND Light3 > 0 ORDER BY SendDateTime ASC LIMIT 1";

        $dateTime = htmlspecialchars(strip_tags($dateTime));

        $stmt = $this->connDB->prepare($sql);

        $stmt->bindParam(":dateTime", $dateTime);

        $stmt->execute();

        return $stmt;
    }
}
