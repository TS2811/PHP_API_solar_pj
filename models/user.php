<?php
class user
{
    private $connDB;

    public $message;

    public $userID;
    public $username;
    public $password;
    public $email;
    public $phone;
    public $location;
    public $imageName;
    public $updated_at;
    public $rank;

    public function __construct($connectDB)
    {
        $this->connDB = $connectDB;
    }
    public function login($username, $password)
    {
        $sql = "SELECT * FROM user WHERE username = :username";
        $username = htmlspecialchars(strip_tags($username));
        $stmt = $this->connDB->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            return $user; // เข้าระบบสำเร็จ
        } else {
            return false; // ล้มเหลว
        }
    }
    // public function login($username, $password)
    // {
    //     $sql = "SELECT * FROM user WHERE username = :username AND password = :password";

    //     $username = htmlspecialchars(strip_tags($username));
    //     $password = htmlspecialchars(strip_tags($password));

    //     $stmt = $this->connDB->prepare($sql);

    //     $stmt->bindParam(':username', $username);
    //     $stmt->bindParam(':password', $password);

    //     $stmt->execute();

    //     return $stmt;
    // }

    public function insertNewUser($username, $password, $email, $phone, $location, $imageName)
    {
        // ตรวจสอบว่ามี imageName หรือไม่
        $imageName = !empty($imageName) ? htmlspecialchars(strip_tags($imageName)) : null;
        // ป้องกัน SQL Injection
        $username = htmlspecialchars(strip_tags($username));
        $password = htmlspecialchars(strip_tags($password));
        $email = htmlspecialchars(strip_tags($email));
        $phone = htmlspecialchars(strip_tags($phone));
        $location = htmlspecialchars(strip_tags($location));

        // เข้ารหัสรหัสผ่านก่อนบันทึก
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // ตรวจสอบ username ซ้ำก่อน INSERT
        $checkQuery = "SELECT COUNT(*) FROM user WHERE username = :username";
        $checkStmt = $this->connDB->prepare($checkQuery);
        $checkStmt->bindParam(':username', $username);
        $checkStmt->execute();
        $count = $checkStmt->fetchColumn();

        if ($count > 0) {
            return "duplicate";  // แจ้งว่า username ซ้ำ
        }

        $query = "";

        if (!empty($imageName)) {
            $query = "INSERT INTO user (username, password, email, phone, location, imageName) 
                  VALUES (:username, :password, :email, :phone, :location, :imageName)";
        } else {
            $query = "INSERT INTO user (username, password, email, phone, location) 
                  VALUES (:username, :password, :email, :phone, :location)";
        }

        $stmt = $this->connDB->prepare($query);
        $stmt->bindParam(':username', $username);
        // $stmt->bindParam(':password', $password);
        $stmt->bindParam(':password', $hashedPassword);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':location', $location);
        if (!empty($imageName)) {
            $stmt->bindParam(':imageName', $imageName);
        }

        // ตรวจสอบการ execute
        if ($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public function updateUser($userID, $username, $password, $email, $phone, $location, $imageName)
    {
        $query = "UPDATE user SET ";
        $fields = [];

        if (!empty($username)) {
            $fields[] = "username = :username";
        }
        if (!empty($password)) {
            $fields[] = "password = :password";
        }
        if (!empty($email)) {
            $fields[] = "email = :email";
        }
        if (!empty($phone)) {
            $fields[] = "phone = :phone";
        }
        if (!empty($location)) {
            $fields[] = "location = :location";
        }
        if (!empty($imageName)) {
            $fields[] = "imageName = :imageName";
        }

        if (empty($fields)) {
            return false;
        }

        $query .= implode(", ", $fields);
        $query .= " WHERE userID = :userID";

        $userID = intval(htmlspecialchars(strip_tags($userID)));
        if (!empty($username)) {
            $username = htmlspecialchars(strip_tags($username));
        }
        if (!empty($password)) {
            $password = htmlspecialchars(strip_tags($password));
            $password = password_hash($password, PASSWORD_DEFAULT);
        }
        if (!empty($email)) {
            $email = htmlspecialchars(strip_tags($email));
        }
        if (!empty($phone)) {
            $phone = htmlspecialchars(strip_tags($phone));
        }
        if (!empty($location)) {
            $location = htmlspecialchars(strip_tags($location));
        }
        if (!empty($imageName)) {
            $imageName = htmlspecialchars(strip_tags($imageName));
        }

        $stmt = $this->connDB->prepare($query);
        $stmt->bindParam(":userID", $userID, PDO::PARAM_INT);
        if (!empty($username)) {
            $stmt->bindParam(":username", $username);
        }
        if (!empty($password)) {
            $stmt->bindParam(":password", $password);
        }
        if (!empty($email)) {
            $stmt->bindParam(":email", $email);
        }
        if (!empty($phone)) {
            $stmt->bindParam(":phone", $phone);
        }
        if (!empty($location)) {
            $stmt->bindParam(":location", $location);
        }
        if (!empty($imageName)) {
            $stmt->bindParam(":imageName", $imageName);
        }

        return $stmt->execute();
    }

    public function getByUserID($userID)
    {
        $query = "SELECT * FROM user WHERE userID = :userID";

        $userID = htmlspecialchars(strip_tags($userID));

        $stmt = $this->connDB->prepare($query);

        $stmt->bindParam(':userID', $userID);

        $stmt->execute();

        return $stmt;
    }
}
