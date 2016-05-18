<?php

require_once 'AuthenticationFunctions.php';
$auth = new AuthenticationFunctions();

 // json response array
$response = array("error" => FALSE);
 
if (isset($_POST['mobile']) && isset($_POST['otp'])) {
    
    $mobile = $_POST['mobile'];
    $otp = $_POST['otp'];

    $phone_str = "+234".substr($mobile, 1,11);
    
    $detail = $auth->verifyOtp($phone_str, $otp);
    
    if($detail != OPERATION_FAILED) {
      $response["message"] = "User created successfully!";
      $response["detail"] = $detail;
    } else {
      $response["error"] = true;
      $response["message"] = "Sorry! Failed to create your account.";
    }
        
    echo json_encode($response);

}else {
    echo 'ERROR! missing param';
}

