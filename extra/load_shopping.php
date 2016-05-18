<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['category']) && isset($_POST['shop_pos'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$category = $_POST['category'];
    $shop_pos = $_POST['shop_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
        
      $shop = $func->loadShopping($category, $shop_pos);
         
      if($shop != OPERATION_FAILED) {
        $response["data"] = array('shopping'=>$shop);
        $response["message"] = "Shopping loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load shopping";
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