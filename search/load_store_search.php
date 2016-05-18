<?php

require_once 'SearchFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new SearchFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['searchQuery']) && isset($_POST['search_pos'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$searchQuery = $_POST['searchQuery'];
    $search_pos = $_POST['search_pos'];
   
    if($util->isActiveSession($username,$sessionId)) { 
       
      $res = $func->loadStoreSearch($username, $searchQuery, $search_pos);
      
      if($res != OPERATION_FAILED) {
        
        $response["data"] = array('shopping'=>$res);
        $response["message"] = "Search loaded successfully";
      
      }else {
        $response["error"] = true;
        $response["message"] = "Error loading search";
      
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