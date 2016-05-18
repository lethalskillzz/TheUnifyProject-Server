<?php

require_once 'ExtraFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');

$func = new ExtraFunctions();
$util = new UtilFunctions();

// json response array
$response = array("isSession" => true,
              "error" => false);
 
 
if (isset($_POST['username']) && isset($_POST['sessionId']) && isset($_POST['category'])  && isset($_POST['condition']) 
    && isset($_POST['price']) && isset($_POST['title'])  && isset($_POST['shop_img'])  && isset($_POST['description'])) {
	
    $username = $_POST['username'];
	$sessionId = $_POST['sessionId'];
    $category = $_POST['category'];
	$condition = $_POST['condition'];
    $price = $_POST['price'];
    $title = $_POST['title'];
	$image = $_POST['shop_img'];
    $description = $_POST['description'];
	
    if($util->isActiveSession($username,$sessionId)) {
        
	  $res = $func->postShopping($username, $category, $condition, $price, $title, $image, $description);
	
	  if ($res == OPERATION_SUCCESSFULL) {
         
         $response["message"] = "Shopping posted successfully.";
        
      } else if ($res == OPERATION_FAILED) {       
         $response["error"] = true;
         $response["message"] = "Post shopping failed.";
      }
      
    } else {
       $response["isSession"] = false;
       $response["error"] = true;
       $response["message"] = "Session expired"; 
    }
    
    echo json_encode($response);
    
} else {
    echo 'ERROR! missing param';
}