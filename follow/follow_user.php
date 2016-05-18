<?php

require_once 'FollowFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FollowFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username']) && isset($_POST['follow_type'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$target_username = $_POST['target_username'];
	$follow_type = $_POST['follow_type'];
	
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = null;
	
	  if($follow_type == "follow") {
	    $res = $func->followUser($username, $target_username);
	  }else if($follow_type == "unfollow") {
	    $res = $func->unfollowUser($username, $target_username);
	  }
    
      if ($res == OPERATION_SUCCESSFULL) {
             
	     $response["data"] = array ('target_username' =>  $target_username,
             'isFollow' => $util->isFollow($username,$target_username)
         );
         $response["message"] = "User followed successfully";
        
      } else if ($res == OPERATION_FAILED) {
        
	     $response["error"] = true;
         $response["message"] = "Unable to follow user";	
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