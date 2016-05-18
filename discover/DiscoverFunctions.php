<?php


class DiscoverFunctions {

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


    public function loadDiscover($username, $discover_pos) {
        
        $userDiscover = $this->loadUserDiscover($username,$discover_pos);
        $hashtagDiscover = $this->loadHashtagDiscover();
        $feedDiscover = $this->loadFeedDiscover($username, $discover_pos/*,$hashtagDiscover*/);

        return array( 'user' => $userDiscover,
              'feed' => $feedDiscover,
              'hashtag' => $hashtagDiscover ); 
      
    }



    private function loadUserDiscover($username, $discover_pos) {
        
       if($discover_pos>5)
          $discover_pos = $discover_pos - 5;
        $extra = "limit $discover_pos,5";
        //$extra1 = "and where course =";
        
        $loadUsers = $this->util->loadUsers(USER_FOLLOWING, $username);
        if(count($loadUsers)) {
          $uname_string = $loadUsers;
          $uname_string = implode(',', $uname_string);
          $extra1 = "where username not in ($uname_string)";
        }else {
          $extra1 = "";
        }


        $sql = "select username, fullname, isVerify from users               
        $extra1 order by id asc $extra";

        $result = $this->conn->query($sql);
        if($result) {

            $user = array();
            while($row = $result->fetch_object()) {

                if(strcmp($username,$row->username)!=0) {

                    if(!$this->util->isFollow($username,$row->username)) {

                        $user[] = array( 'type' => 1,                              
                                'username' => $row->username,
                                'name' => $row->fullname,
                                'isVerify'=> $row->isVerify,
                                'info'=> $this->util->getAcadInfo($row->username),
                                'isFollow' => $this->util->isFollow($username,$row->username)
                        );
                    }
                }
            }
            $result->close();
            return $user;
        }
        return array();  
    }



    private function loadFeedDiscover($username, $discover_pos/*,$hashtag*/) {
        
       if($discover_pos>7)
          $discover_pos = $discover_pos - 7;
        $extra = "limit $discover_pos,3";
        //$extra1 = "where feedMsg like '%$hashtag%'";
        
        $sql = "select id, username, feedMsg, feedImg, stamp from feeds order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {
            
            $feeds = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->feedImg)>0) 
                   $image = FEED_IMG_URL.$row->feedImg;

                $feeds[] = array(  'type' => 2,
                        'id' => $row->id,
                        'username' => $row->username,
                        'name' => $this->util->getFullname($row->username),
                        'isVerify'=> $this->util->getIsVerify($row->username),
                        'message' => $row->feedMsg,
                        'image' => $image,
                        'timeStamp' => $this->util->time_ago($row->stamp),
                        'likeCount' => $this->util->likeCount($row->id),
                        'commentCount' => $this->util->commentCount($row->id),
                        'isLike' => $this->util->isLike($username,$row->id)
                );
            }

            $result->close();
            return $feeds;
        }
        return array();
    }


    private function loadHashtagDiscover() {

        $extra = "limit 0,2";

        $sql = "select hash, count from hashtags order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $hashtags = array();
            while($row = $result->fetch_object()) {
                  $hashtags[] = array(  'type' => 3,
                            'hash' => $row->hash,
                            'count' => $row->count
                  );

            }
            $result->close();
            return $hashtags;
        }
        return array();
    }
    
    
    
   public function loadIntroDiscover($username, $phone, $discover_pos) {
        
        $extra = "limit $discover_pos,15";
        
        if(count($phone)) {
        $phone_string = $phone; 
        }else {
          $phone_string = array();
        }       

        $phone_string = implode(',', $phone_string);
      
        $level = $this->util->getLevel($username);
        $course = $this->util->getCourse($username);
        $location = $this->util->getLocation($username);
        
        //$extra1 = "where mobile in ($phone_string) or level = '$level' or course = '$course' or location = '$location'";
        $extra1 = "";
         
        $sql = "select username, fullname, isVerify from users $extra1 order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $user = array();
            while($row = $result->fetch_object()) {

                if(strcmp($username,$row->username)!=0) {

                    if(!$this->util->isFollow($username,$row->username)) {
                        
                        $user[] = array( 'username' => $row->username,
                                'name' => $row->fullname,
                                'isVerify'=> $row->isVerify,
                                'info'=> $this->util->getAcadInfo($row->username),
                                'isFollow' => $this->util->isFollow($username,$row->username)
                        );
                    }
                }
            }
            $result->close();
            return $user;
        }
        return OPERATION_FAILED;
    }




    public function followIntroDiscover($username, $user) {
        
           foreach($user as $target_username) {   
                  
               $sql = $this->conn->prepare("insert into follow (username, target_username) 
                       values ( '$username', '$target_username' )");

               $result = $sql->execute();
               $sql->close();

               if($result) {
                  $this->notify->notifyFollow($username,$target_username);              
               }
           }
           return OPERATION_SUCCESSFULL; 
    }
        


}