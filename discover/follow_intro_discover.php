<?php

require_once 'DiscoverFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new DiscoverFunctions();
$util = new UtilFunctions();


// json response array
$response = array("isSession" => true,
              "error" => false);


$obj = file_get_contents('php://input');
$json_o=json_decode($obj);
       
if($util->isActiveSession($json_o->username,$json_o->sessionId)) {  
      
      $user = array();
      foreach($json_o->contact as $c) {   
          array_push($user, $c->user);      
      }
      
      $res = $func->followIntroDiscover($json_o->username,$user);
    
      if($res != OPERATION_FAILED) {
        
        $response["message"] = "Intro discover followed successfully";
      
      }else {
        $response["error"] = true;
        $response["message"] = "Error following intro discover";      
      }
         
} else {
        $response["isSession"] = false;
        $response["error"] = true;
        $response["message"] = "Session expired"; 
    }
            
echo json_encode($response);
