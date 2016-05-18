<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId']) && isset($_POST['comment_pos'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
    $comment_pos = $_POST['comment_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $comment = $func->loadComment($feedId, $comment_pos);
    
      if($comment != OPERATION_FAILED) {
        
        $response["data"] = array('comment'=>$comment);
        $response["message"] = "Comments loaded successfully";
      
      }else {
      
        $response["error"] = true;
        $response["message"] = "Error loading comments";
      
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