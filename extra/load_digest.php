<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['category']) && isset($_POST['digest_pos'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$category = $_POST['category'];
    $digest_pos = $_POST['digest_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $digest = $func->loadDigest($category, $digest_pos);
         
      if($digest != OPERATION_FAILED) {
        $response["data"] = array('digest'=>$digest);
        $response["message"] = "Digest loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load digest";
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