
<?php

require_once 'ProfileFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ProfileFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['profilePic'])) {
	
    $username = $_POST['username'];
    $sessionId = $_POST['sessionId'];
    $profilePic = $_POST['profilePic'];
    
    if($util->isActiveSession($username,$sessionId)) {
	
	  $res = $func->changeProfilePic($username, $profilePic);
	
	  if ($res == OPERATION_SUCCESSFULL) {  
          $response["message"] = "Profile pic changed successfully.";  
      } else if ($res == OPERATION_FAILED) {
        
          $response["error"] = true;
          $response["message"] = "Profile pic change failed.";
      
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