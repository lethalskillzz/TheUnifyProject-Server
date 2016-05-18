<?php 

class PolicyFunctions {

    private $conn;
    private $util;

    // constructor
    function __construct() {

        require_once(__DIR__.'/../DbConnect.php');
        include_once(__DIR__.'/../util/UtilFunctions.php');

        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();

        $this->util = new UtilFunctions();

    }

    // destructor
    function __destruct() {}




    public function reportUser($username, $target_username, $report) {

        $sql = $this->conn->prepare("insert into report_user (username, target_username, report) 
                    values ('$username', '$target_username', '".$this->conn->real_escape_string($report)."')");

        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return OPERATION_SUCCESSFULL;
        }
        return OPERATION_FAILED;
    }


    public function reportFeed($username, $feedId) {

        $sql = $this->conn->prepare("insert into report_feed (username, feedId) values ('$username', $feedId)");
        $result = $sql->execute();
        $sql->close();
        if ($result) {
            return OPERATION_SUCCESSFULL;
        }
        return OPERATION_FAILED;
    }

    public function reportShop($username, $shopId) {

        $sql = $this->conn->prepare("insert into report_shop (username, shopId) values ('$username', $shopId)");
        $result = $sql->execute();
        $sql->close();
        if ($result) {
            return OPERATION_SUCCESSFULL;
        }
        return OPERATION_FAILED;
    }


}