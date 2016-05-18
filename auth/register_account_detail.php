<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();

// json response array
$response = array("error" => FALSE);
 
 
if (isset($_POST['sessionId']) && isset($_POST['username']) && isset($_POST['mobile']) && isset($_POST['password'])) {

    
    // receiving the post params
    $sessionId = $_POST['sessionId'];
	$username = $_POST['username'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    
    $phone_str = "+234".substr($mobile, 1,11);
    
    $res = $func->registerAccountDetail($sessionId, $username, $password, $phone_str);
    
    if ($res == USER_CREATED_SUCCESSFULLY) {
         
        // send sms
        //$func->sendSms($mobile, $otp);
         
        $response["error"] = false;
        $response["message"] = "SMS request is initiated! You will be receiving it shortly.";
        
    } else if ($res == USER_CREATE_FAILED) {
        
        $response["error"] = true;
        $response["error_type"] = "Error";
        $response["message"] = "Sorry! Error occurred in registration.";
        
    } else if ($res == USERNAME_ALREADY_EXIST) {
        
        $response["error"] = true;
        $response["error_type"] = "Username";
        $response["message"] = "Username already in use!";
        
    } else if ($res == MOBILE_ALREADY_EXIST) {
        
        $response["error"] = true;
        $response["error_type"] = "Mobile";
        $response["message"] = "Mobile number already in use!";
        
    }
    
    echo json_encode($response);
    
}else {
    echo 'ERROR! missing param';
}