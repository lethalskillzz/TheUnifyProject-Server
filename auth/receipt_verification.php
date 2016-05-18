<?php

require_once 'AuthenticationFunctions.php';
include_once (__DIR__ .  '/../util/UtilFunctions.php');
 require_once(__DIR__ . '/../autoload.php');
       

$func = new AuthenticationFunctions();
$util = new UtilFunctions();


 //set true if you want to use script for billing reports
  //first you need to enable them in your account
  $billing_reports_enabled = false;

  // check that the request comes from Fortumo server
  if(!in_array($_SERVER['REMOTE_ADDR'],
      array('1.2.3.4','2.3.4.5'))) {
    header("HTTP/1.0 403 Forbidden");
    die("Error: Unknown IP");
  }

  // check the signature
  $secret = ''; // insert your secret between ''
  if(empty($secret) || !check_signature($_GET, $secret)) {
    header("HTTP/1.0 404 Not Found");
    die("Error: Invalid signature");
  }

  $product_name = $_GET['product_name'];
  $billing_status = $_GET['status']; 
  $message_id = $_GET['message_id'];//unique id

  // print out the reply
  echo($reply);

  // only grant virtual credits to account, if payment has been successful.
 if (($_GET['billing_type'] == 'MO' and $_GET['status'] == 'pending') or (in_array($_GET['billing_type'], array('MT', 'CC', 'DCB')) and $_GET['billing_type'] == 'OK')) {
     add_credits(user_by_product_name($product_name), $_GET['credit_amount']);
  }

  function check_signature($params_array, $secret) {
    ksort($params_array);

    $str = '';
    foreach ($params_array as $k=>$v) {
      if($k != 'sig') {
        $str .= "$k=$v";
      }
    }
    $str .= $secret;
    $signature = md5($str);

    return ($params_array['sig'] == $signature);
  } 