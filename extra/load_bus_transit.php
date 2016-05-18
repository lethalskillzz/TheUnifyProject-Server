<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['campus'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $campus = $_POST['campus'];
	
    if($util->isActiveSession($username,$sessionId)) {
    
      $transit = $func->loadBusTransit($campus);
         
      if($transit != OPERATION_FAILED) {
        $response["data"] = array('transit'=>$transit);
        $response["message"] = "Transit loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load transit";
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