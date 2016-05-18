<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];

    
    if($util->isActiveSession($username,$sessionId)) {
        
      $detail = $func->loadStoreDetail($target_username);
         
      if($detail != OPERATION_FAILED) {
        $response["data"] = array('detail'=>$detail);
        $response["message"] = "Store loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load store";
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