<?php

require_once 'SearchFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new SearchFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
$obj = file_get_contents('php://input');
$json_o=json_decode($obj);
       
if($util->isActiveSession($json_o->username,$json_o->sessionId)) {  
      
    $phone = array();
    if(count($json_o->contact)) {
          
         foreach($json_o->contact as $c) {
             $phone_str = preg_replace('/\s+/','',$c->phone);
             if(strlen($phone_str)>11) {
                 
                if(substr($phone_str, 0,4)=="+234") {
                   array_push($phone, $phone_str);
                }
             } else if(strlen($phone_str)==11) {
                 
                 $phone_str = "+234".substr($phone_str, 1,11);
                 array_push($phone, $phone_str); 
             }
                   
         }
      }
      
     $user = $func->loadContactSearch($json_o->username, $phone, $json_o->contact_pos);
   
     if($user != OPERATION_FAILED) {
        
          $response["data"] = array('user'=>$user);
          $response["message"] = "Contact loaded successfully";
      
      }else {                    
          $response["error"] = true;
          $response["message"] = "Error loading contact";      
      }
	
} else {
      $response["isSession"] = false;
      $response["error"] = true;
      $response["message"] = "Session expired"; 
  }
    
echo json_encode($response);
       