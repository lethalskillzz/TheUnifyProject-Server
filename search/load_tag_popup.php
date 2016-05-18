<?php

require_once 'SearchFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new SearchFunctions();
$util = new UtilFunctions();

 
// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['query']) && isset($_POST['type'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
	$query = $_POST['query'];
    $type = $_POST['type'];
    
    if($util->isActiveSession($username,$sessionId)) {
        
      $tag = NULL;
      $query = substr($query,1);
      if($type == "mention") 
          $tag = $func->loadMentionPopup($query);
      else 
          $tag = $func->loadHashPopup($query);
      
      if($tag != NULL) {
        
        $response["data"] = array('tag'=>$tag);
        $response["message"] = "Tag loaded successfully";
      
      } else {
        $response["error"] = true;
        $response["message"] = "Error loading tag";
      
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