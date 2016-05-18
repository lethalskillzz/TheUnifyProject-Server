
<?php

class FeedFunctions {

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



    private function feedCount($username) {

        $sql = $this->conn->prepare("select id from feeds where username = '$username'");  
        $result = $sql->execute();
        if($result) {
            
            $count = $sql->num_rows;
            $sql->close();

            return $count;
        }
        return OPERATION_FAILED;
    }


    private function isPoster($username, $feedId) {
        
        $sql = "select username from feeds where id = $feedId";
        $result = $this->conn->query($sql);
        if($result) { 
            
             while($row = $result->fetch_object()) {
                 if(strcmp($username,$row->username)==0)
                    return true;
             }
        }
        return false;
    }
    
    
    
    private function deleteAllLike($feedId) {
        
          $sql = $this->conn->prepare("delete from likes where feedId=$feedId");
          $result = $sql->execute();
          $sql->close();
          if($result) { 
            return OPERATION_SUCCESSFULL;
          }  
        return OPERATION_FAILED;
    }
        
    
    private function deleteAllComment($feedId) {
        
          $sql = $this->conn->prepare("delete from comments
                  where feedId=$feedId");

          $result = $sql->execute();
          $sql->close();
          if($result) { 
            return OPERATION_SUCCESSFULL;
          }
        
        return OPERATION_FAILED;
    }     
   
    
   private function deleteFeedImg($feedId) {

        $img = $this->getFeedImg($feedId);
        if($img!=OPERATION_FAILED) {
           unlink('img\\'.$img);
           return OPERATION_SUCCESSFULL; 
         }
         return OPERATION_FAILED; 
    }
    
    
    private function getFeedImg($feedId) {

        $sql = "select feedImg from feeds where id = $feedId";      
        $result = $this->conn->query($sql);
        if($result) {
            
            while($row = $result->fetch_object()) {
                if(strlen($row->feedImg)>0) {
                    return $row->feedImg; 
                }
            }
        }
        return OPERATION_FAILED; 
    }    
    

    public function deleteFeed($username, $feedId) {

        if($this->isPoster($username, $feedId)) {
            
          $this->deleteFeedImg($feedId);
          
          $sql = $this->conn->prepare("delete from feeds where id = $feedId");
          $result = $sql->execute();
          $sql->close();
          if($result) { 
              
            $this->deleteAllLike($feedId);
            $this->deleteAllComment($feedId);
            return OPERATION_SUCCESSFULL;
          }
        }
        return OPERATION_FAILED;
    }
    

    public function editFeed($username, $feedId, $feed_msg, $feed_img) {
        
        if($this->isPoster($username, $feedId)) {
            
           $img_file = $this->getFeedImg($feedId);          

           if($feed_img!=null) {
               
               if($img_file == OPERATION_FAILED) {
                  $img_file = "";
                  $fid = substr(uniqid("", true), 0, 12);
                  $img_file = $username."_";
                  $img_file = $img_file.$fid;
                  $img_file = $img_file.".png";
               }
               
               $target_dir = FEED_IMG_DIR.$img_file;
               $save_img = str_replace('data:image/jpg;base64,', '', $feed_img);

              if(!file_put_contents($target_dir, base64_decode($save_img))) {
              return OPERATION_FAILED;
              }
          }
      }
      
      $sql = $this->conn->prepare("update feeds set feedMsg = '".$this->conn->real_escape_string($feed_msg). "', 
                                  feedImg = '$img_file' where id = $feedId");
                   
      $result = $sql->execute();
      $sql->close();

     if($result) {
        return OPERATION_SUCCESSFULL;
      }      
      return OPERATION_FAILED;
    }
    
   
    public function postFeed($username, $feedMsg, $feedImg) {

        $img_file = NULL;
           
       /* if($this->feedCount($username)>=15) */

        if($feedImg!=null) {

            $fid = substr(uniqid("", true), 0, 12);
            $img_file = $username."_";
            $img_file = $img_file.$fid;
            $img_file = $img_file.".png";

            $target_dir = FEED_IMG_DIR.$img_file;
            $save_img = str_replace('data:image/jpg;base64,', '', $feedImg);

            if(!file_put_contents($target_dir, base64_decode($save_img))) {
            return OPERATION_FAILED;
            }
         }


         $sql = $this->conn->prepare("insert into feeds (username, feedMsg, feedImg) 
                    values ( '$username', '".$this->conn->real_escape_string($feedMsg). "', '$img_file')");

         $result = $sql->execute();
         $sql->close();

         if($result) {
            $insertId = $this->conn->insert_id;              
                
            $mentions = $this->util->getMentions($feedMsg); 
            if(count($mentions)) {
               $this->notify->notifyMention($username, $insertId, $mentions);
            }
                
            $hashtags = $this->util->getHashtags($feedMsg); 
            if(count($hashtags)) {
               $this->addHash($hashtags);
            }        
                
          } else 
                return OPERATION_FAILED;
       // }

        return OPERATION_SUCCESSFULL;
    }


        
    private function addHash($hashtags) {
        
        $hash_string = implode(',', $hashtags);                 
        $extra = explode(',', $hash_string);
        foreach ($extra as $value) {
            
          $split = explode('\'', $value);                
          $hash = '#'.$split[1]; 
          
          $sql = "select hash, count from hashtags where hash = '$hash' order by id asc";
          $result = $this->conn->query($sql);
          if($result) {
              
             $row = $result->fetch_object();
             if(count($row)) {
                 $count = $row->count + 1;
                 if(!$this->conn->query("update hashtags SET count = $count
                                     WHERE hash = '$hash'")) return OPERATION_FAILED;
             }else {
                 $count = 1;
                 if(!$this->conn->query("insert into hashtags (hash, count) 
			                         values ('$hash',$count)")) return OPERATION_FAILED;
             }
             
          } else 
               return OPERATION_FAILED;
        } 
        return OPERATION_SUCCESSFULL;
    }
        
    

    /**
     * Fetch feeds posted depending on the feed_type  
     * @param feed_type
     * @param username
     * @param feed_pos
     * returns feeds
     */
    public function loadFeed($username, $feed_pos) {

        $extra = "limit $feed_pos,5";
        $usermention = "@".$username;

        $loadUsers = $this->util->loadUsers(USER_FOLLOWING, $username);
        if(count($loadUsers)) {
        $uname_string = $loadUsers; //array_keys($loadUsers);
        }else {
          $uname_string = array();
        }       

        $uname = "'".$username;
        $uname_string[] = $uname."'";
        $uname_string = implode(',', $uname_string);

        $sql = "select id, username, feedMsg, feedImg, stamp from feeds
        where username in ($uname_string) or feedMsg like '%$usermention%'
        order by id desc $extra";

        $result = $this->conn->query($sql);
        if($result) {

            $feeds = array();
            while($row = $result->fetch_object()) {
              
                $image = "";
                if(strlen($row->feedImg)>0)
                   $image = FEED_IMG_URL.$row->feedImg;
              
                $feeds[] = array( 'id' => $row->id,
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
        return OPERATION_FAILED;
    }
    
    
    
    
    /**
     * Fetch feeds posted depending on the feed type  
     * @param feed_type
     * @param username
     * @param feed_pos
     * returns feeds
     */
    public function loadProfileFeed($username, $target_username, $feed_pos) {

        $extra = "limit $feed_pos,5";
        $usermention = "@".$target_username;
        

        $sql = "select id, username, feedMsg, feedImg, stamp from feeds
        where username = '$target_username' or feedMsg like '%$usermention%'
        order by id desc $extra";

        $result = $this->conn->query($sql);   
        if($result) {

            $feeds = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->feedImg)>0) 
                    $image = FEED_IMG_URL.$row->feedImg;
              
                $feeds[] = array( 	'id' => $row->id,
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
        return OPERATION_FAILED;
    }
    
    
    
    public function displayFeed($username, $feedId) {
        
        $sql = "select id, username, feedMsg, feedImg, stamp from feeds where id = '$feedId' ";
        $result = $this->conn->query($sql);
        if($result) {

            $feeds = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->feedImg)>0) 
                    $image = FEED_IMG_URL.$row->feedImg;
              
                $feeds[] = array( 'id' => $row->id,
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
        return OPERATION_FAILED;
    }
    
    
    public function loadHash($username, $hash, $feed_pos) {

        $extra = "limit $feed_pos,5";
        
        $sql = "select id, username, feedMsg, feedImg, stamp from feeds
        where feedMsg like '%$hash%' order by id desc $extra";

        $result = $this->conn->query($sql); 
        if($result) {

        $feeds = array();
        while($row = $result->fetch_object()) {
                 
                 $image = "";
                 if(strlen($row->feedImg)>0) 
                    $image = FEED_IMG_URL.$row->feedImg;
                    
                 $feeds[] = array( 	'id' => $row->id,
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
        return OPERATION_FAILED;
    }
    
    
    
    public function likeFeed($username, $feedId) {

        if(!$this->util->isLike($username,$feedId)) {
            $sql = $this->conn->prepare("insert into likes (feedId, username) 
                    values ( $feedId, '$username' )");

            $result = $sql->execute();
            $sql->close();
            if($result) {
              $this->notify->notifyLike($username,$feedId); 
              return OPERATION_SUCCESSFULL;
            }
        }
        return OPERATION_FAILED;
    }


 


    public function unlikeFeed($username, $feedId) {

       if($this->util->isLike($username,$feedId)) {
           
          $sql = $this->conn->prepare("delete from likes where feedId = $feedId and username = '$username'");
          $result = $sql->execute();
          $sql->close();
          if($result) {
             return OPERATION_SUCCESSFULL;
          }
       }
        return OPERATION_FAILED;
    }

    
    
   public function postComment($username, $feedId, $comment) {
    
       if($comment!=null) {
          $sql = $this->conn->prepare("insert into comments (feedId, username, comment) 
                    values ( $feedId, '$username', '".$this->conn->real_escape_string($comment)."')");

         $result = $sql->execute();
         $sql->close();
        if($result) {
            
          $this->notify->notifyComment($username,$feedId); 
          return OPERATION_SUCCESSFULL;           
        }
      }
      return OPERATION_FAILED; 
   }
 

    
   public function loadComment($feedId, $comment_pos) {
       
       $extra = "limit $comment_pos,10";
       
       $sql = "select id, username, comment, stamp from comments where feedId = $feedId order by id desc $extra";
       $result = $this->conn->query($sql);        
       if($result) {

          $comments = array();
          while($row = $result->fetch_object()) {
                
                  $comments[] = array( 'id' => $row->id,
                        'username' => $row->username,
                        'name' => $this->util->getFullname($row->username),
                        'isVerify'=> $this->util->getIsVerify($row->username),
                        'comment' => $row->comment,                       
                        'timeStamp' => $this->util->time_ago($row->stamp)
                );
                
          }         
        $result->close();
        return $comments;
        }
        return OPERATION_FAILED;
   }




   public function loadLike($username, $feedId, $list_pos) {
       
        $extra = "limit $list_pos,10";
                
        $sql = "select username from likes where feedId = '$feedId' order by id desc $extra";
        $result = $this->conn->query($sql);        
        if($result) {

            $users = array();
            while($row = $result->fetch_object()) {

                  $users[] = array('username' => $row->username,
                        'name' => $this->util->getFullname($row->username),
                        'isVerify'=> $this->util->getIsVerify($row->username),
                        'info'=> $this->util->getAcadInfo($row->username),                       
                        'isFollow' => $this->util->isFollow($username,$row->username)
                );
                
            }      
            $result->close();
            return $users;
        }
        return OPERATION_FAILED;
   }
   
       
   


}