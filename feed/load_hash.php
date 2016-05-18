<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['hash']) && isset($_POST['feed_pos'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $hash = $_POST['hash'];
    $feed_pos = $_POST['feed_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $feed = $func->loadHash($username, $hash, $feed_pos);
       
      if($feed != OPERATION_FAILED) {
        
        $response["data"] = array('feed'=>$feed);
        $response["message"] = "Feed loaded succesfully";
   
      }else {
            
        $response["error"] = true;
        $response["message"] = "Unable to load feed";
      
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