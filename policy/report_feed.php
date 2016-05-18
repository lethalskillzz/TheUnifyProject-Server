<?php

require_once 'PolicyFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new PolicyFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['feedId'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $feedId = $_POST['feedId'];

    if($util->isActiveSession($username,$sessionId)) {
        
        $res = $func->reportFeed($username, $feedId);
	
        if ($res == OPERATION_SUCCESSFULL) {
         
            $response["message"] = "Feed reported successfully.";
        
       } else if ($res == OPERATION_FAILED) {
        
            $response["error"] = true;
            $response["message"] = "report feed failed.";    
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