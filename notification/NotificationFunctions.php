<?php
	
class NotificationFunctions {
    
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
    
    
    
   public function loadNotification($username, $notify_pos) {
        
        $extra = "limit $notify_pos,5";
        
        $sql = "select id, notify_type, notify_data, notify_msg, isSeen, stamp
                from notifications where username = '$username' order by id desc $extra";

        $result = $this->conn->query($sql);
        if($result) {

           $notify = array();
            while($row = $result->fetch_object()) {
              
                $notify[] = array( 	'id' => $row->id,                                    
                        'type' => $row->notify_type,
                        'data' => $row->notify_data,
                        'msg' => $row->notify_msg,
                        'isSeen' => $row->isSeen,
                        'timeStamp' => $this->util->time_ago($row->stamp)
                );
            }
            $result->close();
            return $notify;
        }
        return OPERATION_FAILED;
   }
    
    
    public function seenNotification($username, $notifyId) {
        
        if($this->isNotifier($username, $notifyId)) {
            
            $sql = $this->conn->prepare("update notifications set isSeen = 'TRUE' where id = $notifyId");
            $result = $sql->execute();
            $sql->close();
            if($result) {               
              return OPERATION_SUCCESSFULL; 
            }
        }
        return OPERATION_FAILED; 
    }
      
      
   private function isNotifier($username, $notifyId) { 
       
        $sql = "select username from notifications where id = $notifyId";
        $result = $this->conn->query($sql);
        if($result) { 
            
             while($row = $result->fetch_object()) {
                 if(strcmp($username,$row->username)==0)
                    return true;
             }
        }
        return false;
   }
   
       
   public function notifyMention($username,$feedId,$mentions) {
        
         $uname_string = implode(',', $mentions);                  
         $fullname = $this->util->getFullname($username);            
            
         $data = $username.":";
         $data = $data.$feedId;
         $data = $data.":";
         $data = $data.$fullname;
                
         $msg = $fullname;
         $msg = $msg." mentioned you in a post";
            
         $extra = explode(',', $uname_string);
         foreach ($extra as $value) {
                $user = trim($value,'\'');
                if(strcmp($username,$value)!=0)           
                   $this->addNotification($user,NOTIFICATION_MENTION,$data,$msg);
         }
         return OPERATION_SUCCESSFULL;         
  }
    
    
   
  public function notifyLike($username,$feedId) {
        
         $extra =  "where id = $feedId";

	     $sql = "select username from feeds $extra";
         $result = $this->conn->query($sql);      
         if($result) {
             
            $fullname = $this->util->getFullname($username);            
            
            $data = $username.":";
            $data = $data.$feedId;
            $data = $data.":";
            $data = $data.$fullname;
            
            $msg = $fullname;
            $msg = $msg." likes your post";
            
            while($row = $result->fetch_object()) {   
                 if(strcmp($username,$row->username)!=0)           
                    $this->addNotification($row->username,NOTIFICATION_LIKE,$data,$msg);
            }
            return OPERATION_SUCCESSFULL;
         }
         return OPERATION_FAILED;
    }
    


   public function notifyComment($username,$feedId) {
        
         $extra =  "where id = $feedId";

	     $sql = "select username from feeds $extra";
         $result = $this->conn->query($sql);
         if($result) {

            $fullname = $this->util->getFullname($username);
            
            $data = $username.":";
            $data = $data.$feedId;
            $data = $data.":";
            $data = $data.$fullname;
            
            $msg = $fullname;
            $msg = $msg." commented on your post";
            
            while($row = $result->fetch_object()) {   
                if(strcmp($username,$row->username)!=0)         
                   $this->addNotification($row->username,NOTIFICATION_COMMENT,$data,$msg);
            }
            return OPERATION_SUCCESSFULL;
         }
         return OPERATION_FAILED;
    }
    
        
    
   public function notifyFollow($username,$target_username) {
        
        $fullname = $this->util->getFullname($username);
        
        $data = $username.":";
        $data = $data.$fullname;
        
        $msg = $fullname;
        $msg = $msg." is now following you";
        
        return $this->addNotification($target_username,NOTIFICATION_FOLLOW,$data,$msg);	     
    }
    
    
    
    
   private function addNotification($username,$notify_type,$notify_data,$notify_msg) {
      
            $sql = $this->conn->prepare("insert into notifications (username, notify_type, notify_data, notify_msg) 
                    values ('$username', $notify_type, '$notify_data', '$notify_msg')");

            $result = $sql->execute();
            $sql->close();
            if($result) {
                $gcmToken = $this->util->getGcmToken($username);
                $this->notifyGCM($gcmToken, $notify_type, 'New notification', $notify_msg);
                return OPERATION_SUCCESSFULL;
            }
            return OPERATION_FAILED;
    }
    
    
    
    private function notifyGCM($gcmToken,  $notify_type, $msg_title, $msg_body) {
       
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
 
       
         $message = array(
                 'type'=> $notify_type,
                 'title'=> $msg_title,
                 'body'=> $msg_body
                );
                
         $gcm_regids = array($gcmToken);
        
          
        $fields = array(
            'data' => $message,
            'registration_ids' => $gcm_regids,
        );
 
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result == FALSE) {
           // die('Curl failed: ' . curl_error($ch));
        }

        // Close connection
        curl_close($ch);
       // echo $result;
    }

    
    
    
    
}