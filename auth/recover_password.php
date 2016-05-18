<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();
 
// json response array
$response = array("error" => FALSE);
 
if (isset($_POST['type']) && isset($_POST['login'])) {
 
    // receiving the post params
    $type = $_POST['type'];
    $login = $_POST['login'];

    $res = null;
    
    if($type=='mobile') 
       // get the user by mobile and password
       $res = $func->recoverPasswordByMobile($login);
    else if ($type=='username')
             // get the user by username and password
             $res = $func-> $func->recoverPasswordByUsername($login);
 
     if($res != OPERATION_FAILED) {
        //user is found
        $response["message"] = "Password recovery SMS sent!";
       
    } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["message"] = "Failed to recover password!";
        
    }
    
    echo json_encode($response);
    
} else {
    echo 'ERROR! missing param';
}