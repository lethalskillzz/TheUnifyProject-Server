<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username'])  && isset($_POST['rating'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];
	$rating = $_POST['rating'];
	
    
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = $func->rateStore($username, $target_username, $rating);
	
	  if ($res == OPERATION_SUCCESSFULL) {
         
         $response["message"] = "Store rated successfully.";
        
      } else if ($res == OPERATION_FAILED) {       
         $response["error"] = true;
         $response["message"] = "Unable to rate store.";
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