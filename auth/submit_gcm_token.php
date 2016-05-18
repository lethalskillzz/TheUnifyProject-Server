<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['mobile']) && isset($_POST['gcm_token'])) {
 
    // receiving the post params
    $mobile = $_POST['mobile'];
    $gcm_token = $_POST['gcm_token'];

    $res = $func->submitGcmToken($mobile, $gcm_token);
    
    if($res != OPERATION_FAILED) {
        
        $response["message"] = "GCM token submitted successfully";
 
    }else {
        
        $response["error"] = TRUE;
        $response["message"] = "Unable to submit GCM token!";
    }
    
   echo json_encode($response);
    
}else {
    echo 'ERROR! missing param';
}