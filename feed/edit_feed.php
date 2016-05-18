<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId']) 
&& isset($_POST['feed_msg'])  && isset($_POST['feed_img'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
    $feed_msg = $_POST['feed_msg'];
    $feed_img = $_POST['feed_img'];
	
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $res = $func->editFeed($username, $feedId, $feed_msg, $feed_img);
         
      if($res != OPERATION_FAILED) {
        
        $response["message"] = "Feed edited succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to edit feed";
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