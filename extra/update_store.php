<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['isActive']) && isset($_POST['name']) && isset($_POST['description'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $isActive = $_POST['isActive'];
	$storeName = $_POST['name'];
    $storeDescription = $_POST['description'];

    if($util->isActiveSession($username,$sessionId)) {
        
      $res = $func->updateStore($username, $isActive, $storeName, $storeDescription);
         
      if($res != OPERATION_FAILED) {
        
        $response["message"] = "Store updated succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to update store";
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