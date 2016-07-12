<?php 

class AuthenticationFunctions {

    private $conn;

    // constructor
    function __construct() {
        require_once(__DIR__.'/../DbConnect.php');
        require_once(__DIR__.'/../autoload.php');

        // connecting to database
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct() {}


    private function deleteUserMobile($mobile) {

        $sql = $this->conn->prepare("delete from users where mobile='$mobile'");
        $result = $sql->execute();
        $sql->close();
        return $result;
    }


    private function deleteUserUsername($username) {

        $sql = $this->conn->prepare("delete from users where username='$username'");
        $result = $sql->execute();
        $sql->close();
        return $result;
    }

    /**
     * registering user details
     * @param String $name User full name
     * @param String $gender User gender
     * @param String $mobile User mobile number
     */
    public function registerProfileDetail($name, $location, $course, $level) {

        $ssid = $this->generateSessionId();
        $uname = $this->generateUsername($name);

        $sql = $this->conn->prepare("insert into users (sessionId, fullname, location, course, level)
         values ('$ssid', '".$this->conn->real_escape_string($name)."', '$location', '$course', '$level')");
        $result = $sql->execute();
        //echo $sql->error;
        $sql->close();

        if ($result) {

            $detail = array();
            $detail["sessionId"] = $ssid;
            $detail["username"] = $uname;

            return $detail;
        }
        return OPERATION_FAILED;
    }


    /**
     * registering user details
     * @param String $username unique username
     * @param String $mobile user mobile number
     */
    public function registerAccountDetail($sessionId, $username, $password, $mobile) {

        if (!$this->isUsernameExist($username)) {
            $this->deleteUserUsername($username);

            if (!$this->isMobileExist($mobile)) {
                $this->deleteUserMobile($mobile);

                $hash = $this->hashSSHA($this->conn->real_escape_string($password));
                $encrypted_password = $hash["encrypted"]; // encrypted password
                $salt = $hash["salt"]; // salt


                $sql = $this->conn->prepare("update users set username = '".$this->conn->real_escape_string($username)."',
                mobile = '".$this->conn->real_escape_string($mobile)."', encrypted_password = '$encrypted_password',
                salt = '$salt' where sessionId = '$sessionId'");
                $result = $sql->execute();
                $sql->close();

                if ($result) {

                    $this->createOtp($mobile);
                    return USER_CREATED_SUCCESSFULLY;

                } else {
                    return USER_CREATE_FAILED;
                }

            } else {
                return MOBILE_ALREADY_EXIST;
            }

        } else {
            return USERNAME_ALREADY_EXIST;
        }
    }


    /**
     * Checking for duplicate user by mobile number
     * @param String $mobile mobile number to check in db
     * @return boolean
     */
    private function isMobileExist($mobile) {
        $sql = $this->conn->prepare("select * from users
			   where mobile = '".$this->conn->real_escape_string($mobile)."' and isActive = 1");

        $result = $sql->execute();
        if ($result) {
            $row = $sql->get_result()->fetch_assoc();
            if (count($row)) {
                return true;
            }

        }
        return false;
    }


    /**
     * Checking for duplicate user by mobile number
     * @param String $username unique username to check in db
     * @return boolean
     */
    private function isUsernameExist($username) {
        $sql = $this->conn->prepare("select * from users
			   where username = '".$this->conn->real_escape_string($username)."' and isActive = 1");

        $result = $sql->execute();
        if ($result) {
            $row = $sql->get_result()->fetch_assoc();
            if (count($row)) {
                return true;
            }

        }
        return false;
    }



    private function deleteOtp($mobile) {

        $sql = $this->conn->prepare("delete from otp_sms
			   where mobile='$mobile'");

        $result = $sql->execute();
        $sql->close();
        if ($result) {
            return OPERATION_SUCCESSFULL;
        }
        return OPERATION_FAILED;
    }


    private function attemptCount($mobile) {

        $sql = "select attempt from otp_sms where mobile='$mobile'";
        $result = $this->conn->query($sql);
        if ($result) {

            $row = $result->fetch_object();
            if (count($row)) {
                $count = $row->attempt;
                return $count;
            }
        }
        return OPERATION_FAILED;
    }


    private function isMaxAttempt($mobile) {

        $sql = "SELECT `attempt` FROM `otp_sms` WHERE `mobile` = '$mobile'";
        $result = $this->conn->query($sql);
        if ($result) {

            $row = $result->fetch_object();
            if (count($row)) {
                $count = $row->attempt;
                if ($count >= 3) {
                    return TRUE;
                }
            }
            return FALSE;
        }
        return TRUE;
    }


    public function createOtp($mobile) {

        if ($this->deleteOtp($mobile) == OPERATION_SUCCESSFULL) {

            $otp = $this->generateOtp();
            $sql = $this->conn->prepare("insert into otp_sms (mobile, code, attempt)
	        values ( '".$this->conn->real_escape_string($mobile)."', '$otp', 1 )");

            $result = $sql->execute();
            $sql->close();
            if ($result) {
                return OPERATION_SUCCESSFULL;
                //return $this->sendVerificationSms($mobile, $otp);
            }
        }
        return OPERATION_FAILED;
    }


    public function resendOtp($mobile) {

        if (!$this->isMaxAttempt($mobile)) {

            $otp = $this->generateOtp();
            $count = $this->attemptCount($mobile) + 1;
            $sql = $this->conn->prepare("UPDATE `otp_sms` SET `code`= '$otp',
                  `attempt`= $count WHERE `mobile` = '$mobile'");

            $result = $sql->execute();
            $sql->close();
            if ($result) {
                return OPERATION_SUCCESSFULL;
                //return $this->sendVerificationSms($mobile, $otp);
            }
        }
        return OPERATION_FAILED;
    }


    private function sendVerificationSms($mobile, $otp) {

        $MessageBird = new\MessageBird\Client('YOUR_ACCESS_KEY'); // Set your own API access key here.

        $Message = new\MessageBird\Objects\Message();
        $Message->originator = 'UnifyProject';
        $Message->recipients = array($mobile);
        $Message->body = 'Verification code:'.$otp;

        try {
            $MessageResult = $MessageBird->messages->create($Message);
            var_dump($MessageResult);

        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            // That means that your accessKey is unknown
            //echo 'wrong login';
            return OPERATION_FAILED;

        } catch (\MessageBird\Exceptions\BalanceException $e) {
            // That means that you are out of credits, so do something about it.
            //echo 'no balance';
            return OPERATION_FAILED;

        } catch (\Exception $e) {
            //echo $e->getMessage();
            return OPERATION_FAILED;
        }
        return OPERATION_SUCCESSFULL;
    }


    public function verifyOtp($mobile, $otp) {

        $sql = $this->conn->prepare("select * from otp_sms where
               mobile = '".$this->conn->real_escape_string($mobile)."' and code = '".$this->conn->real_escape_string($otp)."'");

        $result = $sql->execute();
        $sql->close();
        if ($result) {
            if ($this->deleteOtp($mobile) == OPERATION_SUCCESSFULL) return $this->activateUser($mobile);
        }
        return OPERATION_FAILED;
    }


    private function activateUser($mobile) {
        $sql = $this->conn->prepare("update users set isActive = 1
        where mobile = '".$this->conn->real_escape_string($mobile)."'");

        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return $this->fetchUserDetailMobile($mobile);
        }
        return OPERATION_FAILED;
    }


    public function loginMobile($mobile, $password) {

        if ($this->authUserByMobileAndPassword($mobile, $password)) {
            $ssid = $this->generateSessionId();

            $sql = $this->conn->prepare("update users set sessionId = '$ssid'
               where mobile = '".$this->conn->real_escape_string($mobile)."'");

            $result = $sql->execute();
            $sql->close();

            if ($result) {
                return $this->fetchUserDetailMobile($mobile);
            }
        }
        return OPERATION_FAILED;
    }


    public function loginUsername($username, $password) {

        if ($this->authUserByUsernameAndPassword($username, $password)) {
            $ssid = $this->generateSessionId();

            $sql = $this->conn->prepare("update users set sessionId = '$ssid'
                 where username = '".$this->conn->real_escape_string($username)."'");

            $result = $sql->execute();
            $sql->close();

            if ($result) {
                return $this->fetchUserDetailUsername($username);
            }
        }
        return OPERATION_FAILED;
    }



    /**
     * Authenticate user by mobile and password
     * for authentication
     */
    private function authUserByMobileAndPassword($mobile, $password) {

        $encrypted_password = '';
        $salt = '';

        if ($this->isMobileExist($mobile)) {

            $sql = $this->conn->prepare("select encrypted_password, salt from users
			     where mobile = '".$this->conn->real_escape_string($mobile)."' and isActive = 1");

            $result = $sql->execute();

            if ($result) {

                $sql->bind_result($encrypted_password, $salt);
                $sql->store_result();

                if ($sql->num_rows > 0) {

                    $sql->fetch();
                    $sql->close();

                    if ($this->checkhashSSHA($password, $encrypted_password, $salt)) {
                        return TRUE;
                    }

                }
            }
        }
        return FALSE;
    }


    /**
     * Authenticate user by username and password
     * for authentication
     */
    private function authUserByUsernameAndPassword($username, $password) {

        $encrypted_password = '';
        $salt = '';

        if ($this->isUsernameExist($username)) {

            $sql = $this->conn->prepare("select encrypted_password, salt from users
			     where username = '".$this->conn->real_escape_string($username)."' and isActive = 1");

            $result = $sql->execute();

            if ($result) {

                $sql->bind_result($encrypted_password, $salt);
                $sql->store_result();

                if ($sql->num_rows > 0) {

                    $sql->fetch();
                    $sql->close();

                    if ($this->checkhashSSHA($password, $encrypted_password, $salt)) {
                        return TRUE;
                    }

                }
            }
        }
        return FALSE;
    }




    private function fetchUserDetailMobile($mobile) {

        $sql = $this->conn->prepare("select sessionId, username, mobile, fullname, location, course, level from users
			        where mobile = '".$this->conn->real_escape_string($mobile)."'");

        $result = $sql->execute();

        if ($result) {
            $ssid = '';
            $username = '';
            $mobile = '';
            $name = '';
            $location = '';
            $course = '';
            $level = '';
            //$user = $sql->get_result()->fetch_assoc();
            $sql->bind_result($ssid, $username, $mobile, $name, $location, $course, $level);
            $sql->store_result();

            if ($sql->num_rows > 0) {

                $sql->fetch();

                $user = array();
                $user["sessionId"] = $ssid;
                $user["username"] = $username;
                $user["mobile"] = $mobile;
                $user["name"] = $name;
                $user["location"] = $location;
                $user["course"] = $course;
                $user["level"] = $level;

                $sql->close();

                return $user;
            }
        }
        return OPERATION_FAILED;
    }


    private function fetchUserDetailUsername($username) {

        $sql = $this->conn->prepare("select sessionId, username, mobile, fullname, location, course, level from users
			        where username = '".$this->conn->real_escape_string($username)."'");

        $result = $sql->execute();
        if ($result) {

            $ssid = '';
            $username = '';
            $mobile = '';
            $name = '';
            $location = '';
            $course = '';
            $level = '';
            //$user = $sql->get_result()->fetch_assoc();
            $sql->bind_result($ssid, $username, $mobile, $name, $location, $course, $level);
            $sql->store_result();

            if ($sql->num_rows > 0) {

                $sql->fetch();

                $user = array();
                $user["sessionId"] = $ssid;
                $user["name"] = $name;
                $user["username"] = $username;
                $user["mobile"] = $mobile;
                $user["location"] = $location;
                $user["course"] = $course;
                $user["level"] = $level;

                $sql->close();

                return $user;
            }
        }
        return OPERATION_FAILED;
    }



    public function submitGcmToken($mobile, $gcmToken) {

        $sql = $this->conn->prepare("update users set gcmToken = '$gcmToken', where mobile = '$mobile'");
        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return OPERATION_SUCCESSFULL;
        }
        return OPERATION_FAILED;
    }



    public function changePassword($username, $oldPassword, $newPassword) {

        if ($this->authUserByUsernameAndPassword($username, $oldPassword)) {

            $hash = $this->hashSSHA($this->conn->real_escape_string($newPassword));
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt

            $sql = $this->conn->prepare("update users set encrypted_password = '$encrypted_password',
                salt = '$salt' where username = '$username'");

            $result = $sql->execute();
            $sql->close();

            if ($result) {
                return OPERATION_SUCCESSFULL;
            }
        }
        return OPERATION_FAILED;
    }


    public function recoverPasswordByMobile($mobile) {

        $newPassword = $this->generateOtp();

        $hash = $this->hashSSHA($this->conn->real_escape_string($newPassword));
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt

        $sql = $this->conn->prepare("update users set encrypted_password = '$encrypted_password',
        salt = '$salt' where mobile = '$mobile'");

        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return OPERATION_SUCCESSFULL; //sendRecoverPasswordSms($mobile, $newPassword);
        }
        return OPERATION_FAILED;
    }


    public function recoverPasswordByUsername($username) {

        $newPassword = $this->generateOtp();

        $hash = $this->hashSSHA($this->conn->real_escape_string($newPassword));
        $encrypted_password = $hash["encrypted"]; // encrypted password
        $salt = $hash["salt"]; // salt

        $sql = $this->conn->prepare("update users set encrypted_password = '$encrypted_password',
        salt = '$salt' where username = '$username'");

        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return OPERATION_SUCCESSFULL;
        }

        return OPERATION_FAILED;
    }

    private function sendRecoverPasswordSms($mobile, $newPassword) {

        $MessageBird = new\MessageBird\Client('YOUR_ACCESS_KEY'); // Set your own API access key here.

        $Message = new\MessageBird\Objects\Message();
        $Message->originator = 'UnifyProject';
        $Message->recipients = array($mobile);
        $Message->body = 'New Password:'.$newPassword;

        try {
            $MessageResult = $MessageBird->messages->create($Message);
            var_dump($MessageResult);

        } catch (\MessageBird\Exceptions\AuthenticateException $e) {
            // That means that your accessKey is unknown
            //echo 'wrong login';
            return OPERATION_FAILED;

        } catch (\MessageBird\Exceptions\BalanceException $e) {
            // That means that you are out of credits, so do something about it.
            //echo 'no balance';
            return OPERATION_FAILED;

        } catch (\Exception $e) {
            //echo $e->getMessage();
            return OPERATION_FAILED;
        }
        return OPERATION_SUCCESSFULL;
    }

    public function InitializeChangeNumber($username, $mobile, $password) {

        if ($this->authUserByUsernameAndPassword($username, $password)) {
            return $this->createOtp($mobile);
        }
        return OPERATION_FAILED;
    }


    public function changeNumber($username, $mobile) {

        $sql = $this->conn->prepare("update users set mobile = '".$this->conn->real_escape_string($mobile)."'
        where username = '$username'");

        $result = $sql->execute();
        $sql->close();

        if ($result) {
            return OPERATION_SUCCESSFULL;
        }

        return OPERATION_FAILED;
    }


    public function changeNumberVerifyOtp($username, $mobile, $otp) {

        $sql = $this->conn->prepare("select * from otp_sms where
               mobile = '".$this->conn->real_escape_string($mobile)."' and code = '".$this->conn->real_escape_string($otp)."'");

        $result = $sql->execute();
        $sql->close();
        if ($result) {
            if ($this->deleteOtp($mobile) == OPERATION_SUCCESSFULL) return $this->changeNumber($username, $mobile);
        }
        return OPERATION_FAILED;
    }


    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    private function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password.$salt, true).$salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }


    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    private function checkhashSSHA($password, $encrypted_password, $salt) {

        $hash = base64_encode(sha1($password.$salt, true).$salt);
        if (strcmp($hash, $encrypted_password) == 0) {
            return true;
        }
        return false;

    }


    private function generateSessionId() {
        return substr(md5(uniqid(mt_rand(), true)), 0, 16);
    }

    //Generates unique username
    private function generateUsername($fullname) {

        $uname = preg_replace('/\s+/', '', $fullname);
        if (strlen($uname) > 12) {
            $uname = strtok($fullname, " ");
            if (strlen($uname) > 12) {
                $uname = substr($uname, 0, 8);
            }
        }
        $uname = $uname.rand(001, 999);

        if ($this->isUsernameExist($uname)) $this->generateUsername($fullname);
        else return $uname;
    }

    //Generates OTP
    private function generateOtp() {
        $otp = substr(md5(uniqid(mt_rand(), true)), 0, 6);
        return $otp;
    }



}