<?php

require_once 'PolicyFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new PolicyFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username']) && isset($_POST['report'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];
    $report = $_POST['report'];

    if($util->isActiveSession($username,$sessionId)) {
     
       $res = $func->reportUser($username, $target_username, $report);
	
       if ($res == OPERATION_SUCCESSFULL) {
         
           $response["message"] = "User reported successfully.";
        
      } else if ($res == OPERATION_FAILED) {
        
           $response["error"] = true;
           $response["message"] = "report user failed.";    
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