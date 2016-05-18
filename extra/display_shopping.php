<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['shopId'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$shopId = $_POST['shopId'];

    if($util->isActiveSession($username,$sessionId)) {
        
      $shop = $func->displayShopping($shopId);
         
      if($shop != OPERATION_FAILED) {
        
        $response["data"] = array('shopping'=>$shop);
        $response["message"] = "Shopping displayed succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to display shopping";
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