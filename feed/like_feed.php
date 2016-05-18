<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

/// json response array
$response = array("isSession" => true,
              "error" => false);
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId']) && isset($_POST['like_type'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
    $like_type = $_POST['like_type'];
    
    if($util->isActiveSession($username,$sessionId)) {
	
      $res = null;
      
      if($like_type == "like") {
	    $res = $func->likeFeed($username, $feedId);
      }else if($like_type == "unlike") {
        $res = $func->unlikeFeed($username, $feedId);
      }
	
      if ($res == OPERATION_SUCCESSFULL) {
         
         $response["data"] = array ('feedId' => $feedId,
             'count' => $util->likeCount($feedId));
         $response["message"] = "Feed liked successfully.";
        
      } else if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "Like feed failed.";
        
      }
      
    } else {
       $response["isSession"] = false;
       $response["error"] = true;
       $response["message"] = "Session expired"; 
    }    
    
    echo json_encode($response);
} else {
    echo 'ERROR! missing param';
}