<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['bosso_means'])  && isset($_POST['bosso_time']) 
    && isset($_POST['gidan_kwano_means']) && isset($_POST['gidan_kwano_time'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $bosso_means = $_POST['bosso_means'];
	$bosso_time = $_POST['bosso_time'];
    $gidan_kwano_means = $_POST['gidan_kwano_means'];
    $gidan_kwano_time = $_POST['gidan_kwano_time'];
	
    
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = $func->postTransit($username, $bosso_means, $bosso_time, $gidan_kwano_means, $gidan_kwano_time);
	
	  if ($res == OPERATION_SUCCESSFULL) {
         
         $response["message"] = "Transit posted successfully.";
        
      } else if ($res == OPERATION_FAILED) {       
         $response["error"] = true;
         $response["message"] = "Post transit failed.";
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