<?php

require_once 'AuthenticationFunctions.php';
$func = new AuthenticationFunctions();

// json response array
$response = array("error" => FALSE);
 
 
if (isset($_POST['name']) && isset($_POST['location']) && isset($_POST['course']) && isset($_POST['level'])) {

    
    // receiving the post params
    $name = $_POST['name'];
    $location = $_POST['location'];
    $course = $_POST['course'];
    $level = $_POST['level'];
    
    
    $res = $func->registerProfileDetail($name, $location, $course, $level);
    
    if ($res != OPERATION_FAILED) {
         
        $response["detail"] = $res;
        $response["message"] = "User details stored successfully, User confirmation initiated.";
        
    } else {
        
        $response["error"] = true;
        $response["message"] = "Sorry! Error occurred in registration.";
        
    } 
   
    echo json_encode($response);
    
}else {
    echo 'ERROR! missing param';
}
