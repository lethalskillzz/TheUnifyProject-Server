<?php
	
class ProfileFunctions {
    
    private $conn;
    private $util;
 
    // constructor
    function __construct() {
		
        require_once (__DIR__ .  '/../DbConnect.php');
        include_once (__DIR__ .  '/../util/UtilFunctions.php');
		 
        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();
        
        $this->util = new UtilFunctions();

    }
 
    // destructor
    function __destruct() {        
    }
    
   
    
    public function loadProfileDetail($username,$target_username) {

        $sql = "select fullname, mobile, location, hasStore from users
        where username = '$target_username'";

        $result = $this->conn->query($sql);
        if($result) {

            $detail = array();
            while($row = $result->fetch_object()) {

                $detail[] = array( 'name' => $row->fullname,
                        'mobile' => $row->mobile,
                        'isVerify'=> $this->util->getIsVerify($target_username),
                        'acadInfo' =>  $this->util->getAcadInfo($target_username),
                        'location' => $row->location,
                        'hasStore' => $row->hasStore,
                        'following' =>  $this->util->followCount($target_username,'following'),
                        'followers' =>  $this->util->followCount($target_username,'followers'),
                        'isFollow' =>  $this->util->isFollow($username,$target_username)
                );
            }
            $result->close();
            return $detail;
        }
        
        return OPERATION_FAILED;
    }
	   
    
    
    public function changeProfilePic($username, $profilePic) {
        
          if($profilePic!=null) {
           
            $img_file = $username.".png";
            $target_dir = PROFILE_PIC_DIR.$img_file;
            $save_img = str_replace('data:image/jpg;base64,', '', $profilePic);

            if(!file_put_contents($target_dir, base64_decode($save_img))) {
             
              return OPERATION_FAILED;
            }
            
         } else {
            $this->deleteProfileImg($username);
         }
           
        return OPERATION_SUCCESSFULL; 
    }
    
    
    
    private function deleteProfileImg($username) {
            
         $img_file = $username.".png";
         unlink('pic\\'.$img_file);
           
         return OPERATION_SUCCESSFULL;       
    }
    
    
    public function editProfile($username, $name, $location, $course, $level) {
                
            $sql = $this->conn->prepare("update users set fullname = '".$this->conn->real_escape_string($name)."', location = '$location',
                                      course = '$course', level = '$level' where username = '$username'");
                                      
            $result = $sql->execute();
            $sql->close();

            if($result) {               
              return $this->fetchProfileDetail($username);
            }
            return OPERATION_FAILED; 
    }
	
    
    
   private function fetchProfileDetail($username) {
            
           $ssid ='';
           $mobile ='';
           $name='';
           $location='';
           $course='';
           $level='';

           $sql = $this->conn->prepare("select sessionId, mobile, fullname, location, course, level from users
			        where username = '".$this->conn->real_escape_string($username)."'");
                    
           $result = $sql->execute();
             
           if($result) {
              //$user = $sql->get_result()->fetch_assoc();
              $sql->bind_result($ssid,$mobile,$name,$location,$course,$level);
              $sql->store_result();
 
              if($sql->num_rows > 0) {
                 
                 $sql->fetch();
               
                 $user = array();
                 $user["sessionId"] = $ssid;
                 $user["name"] = $name;
                 $user["username"] = $username;
                 $user["mobile"] = $mobile;
                 $user["location"] = $location;
                 $user["course"] = $course;
                 $user["level"] = $level;
                      
                 $sql->close();
                 
                 return $user;
              }
          } 
          return OPERATION_FAILED;
    }
    
    
    
}