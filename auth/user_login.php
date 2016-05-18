<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['type']) && isset($_POST['login']) && isset($_POST['password'])) {
 
    // receiving the post params
    $type = $_POST['type'];
    $login = $_POST['login'];
    $password = $_POST['password'];

    $detail = null;
    
    if($type=='mobile') {
       // get the user by mobile and password
       $phone_str = "+234".substr($login, 1,11);
       $detail = $func-> loginMobile($phone_str, $password);
       
    } else if ($type=='username')
             // get the user by username and password
             $detail = $func-> loginUsername($login, $password);
 
     if($detail != OPERATION_FAILED) {
        //user is found
        $response["message"] = "Login successful!";
        $response["detail"] = $detail;
       
    } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["message"] = " Invalid Login credentials!";
        
    }
    
    echo json_encode($response);
    
} else {
    echo 'ERROR! missing param';
}