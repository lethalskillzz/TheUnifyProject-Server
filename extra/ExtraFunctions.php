<?php

class ExtraFunctions {

    private $conn;
    private $util;
    //private $notify;

    // constructor
    function __construct() {
        
        require_once (__DIR__ .  '/../DbConnect.php');
        include_once (__DIR__ .  '/../util/UtilFunctions.php');
        //include_once '\xampp\htdocs\ToreyProject\notification\NotificationFunctions.php';
        
        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();

        $this->util = new UtilFunctions();
        //$this->notify = new NotificationFunctions();

    }

    // destructor
    function __destruct() {
    }
    
    

    public function loadRepository($level, $faculty, $repo_pos) {
        
        if($level=='100L')
           $extra = "order by id desc limit $repo_pos,6";
        else
           $extra = "and faculty = '$faculty' order by id desc limit $repo_pos,6";
           
        
        $sql = "select id, title, image, url from repository where 
        level = '$level'  $extra";
        
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
                         'url' => PDF_JS_URL.$row->url
                );
            }
            $result->close();
            return $repo;
        }
        return OPERATION_FAILED;
    }



    public function loadDigest($category, $digest_pos) {
               
        if($category=="All") 
            $extra = "order by id desc limit $digest_pos,6";
        else 
            $extra = "where category = '$category' order by id desc limit $digest_pos,6";
              
        $sql = "select id, category, title, image, url from digest $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $digest = array();
            while($row = $result->fetch_object()) {
              
                $image = "";
                if(strlen($row->image)>0) 
                    $image = DIGEST_IMG_URL.$row->image;
 
                $digest[] = array( 'id' => $row->id,
                           'type' => 2,
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
    
    
    public function loadShopping($category, $shop_pos) {
        
        if($category=="All") 
            $extra = "order by id desc limit $shop_pos,6";
        else 
            $extra = "where category = '$category' order by id desc limit $shop_pos,6";       
       
        $sql = "select id, username, price, title, image from shopping $extra";
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
    
    
    
    public function displayShopping($shopId) {
        
        $extra = "where id = '$shopId'";
       
        $sql = "select id, username, category, _condition, price, title, image, description from shopping $extra";
        $result = $this->conn->query($sql);
        if($result) {

            $shop = array();
            while($row = $result->fetch_object()) {
                
                $image = "";
                if(strlen($row->image)>0) 
                   $image = SHOP_IMG_URL.$row->image;
              
                $shop[] = array( 'type' => 3,
                         'id' => $row->id,
                         'category' => $row->category,
                         'username' => $row->username,
                         'condition' => $row->_condition,
                         'price' => number_format($row->price, 2),
                         'title' => $row->title,
                         'image' => $image,
                         'description' => $row->description,
                         'mobile' => $this->util->getMobile($row->username),
                         'store' => $this->util->getStoreName($row->username)
                );
            }
            $result->close();
            return $shop;
        }
        return OPERATION_FAILED;
    }
    
    
    private function deleteOldestShopping($username) {
        
        $sql = $this->conn->prepare("delete min(id) from shopping where username='$username'");
        $result = $sql->execute();
        $sql->close();
        
        if($result) {
           return OPERATION_SUCCESSFULL; 
        }
        return OPERATION_FAILED;
    }
        
    
    private function shoppingCount($username) {

        $sql = $this->conn->prepare("select * from shopping where username = '$username'");  
        $result = $sql->execute();
        if($result) {
            
            $count = $sql->num_rows;
            $sql->close();

            return $count;
        }
        return OPERATION_FAILED;
    }
    

    public function postShopping($username, $category, $condition, 
                                  $price, $title, $image, $description) {                                    

        $img_file = NULL;
           
        /* if($this->shoppingCount($username)>=15) 
              $this->deleteOldestShopping($username);*/
                
        if($image!=null) {

            $sid = substr(uniqid("shop", true), 0, 12);
            $img_file = $username."_";
            $img_file = $img_file.$sid;
            $img_file = $img_file.".png";

            $target_dir = SHOP_IMG_DIR.$img_file;
            $save_img = str_replace('data:image/jpg;base64,', '', $image);

            if(!file_put_contents($target_dir, base64_decode($save_img))) {
               return OPERATION_FAILED;
            }
        }


       $sql = $this->conn->prepare("insert into shopping (username, category, _condition, price, title, image, description) 
                    values ( '$username', '$category', '$condition', '$price', '".$this->conn->real_escape_string($title). "',
                     '$img_file', '".$this->conn->real_escape_string($description). "')");

      $result = $sql->execute();
      $sql->close();

      if($result) {
         return OPERATION_SUCCESSFULL;
      } 
      return OPERATION_FAILED;
    }
    
    
    public function editShopping($username, $shopId, $category, $condition, 
                                     $price, $title, $image, $description) {                                    

        if($this->isPoster($username, $shopId)) {
            
           $img_file = $this->getShopImg($shopId);
           
           if($image!=null) {
           
              if($img_file==OPERATION_FAILED) {
               
                 $sid = substr(uniqid("shop", true), 0, 12);
                 $img_file = $username."_";
                 $img_file = $img_file.$sid;
                 $img_file = $img_file.".png";
              }

              $target_dir = SHOP_IMG_DIR.$img_file;
              $save_img = str_replace('data:image/jpg;base64,', '', $image);

              if(!file_put_contents($target_dir, base64_decode($save_img))) {
                 return OPERATION_FAILED;
              }
           }

         $sql = $this->conn->prepare("update shopping set category = '$category', _condition = '$condition', 
                       price = '".$this->conn->real_escape_string($price). "', title = '".$this->conn->real_escape_string($title). "', 
                      image = '$img_file', description = '".$this->conn->real_escape_string($description). "' where id = $shopId");
                   
         $result = $sql->execute();
         $sql->close();

         if($result) {
            return OPERATION_SUCCESSFULL;
         } 
      }
      return OPERATION_FAILED;
    }
    

    private function getShopImg($shopId) {

        $sql = "select image from shopping where id = $shopId";  
        $result = $this->conn->query($sql);
        if($result) {
            
            while($row = $result->fetch_object()) {
                if(strlen($row->image)!=0) {
                    return $row->image; 
                }
            }
        }
        return OPERATION_FAILED; 
    }
    
    
    private function deleteShopImg($shopId) {

        $img = $this->getShopImg($shopId);
        if($img!=OPERATION_FAILED) {
           unlink(SHOP_IMG_DIR.$img);
           return OPERATION_SUCCESSFULL; 
         }
         return OPERATION_FAILED; 
    }
    

    public function deleteShopping($username, $shopId) {
       
        if($this->isPoster($username, $shopId)) {
            
          $this->deleteShopImg($shopId);
          
          $sql = $this->conn->prepare("delete from shopping where id = $shopId");
          $result = $sql->execute();
          $sql->close();
          if($result) { 
            return OPERATION_SUCCESSFULL;
          }
        }
        return OPERATION_FAILED;
    }
    
    
   private function isPoster($username, $shopId) {
        
        $sql = "select username from shopping where id = $shopId";
        $result = $this->conn->query($sql);
        if($result) { 
            
             while($row = $result->fetch_object()) {
                 if(strcmp($username,$row->username)==0) {
                   return true;
                 }
             }
        }
        return false;
    }
    
   
    private function hasStore($username) {
        
        $sql = "select hasStore from users where username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {
            
            while($row = $result->fetch_object()) {
                  if($row->hasStore == true) {
                      return TRUE;
                  }
            }  
        }
        return FALSE;
    }
   
   
    public function updateStore($username, $isActive, $storeName, $storeDescription) {
        
         $sql = $this->conn->prepare("update users set hasStore = '$isActive', storeName = '".$this->conn->real_escape_string($storeName). "', 
         storeDescription = '".$this->conn->real_escape_string($storeDescription). "' where username = '$username'");
                   
         $result = $sql->execute();
         $sql->close();

         if($result) {
             return OPERATION_SUCCESSFULL;
         }
         return OPERATION_FAILED;
    }
    
    
    Private function getStoreRatingCount($username) {
        
        $sql = "select * from store_rating where target_username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {
               
            $count = $result->num_rows;
            $result->close();

            return $count;
        }
        return OPERATION_FAILED;
    }
    
    
    Private function getStoreRating($username) {
        
        $sql = "select rating from store_rating where target_username = '$username'";
        $result = $this->conn->query($sql);
        if($result) {
            
            $rating = 0.0;
            while($row = $result->fetch_object()) {              
                  $rating += (float)$row->rating;
            }
            $result->close();
            return $rating;
        }
        return OPERATION_FAILED;
    }
    
    
    public function rateStore($username, $target_username, $rating) {
        
        if($this->deleteStoreRating($username,$target_username)==OPERATION_SUCCESSFULL) {
        
           $sql = $this->conn->prepare("insert into store_rating (username, target_username, rating) 
                    values ('$username', '$target_username', $rating)");

           $result = $sql->execute();
           $sql->close();

           if($result) {
              return OPERATION_SUCCESSFULL;
           }  
        }
        return OPERATION_FAILED; 
    }
    
    
    private function deleteStoreRating($username,$target_username) {
        
        $sql = $this->conn->prepare("delete from store_rating
			   where username='$username' and target_username = '$target_username' ");
        
        $result = $sql->execute();
        $sql->close();
        if($result) {
           return OPERATION_SUCCESSFULL; 
        }
        return OPERATION_FAILED;
    }
        
            
    public function loadStoreDetail($username) {
        
        $sql = "select storeName, storeDescription from users
        where username = '$username'";

        $result = $this->conn->query($sql);
        if($result) {
            $detail = array();
            while($row = $result->fetch_object()) {
                
                $rating = $this->getStoreRating($username) / $this->getStoreRatingCount($username);
                
                $detail[] = array( 'name' => $row->storeName,
                            'description' => $row->storeDescription,
                            'rating' => $rating
                );
            }
            $result->close();
            return $detail;
        }
        return OPERATION_FAILED;
    }
        
        
    public function loadStoreSetting($username) {
        
        $sql = "select storeName, storeDescription, hasStore from users
        where username = '$username'";

        $result = $this->conn->query($sql);
        if($result) {
            
            $setting = array();
            while($row = $result->fetch_object()) {
                
                $setting[] = array( 'name' => $row->storeName,
                            'description' => $row->storeDescription,
                            'isActive' => $row->hasStore
                );
            }
            $result->close();
            return $setting;
        }
        return OPERATION_FAILED;
    }
        

    public function loadStore($target_username, $category, $store_pos) {
        
        if($category=="All") 
            $extra = "where username = '$target_username' order by id desc limit $store_pos,6";
        else 
            $extra = "where username = '$target_username' and category = '$category' order by id desc limit $store_pos,6";       
       
        $sql = "select id, price, title, image from shopping $extra";
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
    
    private function deleteTransit($username) {
        
        $sql = $this->conn->prepare("delete from transit where username='$username'  ");
        $result = $sql->execute();
        $sql->close();
        if($result) {
           return OPERATION_SUCCESSFULL; 
        }
        return OPERATION_FAILED;
    }
    
    
    public function postTransit($username, $bosso_means, $bosso_time, 
                                $gidan_kwano_means, $gidan_kwano_time) {
            
        $value = OPERATION_FAILED; 
                                            
        if($this->deleteTransit($username)==OPERATION_SUCCESSFULL) {                           
                                    
           if($bosso_means != "None") {
              $value = $this->postBossoTransit($username, $bosso_means, $bosso_time);
           }
        
           if($gidan_kwano_means != "None") {
              $value = $this->postGidanKwanoTransit($username, $gidan_kwano_means, $gidan_kwano_time);
           }     
        }
        return $value;   
         
    }
    
    
    private function postBossoTransit($username, $bosso_means, $bosso_time) {        
        $campus = CAMPUS_BOSSO;        
        $sql = $this->conn->prepare("insert into transit (username, campus, means, time) 
                    values ( '$username', '$campus', '$bosso_means', '$bosso_time')");

        $result = $sql->execute();
        $sql->close();

        if($result) {
           return OPERATION_SUCCESSFULL;
        }  
        return OPERATION_FAILED;                                     
    }
    
    
    private function postGidanKwanoTransit($username, $gidan_kwano_means, $gidan_kwano_time) {      
        $campus = CAMPUS_GIDAN_KWANO;
        $sql = $this->conn->prepare("insert into transit (username, campus, means, time) 
                    values ( '$username', '$campus', '$gidan_kwano_means', '$gidan_kwano_time')");

        $result = $sql->execute();
        $sql->close();

        if($result) {
           return OPERATION_SUCCESSFULL;
        }  
        return OPERATION_FAILED;  
                                    
    }
    

    public function loadBusTransit($campus) {
        $means = SCHOOL_BUS;
        $sql = "select time from transit where campus = '$campus' and means = '$means'";
        $result = $this->conn->query($sql);   
        if($result) {
            
           $transit = array();
           $t6_7=0; $t7_8=0; $t8_9=0; $t9_10=0; 
           $t10_11=0; $t11_12=0; $t12_1=0; $t1_2=0; 
           $t2_3=0; $t3_4=0; $t4_5=0; $t5_6=0;
           
           while($row = $result->fetch_object()) {
               
               switch ($row->time) {
                   
                   case '06:00 - 07:00':
                       $t6_7++;
                       break;
                       
                   case '07:00 - 08:00':
                       $t7_8++;
                       break;
                       
                   case '08:00 - 09:00':
                       $t8_9++;
                       break;
                            
                  case '09:00 - 10:00':
                       $t9_10++;
                       break; 
                       
                  case '10:00 - 11:00':
                       $t10_11++;
                       break; 
                       
                  case '11:00 - 12:00':
                       $t11_12++;
                       break; 
                       
                  case '12:00 - 01:00':
                       $t12_1++;
                       break;
                       
                  case '01:00 - 02:00':
                       $t1_2++;
                       break;
                       
                  case '02:00 - 03:00':
                       $t2_3++;
                       break;
                       
                  case '03:00 - 04:00':
                       $t3_4++;
                       break;
                                            
                  case '04:00 - 05:00':
                       $t4_5++;
                       break;   
                                          
                  case '05:00 - 06:00':
                       $t5_6++;
                       break;   
                                          
                   default:
                       break;
               }
           }
           $result->close();
           
           $transit[] = array( '06:00 - 07:00' => $t6_7,
                        '07:00 - 08:00' => $t7_8,
                        '08:00 - 09:00' => $t8_9,
                        '09:00 - 10:00' => $t9_10,
                        '10:00 - 11:00' => $t10_11,
                        '11:00 - 12:00' => $t11_12,
                        '12:00 - 01:00' => $t12_1,
                        '01:00 - 02:00' => $t1_2,
                        '02:00 - 03:00' => $t2_3,
                        '03:00 - 04:00' => $t3_4,
                        '04:00 - 05:00' => $t4_5,
                        '05:00 - 06:00' => $t5_6,
                        'taxi_count' => $this->countTaxiTransit($campus)
           );
           
           return $transit;
        }
        return OPERATION_FAILED;  
    }
    
    
    private function countTaxiTransit($campus) {
        
        $means = TAXI;
        
        $sql = "select * from transit where campus = '$campus' and means = '$means'";
        $result = $this->conn->query($sql);   
        if($result) {
            $count = $result->num_rows;
            $result->close();
            
            return $count;
        }
        return OPERATION_FAILED; 
    }
    
    
    public function loadTaxiTransit($username, $campus, $time, $transit_pos) {
        
        $means = TAXI;
        $extra = "limit $transit_pos,10";
        
        $sql = "select username from transit where campus = '$campus' and `time` = '$time' and means = '$means' $extra";
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
    }
    
    

}

