<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $feed = $func->displayFeed($username, $feedId);
         
      if($feed != OPERATION_FAILED) {
        
        $response["data"] = array('feed'=>$feed);
        $response["message"] = "Feed displayed succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to display feed";
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