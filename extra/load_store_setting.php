<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];

   
    if($util->isActiveSession($username,$sessionId)) {
        
      $setting = $func->loadStoreSetting($username);
         
      if($setting != OPERATION_FAILED) {
        $response["data"] = array('setting'=>$setting);
        $response["message"] = "Store setting loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load store setting";
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