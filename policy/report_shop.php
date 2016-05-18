<?php

require_once 'PolicyFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new PolicyFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if(isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['shopId'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $shopId = $_POST['shopId'];

    if($util->isActiveSession($username,$sessionId)) {
        
        $res = $func->reportShop($username, $shopId);
	
        if($res == OPERATION_SUCCESSFULL) {
         
            $response["message"] = "Shop reported successfully.";
        
       } else if ($res == OPERATION_FAILED) {
        
            $response["error"] = true;
            $response["message"] = "report shop failed.";    
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