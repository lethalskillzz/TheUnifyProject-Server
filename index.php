<?php

    function generateUserID() {
        return md5(uniqid(rand(), true));
    }
    
    
    function hashSSHA($password) {
 
        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }
 
    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    function checkhashSSHA($salt, $password) {
 
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
 
        return $hash;
    }
    
    $password = 'looqman';
    
    $hash = hashSSHA($this->conn->real_escape_string($password));
    $encrypted_password = $hash["encrypted"]; // encrypted password
    $salt = $hash["salt"]; // salt
    
    $decrpt = checkhashSSHA($salt,$password);
    
    echo " $decrpt <br /> $encrypted_password <br /> $salt";
    
    if(!strcmp($decrpt,$encrypted_password))
    {
       //echo "<br/> YEAA"; 
    }
    
    $type ='following';
    if($type=='following') {
        echo "<br/> YEAA";
    }
    
    //echo preg_replace('/\s+/','','Your answer worked for');
    echo substr(md5(uniqid(mt_rand(), true)), 0, 8);