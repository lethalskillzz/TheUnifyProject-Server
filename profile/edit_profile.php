
<?php

require_once 'ProfileFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ProfileFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['name']) && isset($_POST['location']) 
 && isset($_POST['course']) && isset($_POST['level'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $name = $_POST['name'];
    $location = $_POST['location'];
    $course = $_POST['course'];
    $level = $_POST['level'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
	  $detail = $func->editProfile($username, $name, $location, $course, $level);
        
         
      if ($detail == OPERATION_FAILED) {
        
          $response["error"] = true;
          $response["message"] = "Profile edit failed.";
        
      } else {
          
          $response["message"] = "Profile edited successfully."; 
          $response["detail"] = $detail;
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