<?php

class FollowFunctions {

    private $conn;
    private $util;
    private $notify;

    // constructor
    function __construct() {

        require_once (__DIR__ .  '/../DbConnect.php');
        include_once (__DIR__ .  '/../util/UtilFunctions.php');
        include_once (__DIR__ .  '/../notification/NotificationFunctions.php');

        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();

        $this->util = new UtilFunctions();
        $this->notify = new NotificationFunctions();

    }

    // destructor
    function __destruct() {
    }


    public function followUser($username, $target_username) {

        if(!$this->util->isFollow($username,$target_username)) {
            $sql = $this->conn->prepare("insert into follow (username, target_username) 
                    values ( '$username', '$target_username' )");

            $result = $sql->execute();
            $sql->close();

            if($result) {
              $this->notify->notifyFollow($username,$target_username); 
              return OPERATION_SUCCESSFULL;
            }
        }
        return OPERATION_FAILED;
    }



    public function unfollowUser($username, $target_username) {

       if($this->util->isFollow($username,$target_username)) {
         $sql = $this->conn->prepare("delete from follow where 
                username = '$username' and target_username = '$target_username'");

         $result = $sql->execute();
         $sql->close();

         if($result) {
           return OPERATION_SUCCESSFULL;
         }
       }
        return OPERATION_FAILED;
    }



    public function loadFollowing($username, $target_username, $follow_pos) {

        $extra = "limit $follow_pos,10";
     
        $sql = "select target_username from follow where username = '$username'
        order by id desc $extra";
    
        $result = $this->conn->query($sql);

        if($result) {

            $follow = array();

            while($row = $result->fetch_object()) {

                $follow[] = array('username' => $row->target_username,
                        'name' => $this->util->getFullname($row->target_username),
                        'isVerify'=> $this->util->getIsVerify($row->target_username),
                        'info'=> $this->util->getAcadInfo($row->target_username),
                        'isFollow' =>$this->util->isFollow($username,$row->target_username)
                );

            }
            $result->close();
            return $follow;
        }

        return OPERATION_FAILED;
    }
    
    
    
    
    public function loadFollowers($username, $target_username, $follow_pos) {

        $extra = "limit $follow_pos,10";
     
        $sql = "select username from follow where target_username = '$username'
        order by id desc $extra";
    
        $result = $this->conn->query($sql);

        if($result) {

            $follow = array();

            while($row = $result->fetch_object()) {

                $follow[] = array( 'username' => $row->username,
                        'name' => $this->util->getFullname($row->username),
                        'isVerify'=> $this->util->getIsVerify($row->username),
                        'info'=> $this->util->getAcadInfo($row->username),
                        'isFollow' =>$this->util->isFollow($username,$row->username)
                );

            }
            $result->close();
            return $follow;
        }

        return OPERATION_FAILED;
    }
    
    



}