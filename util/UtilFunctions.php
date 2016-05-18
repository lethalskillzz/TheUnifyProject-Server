<?php

class UtilFunctions {

    private $conn;

    // constructor
    function __construct() {
        require_once (__DIR__ .  '/../DbConnect.php');
        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct() {
    }


    public function isActiveSession($username,$sessionId) {
        
         $sql = "select sessionId from users where username = '$username'";
         $result = $this->conn->query($sql);
         if($result) {
             
              while($row = $result->fetch_object()) {
                  if(strcmp($row->sessionId,$sessionId)==0) {
                      return TRUE;
                  }
              }
         }
       return FALSE;
    }


    public function loadUsers($type, $username) {

        switch($type) {

            case USER_FOLLOWING:{

                $sql = "select target_username from follow where username='$username'";
                $result = $this->conn->query($sql);
                if($result) {

                    $users = array();
                    while($row = $result->fetch_object()){
                        $uname_str = "'".$row->target_username;
                        array_push($users, $uname_str."'");
                    }
                    $result->close();
                    return $users;
                }
                
            }
            break;
            

            case USER_FOLLOWERS:{

                $sql = "select username from follow where target_username='$username'";
                $result = $this->conn->query($sql);
                if($result) {

                    $users = array();

                    while($row = $result->fetch_object()){
                        $uname_str = "'".$row->username;
                        array_push($users, $uname_str."'");
                    }
                    $result->close();
                    return $users;
                }               
            }
            break;     
        }
        return null;
    }



    public function getFullname($username) {

        $sql = $this->conn->prepare("select fullname from users where username = '$username'");  
        $result = $sql->execute();
        if($result) {
            
            $name=null;
            $sql->bind_result($name);
            $sql->store_result();

            $sql->fetch();
            $sql->close();
        }
        return $name;
    }
    
    
    public function getMobile($username) {

        $sql = $this->conn->prepare("select mobile from users where username = '$username'");  
        $result = $sql->execute();
        if($result) {
            
            $mobile=null;
            $sql->bind_result($mobile);
            $sql->store_result();

            $sql->fetch();
            $sql->close();
        }
        return $mobile;
    }
    

    public function getIsVerify($username) {

        $sql = $this->conn->prepare("select isVerify from users where username = '$username'");  
        $result = $sql->execute();
        if($result) {

            $isVerify=false;
            $sql->bind_result($isVerify);
            $sql->store_result();
            $sql->fetch();
            $sql->close();
        }
        return $isVerify;
    }



    public function getLocation($username) {

        $sql = "select location from users where username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {

            $location=null;
            while($row = $result->fetch_object()) {
                 $location = $row->location;
            }
            return $location;
        }
        return null;
    }



    public function getLevel($username) {

        $sql = "select level from users where username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {

            $level=null;
            while($row = $result->fetch_object()) {
                 $level = $row->level;
            }
            return $level;
        }
        return null;
    }



    public function getCourse($username) {

        $sql = "select course from users where username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {

            $course='';
            while($row = $result->fetch_object()) {
                 $course = $row->course;
            }
            return $course;
        }
        return null;
    }



    public function getAcadInfo($username) {

        $acadInfo = $this->getCourse($username)." (";
        $acadInfo = $acadInfo.$this->getLevel($username);
        $acadInfo = $acadInfo.") ";

        return $acadInfo;
    }
    
    
    
    public function getStoreName($username) {

        $sql = $this->conn->prepare("select storeName from users where username = '$username'");  
        $result = $sql->execute();
        if($result) {
            
            $storeName=null;
            $sql->bind_result($storeName);
            $sql->store_result();

            $sql->fetch();
            $sql->close();
        }
        return $storeName;
    }
    
    
   public function getGcmToken($username) {

        $sql = "select gcmToken from users where username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {

            $gcmToken='';
            while($row = $result->fetch_object()) {
                 $gcmToken = $row->gcmToken;
            }
            return $gcmToken;
        }
        return null;
    }


   public function isLike($username, $feedId) {

        $sql = "select * from likes where username = '$username' and feedId = $feedId";
        $result = $this->conn->query($sql);
        if($result) {
            
            $row = $result->fetch_object();
            if(count($row)) {
                return TRUE;  
            }

        }
        return FALSE;
    }


    public function isFollow($username, $target_username) {

        $sql = "select * from follow where target_username = '$target_username' and username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {
            
            $row = $result->fetch_object();
            if(count($row)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    


    public function followCount($username, $type) {

        $result = null;
        $sql  = null;
        
        if($type=='following') {
            
           $sql = $this->conn->prepare("select * from follow where username = '$username'");  
           $result = $sql->execute();

        }else if($type=='followers') {
            
           $sql = $this->conn->prepare("select * from follow where target_username = '$username'");  
           $result = $sql->execute();
        }

        if($result) {

            $sql->store_result();
            $row = $sql->num_rows;
            $sql->close();

            return number_format($row);
        }
        return null;
    }

   
 
   
   public function likeCount($feedId) {
                   
        $sql = $this->conn->prepare("select * from likes where feedId = $feedId");  
        $result = $sql->execute();
        if($result!=null) {

            $sql->store_result();
            $row = $sql->num_rows;
            $sql->close();

            return number_format($row);
        }
        return null;        
   }
   
   
   public function commentCount($feedId) {
       
        $sql = $this->conn->prepare("select * from comments where feedId = $feedId");  
        $result = $sql->execute();
        if($result) {
            
            $sql->store_result();
            $row = $sql->num_rows;
            $sql->close();

            return number_format($row);
        }
        return null;
   } 
    
    

  public function time_ago( $date ) {
      
        if( empty( $date ) ) {
            return "No date provided";
        }
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");

        $lengths = array("60","60","24","7","4.35","12","10");
        $now = time();
        $unix_date = strtotime( $date );
        // check validity of date
        if( empty( $unix_date ) ) {
            return "Bad date";
        }
        // is it future date or past date
        if( $now > $unix_date ) {
            $difference = $now - $unix_date;
            $tense = "ago";
        }
        else {
            $difference = $unix_date - $now;
            $tense = "from now";
        }
        for( $j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++ ) {
            $difference /= $lengths[$j];
        }
        $difference = round( $difference );
        if( $difference != 1 ) {
            $periods[$j].= "s";
        }
        return "$difference $periods[$j] {$tense}";
    }


    public function getMentions($string) {
          preg_match_all('/@(\w+)/',$string,$matches);
          $keywords = array(); 
          $i = 0;
                  
          foreach ($matches[1] as $match) {
                   $count = count($matches[1]);
                   $dmatch = "'".$match;
                   array_push($keywords, $dmatch."'");
                   $i++;
          } 
          return $keywords;
   }

        
    public function getHashtags($string) {
          preg_match_all('/#(\w+)/',$string,$matches);
          $keywords = array(); 
          $i = 0;
                  
          foreach ($matches[1] as $match) {
                   $count = count($matches[1]);
                   $dmatch = "'".$match;
                   array_push($keywords, $dmatch."'");
                   $i++;
         } 
         return $keywords;
   }

                

}