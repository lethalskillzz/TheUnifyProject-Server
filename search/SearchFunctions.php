
<?php


class SearchFunctions {

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


    public function loadSearch($username, $searchQuery, $search_pos) {

        $userSearch = $this->searchUser($username, $searchQuery, $search_pos);
        $feedSearch = $this->searchFeed($username, $searchQuery, $search_pos);
        $hashtagSearch = $this->searchHashtag($searchQuery, $search_pos);


        return array('user' => $userSearch,
                'feed' => $feedSearch,
                'hashtag' => $hashtagSearch
        );

    }



    private function searchUser($username, $searchQuery, $search_pos) {

        if($search_pos>5)
            $search_pos = $search_pos - 5;
        $extra = "limit $search_pos,5";
        //$extra1 = "and where course =";
        $query = "where fullname or username like '%$searchQuery%'";

        $sql = "select username, fullname, isVerify from users $query order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $users = array();
            while($row = $result->fetch_object()) {

                if(strcmp($username,$row->username)!=0) {

                        $users[] = array( 'type' => 1,
                                'username' => $row->username,
                                'name' => $row->fullname,
                                'isVerify'=> $row->isVerify,
                                'info'=> $this->util->getAcadInfo($row->username),
                                'isFollow' => $this->util->isFollow($username,$row->username)
                        );
                }
            }
            $result->close();
            return $users;
        }
        return array();
    }




    private function searchFeed($username, $searchQuery, $search_pos) {

        if($search_pos>7)
           $search_pos = $search_pos - 7;
        $extra = "limit $search_pos,3";
        $query = "where feedMsg like '%$searchQuery%'";

        $sql = "select id, username, feedMsg, feedImg, stamp from feeds $query order by id asc $extra";
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
    

    private function searchHashtag($searchQuery, $search_pos) {

        $extra = "limit $search_pos,2";
        $query = "where hash like '%$searchQuery%'";

        $sql = "select hash, count from hashtags $query order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $hashtags = array();
            while($row = $result->fetch_object()) {
                $hashtags[] = array( 'type' => 3,
                        'hash' => $row->hash,
                        'count' => number_format($row->count)
                );
            }
            $result->close();
            return $hashtags;
        }

        return array();
    }
      
    
    public function loadRepoSearch($searchQuery, $search_pos) {
        
        $extra = "limit $search_pos,6";
        $query = "where title like '%$searchQuery%'";
        
        $sql = "select id, title, image, url from repository $query order by id desc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $repo = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->image)>0) 
                    $image = REPO_IMG_URL.$row->image;
              
              
                $repo[] = array( 'type' => 1,
                         'id' => $row->id,
                         'title' => $row->title,
                         'image' => $image,
                         'url' => $row->url
                );
            }
            $result->close();
            return $repo;
        }
        return OPERATION_FAILED;
    }

    
    public function loadDigestSearch($searchQuery, $search_pos) {
        
        $extra = "limit $search_pos,6";
        $query = "where title like '%$searchQuery%'";
        
        $sql = "select id, category, title, image, url from digest $query order by id desc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $digest = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->image)>0) 
                    $image = DIGEST_IMG_URL.$row->image;
              
              
                $digest[] = array( 'type' => 2,
                         'id' => $row->id,
                         'category' => $row->category,
                         'title' => $row->title,
                         'image' => $image,
                         'url' => $row->url
                );
            }
            $result->close();
            return $digest;
        }
        return OPERATION_FAILED;
    }

    
    public function loadShopSearch($searchQuery, $search_pos) {
        
        $extra = "limit $search_pos,6";
        $query = "where title like '%$searchQuery%'";
        
        $sql = "select id, username, price, title, image from shopping
                $query order by id desc $extra";

        $result = $this->conn->query($sql);
        if($result) {

            $shop = array();
            while($row = $result->fetch_object()) {
              
                $image = "";
                if(strlen($row->image)>0) 
                    $image = SHOP_IMG_URL.$row->image;
              
              
                $shop[] = array( 'type' => 3,
                         'id' => $row->id,
                         'username' => $row->username,
                         'price' => number_format($row->price, 2),
                         'title' => $row->title,
                         'image' => $image
                );
            }
            $result->close();
            return $shop;
        }
        return OPERATION_FAILED;
    }
    
    
    public function loadStoreSearch($username, $searchQuery, $search_pos) {
        
        $extra = "limit $search_pos,6";
        $query = "where username = '$username' and title like '%$searchQuery%'";
        
        $sql = "select id, price, title, image from shopping
                $query order by id desc $extra";

        $result = $this->conn->query($sql);
        if($result) {

            $shop = array();
            while($row = $result->fetch_object()) {
              
                $image = "";
                if(strlen($row->image)>0) 
                    $image = SHOP_IMG_URL.$row->image;
              
                $shop[] = array( 'type' => 4,
                         'id' => $row->id,
                         'price' => number_format($row->price, 2),
                         'title' => $row->title,
                         'image' => $image
                );
            }
            $result->close();
            return $shop;
        }
        return OPERATION_FAILED;
    }
    

    public function loadContactSearch($username, $phone, $contact_pos) {
        
        $extra = "limit $contact_pos,15";
        
        if(count($phone)) {
           $phone_string = $phone; 
        }else {
           $phone_string = array();
        }       

        $phone_string = implode(',', $phone_string);
        $level = $this->util->getLevel($username);
        $course = $this->util->getCourse($username);
        $location = $this->util->getLocation($username);
        
        $extra1 = "where mobile in ($phone_string)";
        
        $sql = "select username, fullname, isVerify from users $extra1 order by id asc $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $user = array();
            while($row = $result->fetch_object()) {

                if(strcmp($username,$row->username)!=0) {

                    //if(!$this->util->isFollow($username,$row->username)) {

                        $user[] = array('username' => $row->username,
                                'name' => $row->fullname,
                                'isVerify'=> $row->isVerify,
                                'info'=> $this->util->getAcadInfo($row->username),
                                'isFollow' => $this->util->isFollow($username,$row->username)
                        );
                    //}
                }
            }
            $result->close();
            return $user;
        }
        return OPERATION_FAILED;       
    }
        
    
    public function loadMentionPopup($query) {
      
       $query = "where username like '%$query%'";
       $extra = "limit 0,10";
       
       $sql = "select fullname, username from users $query order by id asc $extra";
       $result = $this->conn->query($sql);
       if($result) {

          $tag = array();
          while($row = $result->fetch_object()) {
                $tag[] = array( 'type' => 1,
                        'title' => $row->fullname,
                        'subtitle' => $row->username
                );
         }
         $result->close();
         return $tag;
      }
      return OPERATION_FAILED;
   }
   
       
   public function loadHashPopup($query) {
       
       $query = "where hash like '%$query%'";
       $extra = "limit 0,10";
       
       $sql = "select hash, count from hashtags $query order by id asc $extra";
       $result = $this->conn->query($sql);
       if($result) {

          $tag = array();
          while($row = $result->fetch_object()) {
                $tag[] = array(  'type' => 2,
                        'title' => $row->hash,
                        'subtitle' => number_format($row->count)
                );
         }
         $result->close();
         return $tag;
      }
      return OPERATION_FAILED;
   }
      

}
