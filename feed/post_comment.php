<?php

require_once 'FeedFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new FeedFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId'])  && isset($_POST['comment'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];
	$comment = $_POST['comment'];
	
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = $func->postComment($username, $feedId, $comment);
	
	  if ($res == OPERATION_SUCCESSFULL) {
         
         $response["data"] = array ('feedId' => $feedId,
         'count' => $util->commentCount($feedId));
         $response["message"] = "Comment posted successfully.";
        
      } else if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "Error posting comment.";
        
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