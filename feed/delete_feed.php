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

      $res = $func->deleteFeed($username, $feedId);
	
      if ($res == OPERATION_SUCCESSFULL) {
         
         $response["message"] = "Feed deleted successfully.";
        
      } else if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "delete feed failed.";    
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