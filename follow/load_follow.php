<?php

require_once 'FollowFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FollowFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username']) && isset($_POST['list_pos'])  && isset($_POST['list_type'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];
    $list_pos = $_POST['list_pos'];
    $list_type = $_POST['list_type'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $follow = NULL;
     
      if($list_type == LIST_FOLLOWING) {
        
         $follow = $func->loadFollowing($username,$target_username,$list_pos);
        
      }else if($list_type == LIST_FOLLOWERS) {
        
         $follow = $func->loadFollowers($username,$target_username,$list_pos);
        
      }
	
	  if($follow != OPERATION_FAILED) {
        
        $response["data"] = array('user'=>$follow);
        $response["message"] = "Users loaded successfully";
      
      }else {
      
        $response["error"] = true;
        $response["message"] = "Error loading users";
      
      }

    } else {
       $response["isSession"] = false;
       $response["error"] = true;
       $response["message"] = "Session expired"; 
    }
               
    echo json_encode($response);
	
}else {
    echo 'ERROR! missing param';
}