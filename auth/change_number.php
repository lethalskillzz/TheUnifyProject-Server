<?php

require_once 'AuthenticationFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new AuthenticationFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['mobile'])  && isset($_POST['password'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    
    if($util->isActiveSession($username,$sessionId)) {

      $phone_str = "+234".substr($mobile, 1,11);
    
      $res = $func->InitializeChangeNumber($username, $phone_str, $password);
	   
      if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "Unable to initialize number change.";   
          
      } else {
         $response["message"] = "Number change initialized.";
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