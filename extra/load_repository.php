<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['level'])  
    && isset($_POST['faculty']) && isset($_POST['repo_pos'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $level = $_POST['level'];
	$faculty = $_POST['faculty'];
    $repo_pos = $_POST['repo_pos'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $repository = $func->loadRepository($level, $faculty, $repo_pos);
         
      if($repository != OPERATION_FAILED) {
        $response["data"] = array('repository'=>$repository);
        $response["message"] = "Repository loaded succesfully";
      
      }else {    
        $response["error"] = true;
        $response["message"] = "Unable to load repository";
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