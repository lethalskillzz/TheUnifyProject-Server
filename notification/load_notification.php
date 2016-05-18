<?php

require_once 'NotificationFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new NotificationFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['notify_pos'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $notify_pos = $_POST['notify_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
        
      $notify = $func->loadNotification($username, $notify_pos);
    
      
      if($notify != OPERATION_FAILED) {
           
        $response["data"] = array('notification'=>$notify);
        $response["message"] = "Notification loaded succesfully";
     
      }else {
   
        $response["error"] = true;
        $response["message"] = "Unable to load notification";
 
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