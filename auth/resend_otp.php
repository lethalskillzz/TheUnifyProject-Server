<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['mobile'])) {
 
    // receiving the post params
    $mobile = $_POST['mobile'];
    
    $phone_str = "+234".substr($mobile, 1,11);
    
    $res = $func->resendOtp($phone_str);
 
    if($res != OPERATION_FAILED) {
        // send sms
        $response["message"] = "Verification code successfully sent!";
       
    } else {

        $response["error"] = TRUE;
        $response["message"] = "Unable to send verification code!";
        
    }
    
    echo json_encode($response);
    
} else {
    echo 'ERROR! missing param';
}