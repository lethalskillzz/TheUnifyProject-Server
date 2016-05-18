<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username']) && isset($_POST['category']) && isset($_POST['store_pos'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];
	$category = $_POST['category'];
    $store_pos = $_POST['store_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
        
      $store = $func->loadStore($target_username, $category, $store_pos);
         
      if($store != OPERATION_FAILED) {
        $response["data"] = array('store'=>$store);
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