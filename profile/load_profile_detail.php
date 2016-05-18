<?php 

require_once 'ProfileFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ProfileFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['target_username'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $target_username = $_POST['target_username'];
    
    if($util->isActiveSession($username,$sessionId)) {
    
      $detail = $func->loadProfileDetail($username, $target_username);
    
	  if($detail != OPERATION_FAILED)
      {
        $response["message"] = "Profile loaded successfully!";
        $response["profile"] = array('detail'=>$detail);
      } else {
        $response["error"] = true;
        $response["message"] = "Sorry! Failed to load profile.";
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