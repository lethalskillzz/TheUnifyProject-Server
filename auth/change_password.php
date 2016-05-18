<?php

require_once 'AuthenticationFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new AuthenticationFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['oldPassword'])  && isset($_POST['newPassword'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    
    if($util->isActiveSession($username,$sessionId)) {

      $res = $func->changePassword($username, $oldPassword, $newPassword);
        
      if ($res == OPERATION_FAILED) {
        
         $response["error"] = true;
         $response["message"] = "Unable to change password."; 
            
      } else {
         $response["message"] = "Password changed successfully.";
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