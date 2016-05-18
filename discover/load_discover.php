<?php

require_once 'DiscoverFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new DiscoverFunctions();
$util = new UtilFunctions();


// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['discover_pos'])) {
	
    
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $discover_pos = $_POST['discover_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $discover = $func->loadDiscover($username, $discover_pos);
    
      if($discover != NULL) {
        
        $response["data"] = $discover;//array('discover'=>$discover);
        $response["message"] = "Discover loaded successfully";
      
      }else { 
        $response["error"] = true;
        $response["message"] = "Error loading discover"; 
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