<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feed_msg'])  && isset($_POST['feed_img'])) {
	
    
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $feed_msg = $_POST['feed_msg'];
    $feed_img = $_POST['feed_img'];
	
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = $func->postFeed($username, $feed_msg, $feed_img);
	
	  if ($res == OPERATION_SUCCESSFULL) {
         
         $response["message"] = "Feed posted successfully.";
        
      } else if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "Post feed failed.";
        
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