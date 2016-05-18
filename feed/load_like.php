<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId'])  && isset($_POST['list_pos'])  && isset($_POST['list_type'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
    $list_pos = $_POST['list_pos'];
    $list_type = $_POST['list_type'];
	
    if($util->isActiveSession($username,$sessionId)) {
    
	  $user = $func->loadLike($username,$feedId,$list_pos,$list_type);
	
	  if($user != OPERATION_FAILED) {
        
        $response["data"] = array('user'=>$user);
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