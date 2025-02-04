<?php
    namespace App\Controllers\Api\v1;
    use App\Controllers\ApiController;
    use App\Models\Operation;
    use App\Models\Mailler;


    class Users extends ApiController{

        public function check_email_phone(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $type = $this->input_post("type"); // 1 for email, 2 for phone no
            $value = $this->input_post("check_value");
            if( (strlen($type) >0 )){
                if($type == 1){
                    if( (strlen($value) >0 )){
                        $checkData = $operation->get_data("users",'id,email',array("email"=>$value));
                        $returnSucMsg = "This email is available for registration";
                        $returnErrMsg = "This email already exist";
                    }else{
                        return $this->error_response("Sorry! Invalid Request!");
                    }
                }else if($type == 2){
                    if( (strlen($value) >0 )){
                        $checkData = $operation->get_data("users",'id,phone_no',array("phone_no"=>$value));
                        $returnSucMsg = "This phone no is available for registration";
                        $returnErrMsg = "This phone no already exist";
                    }else{
                        return $this->error_response("Sorry! Invalid Request!");
                    }
                }else if($type == 3){
                    $email = $this->input_post("email");
                    $phoneNo = $this->input_post("phone_no");
                    if( (strlen($email) >0 ) && (strlen($phoneNo) >0 )){
                        $otp = rand(100001,999999);
                        $returnArr = array(
                            "email_used" =>(string)0,
                            "phone_no_used" =>(string)0,
                            "otp"=>(string)$otp
                        );
                        $checkData = $operation->get_data("users",'id,email',array("email"=>$email));
                        if($checkData["num_rows"] > 0){
                            $returnArr["email_used"] = (string)1;
                            $checkData = $operation->get_data("users",'id,phone_no',array("phone_no"=>$phoneNo));
                            if($checkData["num_rows"] > 0){
                                $returnArr["phone_no_used"] = (string)1;
                                return $this->error_response("This email and phone no are already exist",$returnArr);
                            }else{
                                return $this->error_response("This email already exist!",$returnArr);
                            }
                        }else{
                            $returnArr["email_used"] = (string)0;
                            $checkData = $operation->get_data("users",'id,phone_no',array("phone_no"=>$phoneNo));
                            if($checkData["num_rows"] > 0){
                                $returnArr["phone_no_used"] = (string)1;
                                return $this->error_response("This phone no already exist!",$returnArr);
                            }else{
                                $variables = array();
                                $variables['OTP'] = $otp;
                                $emailTemplate = $operation->get_data("email_template","*",array("id"=>3));
                                $template=$emailTemplate["result"][0]->template;
                                foreach($variables as $key => $value){
                                    $template = str_replace('['.$key.']', $value, $template);
                                }
                                $subject = "otp Verification.";
                                $sentMail=$mailer->sent_email($email,$email,$subject,$template);
                                return $this->success_response("This email and phone no is available for registration",$returnArr);
                            }
                        }
                    }else{
                        return $this->error_response("Sorry! email and phone no Request!");
                    }
                }else{
                    return $this->error_response("Sorry! Invalid Request!");
                }
                if($checkData["num_rows"] > 0){
                    return $this->error_response($returnErrMsg);
                }else{
                    return $this->success_response($returnSucMsg);
                }
            }else{
                return $this->error_response("Sorry! type and check_value Request!");
            }
        }


        public function login() {
            $operation = new Operation();
            $loginType = $this->input_post("login_type"); // 1=normal, 2=gmail, 3 = fb, 4=twitter, 5=instagram 6=metamask
            $deviceType = $this->input_post("device_type"); // 1= web, 2= android, 3=iOS 
            $deviceToken = $this->input_post("device_token"); 
            $fcm_token = $this->input_post("fcm_token");
            if( (strlen($loginType) > 0) ){
                // =============== normal login section =============== //
                if($loginType == 1){
                    $phoneNo = $this->input_post("phone_no");
                    $email = $this->input_post("email");
                    $password = $this->input_post("password");
                    $emailOrPhoneNo = $this->input_post("email_or_phone");
                    if(strlen($emailOrPhoneNo) == 0){
                        $emailOrPhoneNo = 1;
                    }
                    if($emailOrPhoneNo == 1){
                        if( (strlen($phoneNo) > 0) && (strlen($password) > 0) ){
                            $getUserData = $operation->get_data("users",'*',array("phone_no"=>$phoneNo,"password"=>md5($password)));
                            if($getUserData["num_rows"] > 0){
                                if($getUserData["result"][0]->phone_verify == 1){
                                    $operation->update_data("users",array('id'=>$getUserData["result"][0]->id),array("fcm_token"=>$fcm_token));
                                    $getUserData = $this->user_details($getUserData["result"]);
                                    
                                    return $this->success_response("Welcome! Login successfully!",$getUserData);
                                }else{
                                    return $this->error_response("Sorry! This phone no is not verified!");
                                }
                                
                                // $checkEmailVerification = $getUserData["result"][0]->email_verify;
                                // if($checkEmailVerification == 0){
                                //     return $this->error_response("Sorry! Please verify your email address!");
                                // }else{
                                //     $getUserData = $this->user_details($getUserData["result"]);
                                //     return $this->success_response("Welcome! Login successfully!",$getUserData);
                                // }
                            }else{
                                return $this->error_response("Sorry! Invalid login credential!");
                            }
                        }else{
                            return $this->error_response("Sorry! For normal login, phone_no and password required!");
                        }
                    }else if($emailOrPhoneNo == 2){
                        if( (strlen($email) > 0) && (strlen($password) > 0) ){
                            $getUserData = $operation->get_data("users",'*',array("email"=>$email,"password"=>md5($password)));
                            if($getUserData["num_rows"] > 0){
                                if($getUserData["result"][0]->email_verify == 1){
                                    $operation->update_data("users",array('id'=>$getUserData["result"][0]->id),array("fcm_token"=>$fcm_token));
                                    $getUserData = $this->user_details($getUserData["result"]);
                                    
                                    return $this->success_response("Welcome! Login successfully!",$getUserData);
                                }else{
                                    return $this->error_response("Sorry! This email is not verified!");
                                }
                                
                            }else{
                                return $this->error_response("Sorry! Invalid login credential!");
                            }
                        }else{
                            return $this->error_response("Sorry! For normal login, email and password required!");
                        }
                        
                    }
                    
                }
                // =============== normal login section =============== //

                // =============== gmail login section =============== //
                if($loginType == 2){
                    $email = $this->input_post("email");
                    $socialId = $this->input_post("social_id");
                    if( (strlen($email) > 0) && (strlen($socialId) > 0) ){
                        $getUserData = $operation->get_data("users",'*',array("email"=>$email,"social_id"=>$socialId));
                        if($getUserData["num_rows"] > 0){
                            $getUserData = $this->user_details($getUserData["result"]);
                            return $this->success_response("Welcome! Login successfully!",$getUserData);
                        }else{
                            return $this->error_response("Sorry! Invalid login credential!");
                        }
                    }else{
                        return $this->error_response("Sorry! For gmail login, email and social_id required!");
                    }
                }
                // =============== gmail login section =============== //

                // =============== fb or twitter or instagram login section =============== //
                if( ($loginType == 3) || ($loginType == 4) || ($loginType == 5)){
                    $socialId = $this->input_post("social_id");
                    if( (strlen($socialId) > 0) ){
                        $getUserData = $operation->get_data("users",'*',array("social_id"=>$socialId));
                        if($getUserData["num_rows"] > 0){
                            $getUserData = $this->user_details($getUserData["result"]);
                            return $this->success_response("Welcome! Login successfully!",$getUserData);
                        }else{
                            return $this->error_response("Sorry! Invalid login credential!");
                        }
                    }else{
                        return $this->error_response("Sorry! For gmail login, email and social_id required!");
                    }
                }
                // =============== fb or twitter or instagram =============== //
            }else{
                return $this->error_response("Sorry! login_type required!");
            }
        }


        public function registration(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $loginType = $this->input_post("login_type"); // 1=normal, 2=gmail, 3 = fb, 4=twitter, 5=instagram 6=metamask
            $deviceType = $this->input_post("device_type"); // 1= web, 2= android, 3=iOS 
            $deviceToken = $this->input_post("device_token"); 
            $referral = $this->input_post("referral");
            $email_verify = $this->input_post("email_verify");
            $phone_verify = $this->input_post("phone_verify");
            $fcm_token = $this->input_post("fcm_token");
            if(strlen($phone_verify) == 0){
                $phone_verify = 0;
            }
            if(strlen($email_verify) == 0){
                $email_verify = 0;
            }

            if( (strlen($loginType) > 0) && (strlen($deviceType) > 0) && (strlen($deviceToken) > 0) ){
                if(strlen($referral) > 0 ){
                    $checkReferral = $operation->get_data("users",'id,referral_code',array("referral_code"=>$referral));
                    if($checkReferral["num_rows"] == 0){
                        return $this->error_response("Sorry! invalid referral code!");
                        exit();
                    }
                }
                // =============== normal registration section =============== //
                if($loginType == 1){
                    $name = $this->input_post("name");
                    // $dialingCode = $this->input_post("dialing_code");
                    $phoneNo = $this->input_post("phone_no");
                    $email = $this->input_post("email");
                    $password = $this->input_post("password");
                    if( (strlen($name) > 0) && (strlen($phoneNo) > 0) && (strlen($email) > 0) && (strlen($password) > 0) ){
                        if(is_numeric($phoneNo)){
                            $checkEmail = $operation->get_data("users",'id,email,status,email_verify,phone_no',array("email"=>$email));
                            if($checkEmail["num_rows"] == 0){
                                $checkPhoneNo = $operation->get_data("users",'id,email,phone_no',array("phone_no"=>$phoneNo));
                                if($checkPhoneNo["num_rows"] == 0){
                                    $code = 'LBT0000000';
                                    $lastCode = $operation->get_data("users",'id',array(),"id","DESC","1","0");
                                    if($lastCode["num_rows"]==1){
                                        $lastId = $lastCode["result"][0]->id;
                                        $currId = (int)$lastId+1;
                                        $WalletId = $code.$currId;
                                    }else{
                                        $WalletId = $code."1";
                                    }
        
                                    
        
                                    // $getRewardForNewUser = $operation->get_data("app_reward_settings",'*',array("id"=>5));
                                    
                                    if(strlen($referral) > 0 ){
                                        $is_referred = 1;
                                    }else{
                                        $is_referred = 0;
                                    }
                                    $chainCode = $this->generate_chain_net_code($referral);


                                    $dataToInsert = array(
                                        "name"=>$name,
                                        "email"=>$email,
                                        "password"=>md5($password),
                                        "wallet_id"=>$WalletId,
                                        "phone_no"=>$phoneNo,
                                        "login_type"=>1,
                                        "status"=>1,
                                        "email_verify"=>$email_verify,
                                        "phone_verify"=>$phone_verify,
                                        "wallet_point"=>0,
                                        "referral_code"=>"LBT-".$this->generateRandomString(),
                                        "fcm_token"=>$fcm_token,
                                        "is_referred"=>$is_referred,
                                        "chain_code"=>$chainCode
                                    );
        
                                    $userId = $operation->insert_data("users",$dataToInsert);
        
                                    
                                    
                                    if(!empty($referral)){
                                        $checkReferral = $operation->get_data("users",'id,referral_code',array("referral_code"=>$referral));
                                        if($checkReferral["num_rows"] > 0){
                                            $getReferredUser = $checkReferral["result"][0]->id;
                                            $dataForReferral = array(
                                                "user_id"=>$userId,
                                                "referred_by"=>$getReferredUser,
                                            );
                                            $operation->insert_data("user_referral",$dataForReferral);
                                        }else{
                                            return $this->error_response("Sorry! invalid referral code!");
                                        }
                                    }
                                    
        
                                    // $otp=rand("1000","9999");
                                    // $variables = array();
                                    // $variables['OTP'] = $otp;
                                    // $emailTemplate = $operation->get_data("email_template","*",array("id"=>3));
                                    // $template=$emailTemplate["result"][0]->template;
                                    // foreach($variables as $key => $value){
                                    //     $template = str_replace('['.$key.']', $value, $template);
                                    // }
                                    // $subject = "otp Verification.";
                                    // $sentMail=$mailer->sent_email($email,$name,$subject,$template);
        
                                    // $otpProcessId = rand("1000000000","9999990000");
                                    // $verificationInsert=array(
                                    //     "otp_process_id"=>$otpProcessId,
                                    //     "email"=>$email,
                                    //     "otp"=>$otp,
                                    //     "encrypted_id"=>$operation->encrypt_decrypt("encrypt",$WalletId),
                                    //     "type"=>1
                                    // );
                                    $getUserData = $operation->get_data("users",'*',array("id"=>$userId));
                                    $getUserData = $this->user_details($getUserData["result"]);
                                    return $this->success_response("Welcome! Login successfully!",$getUserData);
                                    // $otpVerification=$operation->insert_data("email_verification",$verificationInsert);
                                    // return $this->success_response("Welcome! Verification Email sent to your email, please check and verify",array("otp_process_id"=>(string)$otpProcessId));
        
                                }else{
                                    return $this->error_response("Sorry! this phone no is already registered with another account!");
                                }
                            }else{
                                return $this->error_response("Sorry! this email is already registered. Please login!");
                            }
                        }else{
                            return $this->error_response("Sorry! phone no should be number only!");
                        }
                    }else{
                        return $this->error_response("Sorry! name, phone no, email, password required!");
                    }

                }
                // =============== normal registration section =============== //

                // =============== gmail registration section =============== //
                if($loginType == 2){
                    $email = $this->input_post("email");
                    $socialLoginId = $this->input_post("social_login_id");
                    if( (strlen($email) > 0) && (strlen($socialLoginId) > 0) ){
                        $checkEmail = $operation->get_data("users",'id,email,status,email_verify,social_id',array("social_id"=>$socialLoginId));
                        if($checkEmail["num_rows"] == 0){
                            $code = 'UAE0000000';
                            $lastCode = $operation->get_data("users",'id',array(),"id","DESC","1","0");
                            if($lastCode["num_rows"]==1){
                                $lastId = $lastCode["result"][0]->id;
                                $currId = (int)$lastId+1;
                                $WalletId = $code.$currId;
                            }else{
                                $WalletId = $code."1";
                            }

                            $dataToInsert = array(
                                "email"=>$email,
                                "wallet_id"=>$WalletId,
                                "login_type"=>2,
                                "status"=>1,
                                "email_verify"=>1,
                                "phone_verify"=>0,
                                "social_id"=>$socialLoginId,
                                "wallet_point"=>1000
                            );

                            $userId = $operation->insert_data("users",$dataToInsert);

                            $transactionData = array(
                                "user_id"=>$userId,
                                "type"=>1,
                                "points"=>1000,
                                "details"=>"Registration Successfully"
                            );
                            $operation->insert_data("user_wallet_transaction",$transactionData);

                            $getUserData = $operation->get_data("users",'*',array("social_id"=>$socialLoginId));
                            $getUserData = $this->user_details($getUserData["result"]);
                            return $this->success_response("Welcome! Login successfully!",$getUserData);
                        }else{
                            return $this->error_response("Sorry! this account is already registered. Please login!");
                        }
                    }else{
                        return $this->error_response("Sorry! email, social_login_id required!");
                    }
                }
                // =============== gmail registration section =============== //

                // =============== fb or twitter or instagram registration section =============== //
                if( ($loginType == 3) || ($loginType == 4) || ($loginType == 5)){
                    $socialLoginId = $this->input_post("social_login_id");
                    if( (strlen($socialLoginId) > 0) ){
                        $checkEmail = $operation->get_data("users",'id,email,status,email_verify,social_id',array("social_id"=>$socialLoginId));
                        if($checkEmail["num_rows"] == 0){
                            $code = 'UAE0000000';
                            $lastCode = $operation->get_data("users",'id',array(),"id","DESC","1","0");
                            if($lastCode["num_rows"]==1){
                                $lastId = $lastCode["result"][0]->id;
                                $currId = (int)$lastId+1;
                                $WalletId = $code.$currId;
                            }else{
                                $WalletId = $code."1";
                            }

                            $dataToInsert = array(
                                "wallet_id"=>$WalletId,
                                "login_type"=>$loginType,
                                "status"=>1,
                                "email_verify"=>0,
                                "phone_verify"=>0,
                                "social_id"=>$socialLoginId,
                                "wallet_point"=>1000
                            );

                            $userId = $operation->insert_data("users",$dataToInsert);


                            $transactionData = array(
                                "user_id"=>$userId,
                                "type"=>1,
                                "points"=>1000,
                                "details"=>"Registration Successfully"
                            );
                            $operation->insert_data("user_wallet_transaction",$transactionData);

                            $getUserData = $operation->get_data("users",'*',array("social_id"=>$socialLoginId));
                            $getUserData = $this->user_details($getUserData["result"]);
                            return $this->success_response("Welcome! Login successfully!",$getUserData);
                        }else{
                            return $this->error_response("Sorry! this account is already registered. Please login!");
                        }
                    }else{
                        return $this->error_response("Sorry! social_login_id required!");
                    }
                }
                // =============== fb or twitter or instagram registration section =============== //

            }else{
                return $this->error_response("Sorry! login_type, device_type, device_token required!");
            }
        }


        protected function generate_chain_net_code($referralCode=""){
            // A-000001 ====== A-999999
            // A-000001-000001
            // A-000001-000001-000001
            // A-000001-000001-000001-000001
            // A-000001-000001-000001-000001-000005
            $operation = new Operation();

            if(strlen($referralCode) > 0){
                $getLastUser = $operation->get_data("users",'id,is_referred,referral_code,chain_code',array("referral_code"=>$referralCode),"id","DESC","1","0");
            }else{
                $getLastUser = $operation->get_data("users",'id,is_referred,referral_code,chain_code',array("is_referred"=>0),"chain_code","DESC","1","0");
            }
            if($getLastUser["num_rows"] == 0){
                if(strlen($referralCode) > 0){
                    $getLastUser = $operation->get_data("users",'id,is_referred,referral_code,chain_code',array("is_referred"=>0),"chain_code","DESC","1","0");
                    if($getLastUser["num_rows"] > 0){
                        $lastChainCode = $getLastUser["result"][0]->chain_code;
                        $lastChainCodeArr = explode("-",$lastChainCode);
                        $lastChainCodeNum = end($lastChainCodeArr);
                        $firstChainCodeNum = current($lastChainCodeArr);
                        $newLastChainCodeNum = (int)$lastChainCodeNum+1;
                        $newFirstChainCodeNum = $firstChainCodeNum;
                        $chainCode = $newFirstChainCodeNum."-".str_pad($newLastChainCodeNum, 6, '0', STR_PAD_LEFT);
                    }else{
                        $chainCode = "0A-000001";
                    }
                }else{
                    $chainCode = "0A-000001";
                }
            }else{
                $lastChainCode = $getLastUser["result"][0]->chain_code;
                if(empty($lastChainCode)){
                    $lastChainCode = "0A-000001";
                }
                $lastChainCodeArr = explode("-",$lastChainCode);
                $lastChainCodeLevelCount = count($lastChainCodeArr);
                $firstChainCodeNum = current($lastChainCodeArr);
                $lastChainCodeNum = end($lastChainCodeArr);
                

                if(strlen($referralCode) > 0){
                    $getLastUser = $operation->get_data("users",'id,is_referred,referral_code,chain_code',array(),"id","DESC","0","1",array("chain_code"=>$lastChainCode));
                    $lastNetworkCode = $getLastUser["result"][0]->chain_code;
                    $lastNetworkCodeArr = explode("-",$lastNetworkCode);
                    $lastNetworkLevelCount = count($lastNetworkCodeArr);
                    if($lastNetworkLevelCount > $lastChainCodeLevelCount){
                        $lastNetworkCodeLastNum = end($lastNetworkCodeArr);
                        $newNetworkCodeLastNum = (int)$lastNetworkCodeLastNum+1;
                        $chainCode = $lastChainCode."-".str_pad($newNetworkCodeLastNum, 6, '0', STR_PAD_LEFT);
                    }else{
                        $chainCode = $lastChainCode."-000001";
                    }
                }else{
                    $firstDigitOfCode = substr($firstChainCodeNum,0,1);
                    $lastDigitOfCode = substr($firstChainCodeNum,1,1);
                    if(($firstDigitOfCode == 0) && (ord($lastDigitOfCode) < 90)){
                        $newNetworkCodeFirstNum = "0".$lastDigitOfCode;
                        $newNetworkCodeLastNum = (int)$lastChainCodeNum+1;
                    }else if(($firstDigitOfCode == 0) && (ord($lastDigitOfCode) >= 90)){
                        $newNetworkCodeFirstNum = "AA";
                        $newNetworkCodeLastNum = "000001";
                    }else{
                        $newNetworkCodeFirstNum = chr(ord($firstDigitOfCode) + 1)."A";
                        $newNetworkCodeLastNum = "000001";
                    }
                    $chainCode = $newNetworkCodeFirstNum."-".str_pad($newNetworkCodeLastNum, 6, '0', STR_PAD_LEFT);
                }
            }
            return $chainCode;
        }

        function send_user_email_oto(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $userId = $this->input_post("user_id");
            if( (strlen($userId) >0 ) ){
                $checkEmail = $operation->get_data("users",'id,email,name,wallet_id',array("id"=>$userId));
                if($checkEmail["num_rows"] > 0){
                    $otp=rand("100000","999999");
                    $variables = array();
                    $variables['OTP'] = $otp;
                    $emailTemplate = $operation->get_data("email_template","*",array("id"=>3));
                    $template=$emailTemplate["result"][0]->template;
                    foreach($variables as $key => $value){
                        $template = str_replace('['.$key.']', $value, $template);
                    }
                    $subject = "otp Verification.";
                    $sentMail=$mailer->sent_email($checkEmail["result"][0]->email,$checkEmail["result"][0]->name,$subject,$template);
    
                    $otpProcessId = rand("1000000000","9999990000");
                    $verificationInsert=array(
                        "otp_process_id"=>$otpProcessId,
                        "email"=>$checkEmail["result"][0]->email,
                        "otp"=>$otp,
                        "encrypted_id"=>$operation->encrypt_decrypt("encrypt",$checkEmail["result"][0]->wallet_id),
                        "type"=>1
                    );
    
                    $otpVerification=$operation->insert_data("email_verification",$verificationInsert);
                    return $this->success_response("Welcome! Verification Email sent to your email, please check and verify",array("otp_process_id"=>(string)$otpProcessId));
                }else{
                    return $this->error_response("Sorry! invalid user!");
                }

                
            }else{
                return $this->error_response("Sorry! user_id required!");
            }
        }



        public function email_verification(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $otpProcessId = $this->input_post("otp_process_id");
            $otp = $this->input_post("otp");
            if( (strlen($otpProcessId) >0 ) && (strlen($otp) >0 )){
                $checkOTPData = $operation->get_data("email_verification",'*',array("otp_process_id"=>$otpProcessId));
                if($checkOTPData["num_rows"] > 0 ){
                    if($otp == $checkOTPData["result"][0]->otp){
                        // $updateProfile = $operation->update_data("users",array('email'=>$checkOTPData["result"][0]->email),array("email_verify"=>1,"profile_completion"=>25));

                        $this->db = \Config\Database::connect();
                        $builder = $this->db->table("users");
                        $builder->set('email_verify', 1);
                        $builder->set('profile_completion', 'profile_completion+25', FALSE);
                        $builder->where(array("email"=>$checkOTPData["result"][0]->email));
                        $updateProfile = $builder->update();


                        $operation->update_data("email_verification",array('id'=>$checkOTPData["result"][0]->id),array("status"=>0));
                        if($updateProfile){
                            return $this->success_response("Welcome! Email is verified. Please login");
                        }else{
                            return $this->error_response("Sorry! Internal Error !");
                        }
                    }else{
                        return $this->error_response("Sorry! Invalid OTP!");
                    }
                }else{
                    return $this->error_response("Sorry! Invalid Request!");
                }
            }else{
                return $this->error_response("Sorry! otp_process_id and otp required!");
            }
        }

        public function resend_otp(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $otpProcessId = $this->input_post("otp_process_id");

            if( (strlen($otpProcessId) >0 ) ){
                $checkOTPData = $operation->get_data("email_verification",'*',array("otp_process_id"=>$otpProcessId));
                if($checkOTPData["num_rows"] > 0){
                    
                    $otp=rand("1000","9999");
                    $variables = array();
                    $variables['OTP'] = $otp;
                    $emailTemplate = $operation->get_data("email_template","*",array("id"=>3));
                    $template=$emailTemplate["result"][0]->template;
                    foreach($variables as $key => $value){
                        $template = str_replace('['.$key.']', $value, $template);
                    }
                    $subject = "otp Verification.";
                    $email = $checkOTPData["result"][0]->email;
                    $name = "User";
                    $sentMail=$mailer->sent_email($email,$name,$subject,$template);

                    $otpProcessId = rand("1000000000","9999990000");
                    $verificationInsert=array(
                        "otp"=>$otp
                    );

                    $operation->update_data("email_verification",array("id"=>$checkOTPData["result"][0]->id),$verificationInsert);

                    return $this->success_response("OTP sended to your email. Please check");

                }else{
                    return $this->error_response("Sorry! Invalid Request!");
                }
            }else{
                return $this->error_response("Sorry! otp process id and otp required!");
            }
        }


        public function forgot_password(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $type = $this->input_post("type"); // 1 = phone, 2= email, 3 = security question 
            
            $emailOrPhone = $this->input_post("email_or_phone");
            if( (strlen($emailOrPhone) >0 ) ){
                $queryStr = '`email` = "'.$emailOrPhone.'" or `phone_no` = "'.$emailOrPhone.'"';
                $getUser = $operation->get_data("users",'id,email,name,phone_no,status,phone_verify,email_verify',array(),"","","","","","",$queryStr);
                if($getUser["num_rows"] > 0){
                    if($getUser["result"][0]->status == 1){
                        if($type == 1){
                            if($getUser["result"][0]->phone_verify == 1){
                                return $this->success_response("This phone no is verified");
                            }else{
                                return $this->error_response("Sorry! This phone no is not verified!");
                            }
                        }else if($type == 2){
                            if($getUser["result"][0]->email_verify == 1){
                                $otp=rand("00000","999999");
                                $otpProcessId = rand("1000000000","9999990000");
                                $variables = array();
                                $variables['OTP'] = $otp;
                                $emailTemplate = $operation->get_data("email_template","*",array("id"=>3));
                                $template=$emailTemplate["result"][0]->template;
                                foreach($variables as $key => $value){
                                    $template = str_replace('['.$key.']', $value, $template);
                                }
                                $subject = "Forgot Password.";
                                $sentMail=$mailer->sent_email($getUser["result"][0]->email,$getUser["result"][0]->name,$subject,$template);
                                $otpData = array(
                                    'user_id'=>$getUser["result"][0]->id,
                                    'email'=>$getUser["result"][0]->email,
                                    'phone_no'=>$getUser["result"][0]->phone_no,
                                    'otp'=>$otp,
                                    'otp_process_id'=>$otpProcessId
                                );
                                $otpVerification=$operation->insert_data("user_otp",$otpData);
                                return $this->success_response("Please check your email for the verification OTP",array("otp_process_id"=>(string)$otpProcessId,"otp"=>(string)$otp));
                            }else{
                                return $this->error_response("Sorry! This email is not verified!");
                            }
                        }else{
                            return $this->error_response("Sorry! This email is not verified!");
                        }
                    }else{
                        return $this->error_response("Sorry! Invalid request!");
                    }
                }else{
                    if($type == 1){
                        return $this->error_response("Sorry! This phone no is not registered with us!");
                    }else if($type == 2){
                        return $this->error_response("Sorry! This email is not registered with us!");
                    }else{
                        return $this->error_response("Sorry! Invalid request!");
                    }
                }
            }else{
                return $this->error_response("Sorry! email_or_phone required!");
            }
        }

        public function forgot_password_otp_verification(){
            $operation = new Operation();
            $otpProcessId = $this->input_post("otp_process_id");
            $otp = $this->input_post("otp");
            if( (strlen($otpProcessId) >0 ) && (strlen($otp) >0 )){
                $checkOtp = $operation->get_data("user_otp","*",array("otp_process_id"=>$otpProcessId,"otp"=>$otp,"status"=>0),"id","DESC");
                if($checkOtp["num_rows"] > 0){
                    $updateOTP = $operation->update_data("user_otp",array('id'=>$checkOtp["result"][0]->id),array("status"=>1));
                    return $this->success_response("Welcome! OTP verified. Please Set new password now");
                }else{
                    return $this->error_response("Sorry! invalid request!");
                }
            }else{
                return $this->error_response("Sorry! otp_process_id and otp required!");
            }
        }


        public function forgot_password_change_password(){
            $operation = new Operation();
            $otpProcessId = $this->input_post("otp_process_id");
            $password = $this->input_post("password");
            $withPhone = $this->input_post("with_phone");
            $phoneNo = $this->input_post("phone_no");
            if(strlen($withPhone) == 0){
                $withPhone = 0;
            }
            if($withPhone == 1){
                if( (strlen($password) >0 )){
                    $checkPhone = $operation->get_data("users","*",array("phone_no"=>$phoneNo),"id","DESC");
                    if($checkPhone["num_rows"] > 0){
                        if($checkPhone["result"][0]->phone_verify == 1){
                            $updatePassword = $operation->update_data("users",array('id'=>$checkPhone["result"][0]->id),array("password"=>md5($password)));
                            return $this->success_response("Password changed successfully");
                        }else{
                            return $this->error_response("Sorry! this phone no is not verified!");
                        }
                        
                    }else{
                        return $this->error_response("Sorry! invalid request!");
                    }
                }else{
                    return $this->error_response("Sorry! otp process id and password required!");
                }
            }else{
                if( (strlen($otpProcessId) >0 ) && (strlen($password) >0 )){
                    $checkOtp = $operation->get_data("user_otp","*",array("otp_process_id"=>$otpProcessId),"id","DESC");
                    if($checkOtp["num_rows"] > 0){
                        $checkPhone = $operation->get_data("users","*",array("id"=>$checkOtp["result"][0]->user_id),"id","DESC");
                        if($checkPhone["result"][0]->email_verify == 1){
                            $updatePassword = $operation->update_data("users",array('id'=>$checkOtp["result"][0]->user_id),array("password"=>md5($password)));
                            return $this->success_response("Password changed successfully");
                        }else{
                            return $this->error_response("Sorry! invalid request!");
                        }
                    }else{
                        return $this->error_response("Sorry! invalid request!");
                    }
                }else{
                    return $this->error_response("Sorry! otp process id and password required!");
                }
            }
            
        }

        public function user_profile(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            if( (strlen($userId) >0 ) ){
                $getUserData = $operation->get_data("users",'*',array("id"=>$userId));
                if($getUserData["num_rows"] > 0){
                    $userDetails = $this->user_details($getUserData["result"]);
                    $kycData =  $operation->get_data("user_kyc",'*',array("user_id"=>$userId));
                    if($kycData["num_rows"]>0){
                        $userDetails["kyc_image"] = $kycData["result"][0]->document;
                    }else{
                        $userDetails["kyc_image"] = "";
                    }
                    return $this->success_response("User Profile!",$userDetails);
                }else{
                    return $this->error_response("Sorry! Invalid login credential!");
                }
            }else{
                return $this->error_response("Sorry! user_id required!");
            }
        }

        public function edit_profile(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $name = $this->input_post("name");
            $dob = $this->input_post("dob");
            $gender = $this->input_post("gender");
            $image = $this->input_file("image");
            if( (strlen($userId) >0 ) ){
                $updateData = array();
                if(!empty($image)){
                    if(  $image->getSize() != 0 ){
                        $newFileName = md5(time()).'-'.$image->getRandomName();
                        $uploadFileName = "lbt-user-".date("Y-m-d")."-".$newFileName;
                        if (! $image->isValid()) {
                            return $this->error_response("Sorry!".$image->getErrorString());
                        }else{
                            $targetDir = APIASSETSPATH."user_image/";
                            $uploadImage = $image->move($targetDir,$uploadFileName);
                            $updateData["image"] = BASEPATH."public/files/user_image/".$uploadFileName;
                        }
                    }
                }
                
                if((strlen($name) > 0)){
                    $updateData["name"] = $name;
                }
                if((strlen($dob) > 0)){
                    $updateData["dob"] = date("Y-m-d",strtotime($dob));
                }
                if((strlen($gender) > 0)){
                    $updateData["gender"] = $gender;
                }
    
                $updateUser = $operation->update_data("users",array("id"=>$userId),$updateData);
                return $this->success_response("Profile updated successfully!");
            }else{
                return $this->error_response("Sorry! user_id required!");
            }
        }


        public function change_password(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $password = $this->input_post("password");
            $confirmPassword = $this->input_post("confirm_password");
            if( (strlen($userId) >0 ) && (strlen($password) >0 ) && (strlen($confirmPassword) >0 )){
                if($password == $confirmPassword){
                    $checkUserLogin = $operation->get_data("users",'id,login_type',array("id"=>$userId));
                    if($checkUserLogin["num_rows"] > 0){
                        if($checkUserLogin["result"][0]->login_type == 1){
                            $updateUser = $operation->update_data("users",array("id"=>$userId),array("password"=>md5($password)));
                            if($updateUser){
                                return $this->success_response("Changed password successfully!");
                            }else{
                                return $this->error_response("Sorry! This is old password!");
                            }
                        }else{
                            return $this->error_response("Sorry! User logged in with social login!");
                        }
                    }else{
                        return $this->error_response("Sorry! Invalid user!");
                    }
                }else{
                    return $this->error_response("Sorry! password and confirm_password should be same!");
                }
            }else{
                return $this->error_response("Sorry! user_id,password and confirm_password required!");
            }
        }


        public function upload_kyc(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $documentType = $this->input_post("document_type");
            $image = $this->input_file("image");
            if( (strlen($userId) >0 ) && (strlen($userId) >0 ) && (!empty($image))){
                $updateKycData = array();
                $updateKycData["user_id"] = $userId;
                $updateKycData["document_type"] = $documentType;
                $newFileName = md5(time()).'-'.$image->getRandomName();
                $uploadFileName = "lbt-kyc-".date("Y-m-d")."-".$newFileName;
                if ($image->getSize() != 0) {
                    $targetDir = APIASSETSPATH."kyc/";
                    $uploadImage = $image->move($targetDir,$uploadFileName);
                    $updateKycData["document"] = BASEPATH."public/files/kyc/".$uploadFileName;
                    $updateKyc = $operation->insert_data("user_kyc",$updateKycData);
                    if($updateKyc){
                        return $this->success_response("KYC updated successfully!");
                    }else{
                        return $this->error_response("Sorry! Invalid login credential!");
                    }
                }else{
                    return $this->error_response("Sorry!".$image->getErrorString());
                }
            }else{
                return $this->error_response("Sorry! user_id, document_type and image required!");
            }

        }

        public function verify_phone(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $isVerified = $this->input_post("is_verified");
            if( (strlen($userId)>0) && (strlen($isVerified)>0)){
                if($isVerified == 1){

                    $this->db = \Config\Database::connect();
                    $builder = $this->db->table("users");
                    $builder->set('phone_verify', $isVerified);
                    $builder->set('profile_completion', 'profile_completion+25', FALSE);
                    $builder->where(array("id"=>$userId));
                    $builder->update();

                    return $this->success_response("Phone no verified successfully!");
                }else if($isVerified == 0){
                    $operation->update_data("users",array("id"=>$userId),array("phone_verify"=>$isVerified));
                    return $this->success_response("Phone no un verified successfully!");
                }else{
                    return $this->error_response("Sorry! Invalid required!");
                }
            }else{
                return $this->error_response("Sorry! user_id and is_verified required!");
            }
        }


        public function add_family_member(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $name = $this->input_post("name");
            $email = $this->input_post("email");
            $dialingCode = $this->input_post("dialing_code");
            $phoneNo = $this->input_post("phone_no");
            $gender = $this->input_post("gender");
            $dob = $this->input_post("dob");
            $relationId = $this->input_post("relation_id");
            $image = $this->input_file("image");

            if( (strlen($userId) >0 ) && (strlen($name) >0 ) && (strlen($email) >0 ) && (strlen($dialingCode) >0 )  && (strlen($phoneNo) >0 )  && (strlen($gender) >0 ) && (strlen($dob) >0 )  && (strlen($relationId) >0 )){
                $insertData = array();
                $insertData["user_id"] = $userId;
                $insertData["name"] = $name;
                $insertData["email"] = $email;
                $insertData["dialing_code"] = $dialingCode;
                $insertData["phone_no"] = $phoneNo;
                $insertData["gender"] = $gender;
                $insertData["dob"] = date("Y-m-d",strtotime($dob));
                $insertData["relation_id"] = $relationId;
                $geRelationData = $operation->get_data("app_family_relation",'*',array("id"=>$relationId));
                if($geRelationData["num_rows"] > 0){
                    $insertData["relation"] = $geRelationData["result"][0]->name;
                    if(!empty($image)){
                        if(  $image->getSize() != 0 ){
                            $newFileName = md5(time()).'-'.$image->getRandomName();
                            $uploadFileName = "lbt-user-".date("Y-m-d")."-".$newFileName;
                            if (! $image->isValid()) {
                                return $this->error_response("Sorry!".$image->getErrorString());
                            }else{
                                $targetDir = APIASSETSPATH."user_family_image/";
                                $uploadImage = $image->move($targetDir,$uploadFileName);
                                $insertData["image"] = BASEPATH."public/files/user_family_image/".$uploadFileName;
                            }
                        }
                    }
                    
                    $addFamily = $operation->insert_data("user_family_members",$insertData);
                    if($addFamily){
                        return $this->success_response("Family member added successfully!");
                    }else{
                        return $this->error_response("Sorry! Try again!");
                    }
                }else{
                    return $this->error_response("Sorry! relation_id is not valid!");
                }
            }else{
                return $this->error_response("Sorry! user_id, name, email, dialing_code, phone_no, gender, dob  and relation_id required!");
            }
        }

        public function family_members(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            $memberId = $this->input_get("member_id");
            if( (strlen($userId) >0 ) ){
                if((strlen($memberId) >0 )){
                    $geMemberData = $operation->get_data("user_family_members",'*',array("id"=>$memberId));
                    if($geMemberData["num_rows"] > 0){
                        return $this->success_response("Family member!",$geMemberData["result"][0]);
                    }else{
                        return $this->error_response("Sorry! No member found!");
                    }
                }else{
                    $geMemberData = $operation->get_data("user_family_members",'*',array("user_id"=>$userId));
                    if($geMemberData["num_rows"] > 0){
                        return $this->success_response("Family member!",$geMemberData["result"]);
                    }else{
                        return $this->error_response("Sorry! No member found!");
                    }
                }
            }else{
                return $this->error_response("Sorry! user_id is required!");
            }
        }


        public function wallet_history(){
            $operation = new Operation();
            date_default_timezone_set('Asia/Kolkata');
            $userId = $this->input_get("user_id");
            $type = $this->input_get("type");
            if( (strlen($userId) >0 ) ){
                $searchCondition = array("user_id"=>$userId);
                if(!empty($type)){
                    $searchCondition = array_merge($searchCondition,array("type"=>$type));
                }
                $getUserData = $operation->get_data("users",'id,wallet_point',array("id"=>$userId));
                $getTransaction = $operation->get_data("user_wallet_transaction",'*',$searchCondition);
                if($getTransaction["num_rows"] > 0){
                    $returnArr = array();
                    $getTransactionRowCount = 0;
                    foreach($getTransaction["result"] as $getTransactionRow){
                        $returnArr[$getTransactionRowCount]["id"] = $getTransactionRow->id;
                        $returnArr[$getTransactionRowCount]["type"] = $getTransactionRow->type;
                        $returnArr[$getTransactionRowCount]["points"] = $getTransactionRow->points;
                        $returnArr[$getTransactionRowCount]["details"] = $getTransactionRow->details;
                        $returnArr[$getTransactionRowCount]["added_on_date"] = date("M d Y",strtotime($getTransactionRow->added_on));
                        $returnArr[$getTransactionRowCount]["added_on_time"] = date("H:s",strtotime($getTransactionRow->added_on));
                        $getTransactionRowCount++;
                    }
                    $infoData = array(
                        "wallet_point"=>$getUserData["result"][0]->wallet_point
                    );
                    return $this->success_response("Wallet transaction!",$returnArr,array(),$infoData);
                }else{
                    return $this->error_response("Sorry! No transaction record found!");
                }
            }else{
                return $this->error_response("Sorry! user_id is required!");
            }
        }

        protected function user_details($getUserData){
            $userData = array();
            foreach($getUserData as $getUserDataRow){
                if($getUserDataRow->gender == 1){
                    $gender = "male";
                }else if($getUserDataRow->gender == 2){
                    $gender = "female";
                }else{
                    $gender = "";
                }
                if(!empty($getUserDataRow->dob)){
                    $dob = date("d M Y",strtotime($getUserDataRow->dob));
                }else{
                    $dob = "";
                }
                $userData = array(
                    "account_type" => (string)1,
                    "id" => $getUserDataRow->id,
                    "name" => $getUserDataRow->name,
                    "email" => $getUserDataRow->email,
                    "phone_no" => $getUserDataRow->phone_no,
                    "address" => $getUserDataRow->address,
                    "wallet_id" => $getUserDataRow->wallet_id,
                    "wallet_point" => $getUserDataRow->wallet_point,
                    "crypto_wallet" => $getUserDataRow->crypto_wallet,
                    "login_type" => $getUserDataRow->login_type,
                    "device_type" => $getUserDataRow->device_type,
                    "social_id" => $getUserDataRow->social_id,
                    "referral_code" => $getUserDataRow->referral_code,
                    "phone_verify" => $getUserDataRow->phone_verify,
                    "email_verify" => $getUserDataRow->email_verify,
                    "image" => $getUserDataRow->image,
                    "profile_completion"=> $getUserDataRow->profile_completion,
                    "security_question_answered" => $getUserDataRow->security_question,
                    "dob" => $dob,
                    "gender"=> $gender,
                    "height" => $getUserDataRow->height,
                    "width" => $getUserDataRow->width,
                    "member_since"=> date("Y",strtotime($getUserDataRow->added_on)),
                    "kyc_verify"=>$getUserDataRow->kyc_verify,
                    "walk_reward_point"=>"0.01"
                );
            }
            return $userData;
        }
        
        protected function generateRandomString($length = 6) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }

        //----------- **** Daily Checkin Start ********** //

        public function user_daily_checkin(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $currentDate = $this->input_post("date");
            if(strlen($currentDate) == 0){
                $currentDate = date("Y-m-d");
            }
            // date("Y-m-d");
            // strtotime($this->input_get("current_date"));
            if(strlen($userId) > 0){
                $dailyCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId,"dateon"=>$currentDate));
                if($dailyCheckIn["num_rows"] > 0){
                    return $this->error_response("Sorry! invalid request! Daily Checkin Already Added!");
                }else{
                    $checkUsersCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId),"date_seq","DESC");
                    if($checkUsersCheckIn["num_rows"] > 0){
                        $lastSeq = $checkUsersCheckIn["result"][0]->date_seq;
                        $lastAddedDate = $checkUsersCheckIn["result"][0]->dateon;
                    }else{
                        $lastSeq = 0;
                        $lastAddedDate = date("Y-m-d", time() - 86400);;
                    }
                    $dateDiff = date_diff(date_create($currentDate),date_create($lastAddedDate));
                    $dateDiff = $dateDiff->format('%d');
                    if( ($dateDiff > 1) && ($dateDiff < 8)){
                        $operation->delete_data("user_daily_check_in",array("user_id"=>$userId));
                        $newSeq = 1;
                        $dataToAdd = array(
                            "user_id"=>$userId,
                            "dateon"=>$currentDate,
                            "date_seq"=>1
                        );
                        $operation->insert_data("user_daily_check_in",$dataToAdd);
                    }else if(($dateDiff == 1) && ($lastSeq >= 8)){
                        $operation->delete_data("user_daily_check_in",array("user_id"=>$userId));
                        $newSeq = 1;
                        $dataToAdd = array(
                            "user_id"=>$userId,
                            "dateon"=>$currentDate,
                            "date_seq"=>1
                        );
                        $operation->insert_data("user_daily_check_in",$dataToAdd);
                    }else if(($dateDiff == 1) && ($lastSeq <= 7)){
                        $newSeq = $lastSeq+1;
                        $dataToAdd = array(
                            "user_id"=>$userId,
                            "dateon"=>$currentDate,
                            "date_seq"=>$newSeq
                        );
                        $operation->insert_data("user_daily_check_in",$dataToAdd);
                    }else if(($dateDiff == 1) && ($lastSeq == 7)){
                        $operation->delete_data("user_daily_check_in",array("user_id"=>$userId));
                        $newSeq = 1;
                        $dataToAdd = array(
                            "user_id"=>$userId,
                            "dateon"=>$currentDate,
                            "date_seq"=>1
                        );
                        $operation->insert_data("user_daily_check_in",$dataToAdd);
                    }
        
                    if($newSeq == 1){
                        $pointId = 8;
                    }else if($newSeq == 2){
                        $pointId = 9;
                    }else if($newSeq == 3){
                        $pointId = 10;
                    }else if($newSeq == 4){
                        $pointId = 11;
                    }else if($newSeq == 5){
                        $pointId = 12;
                    }else if($newSeq == 6){
                        $pointId = 13;
                    }else if($newSeq == 7){
                        $pointId = 14;
                    }else{
                        $pointId = 8;
                    }
                    $getPoint = $operation->get_data("app_reward_settings",'*',array("id"=>$pointId),"id","DESC");
        
                    $transactionData = array(
                        "user_id"=>$userId,
                        "type"=>1,
                        "points"=>$getPoint["result"][0]->required_point,
                        "details"=>$getPoint["result"][0]->point_name
                    );
                    $operation->insert_data("user_wallet_transaction",$transactionData);


                    $todayCheckIn="0";

                    $updatedUsersCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId),"date_seq","ASC");
                    $day_checkin=array();
                    for($chkIndex = 1; $chkIndex<=7;$chkIndex++){
                        $curIndexVal = "day_".$chkIndex;
                        $day_checkin[$curIndexVal] = "0";
                    }
                    if($updatedUsersCheckIn["num_rows"] > 0){
                        $updatedUsersCheckInRowCount = 1;
                        foreach($updatedUsersCheckIn["result"] as $updatedUsersCheckInRow){
                            if($updatedUsersCheckInRow->date_seq >= 1){
                                $curIndexVal = "day_".$updatedUsersCheckInRowCount;
                                $day_checkin[$curIndexVal] = "1";
                                if($updatedUsersCheckInRow->dateon == date("Y-m-d")){
                                    $todayCheckIn = "1";
                                }
                            }
                            $updatedUsersCheckInRowCount++;
                        }
                    }

                    $returnArr['today_checkin']=$todayCheckIn;
                    $returnArr['checkin_history']=$day_checkin;
                    return $this->success_response("Daily claim updated!",$returnArr);
                }
            }else{
                return $this->error_response("Sorry! user id Request!");
            }
        }


        public function user_daily_checkin_history(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            if(strlen($userId) > 0){
                $updatedUsersCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId),"date_seq","ASC");
                $returnArr = array();
                $updatedUsersCheckInRowCount = 0;
                if($updatedUsersCheckIn["num_rows"] > 0){
                    foreach($updatedUsersCheckIn["result"] as $updatedUsersCheckInRow){
                        $returnArr[$updatedUsersCheckInRowCount]["date"] = $updatedUsersCheckInRow->dateon;
                        $returnArr[$updatedUsersCheckInRowCount]["sequence"] = $updatedUsersCheckInRow->date_seq;
                        $returnArr[$updatedUsersCheckInRowCount]["status"] = (string)1;
                        $updatedUsersCheckInRowCount++;
                    }
                    if($updatedUsersCheckInRowCount < 7){
                        for($index=$updatedUsersCheckInRowCount;$index<7;$index++){
                            $returnArr[$index]["date"] = "";
                            $returnArr[$index]["sequence"] = "";
                            $returnArr[$index]["status"] = (string)0;
                        }
                    }
                }else{
                    for($index=0;$index<7;$index++){
                        $returnArr[$index]["date"] = "";
                        $returnArr[$index]["sequence"] = "";
                        $returnArr[$index]["status"] = (string)0;
                    }
                }
                return $this->success_response("Daily claim history!",$returnArr);
            }else{
                return $this->error_response("Sorry! user id Request!");
            }
        }
        //----------- **** Daily Checkin End ********** //

        public function user_dashboard(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            $latitude = $this->input_get("latitude");
            $longitude = $this->input_get("longitude");
            $currentdate=$this->input_get("currentdate");
            if(strlen($userId) > 0){
                $getUserData = $operation->get_data("users",'id,name,email,image,profile_completion,phone_verify,email_verify,wallet_id,wallet_point,kyc_verify',array("id"=>$userId));
                if($getUserData["num_rows"] > 0){
                    $returnArr = array();

                    // ========== users details ========== //
                    $userDetails['name'] = $getUserData["result"][0]->name;
                    $userDetails['email'] = $getUserData["result"][0]->email;
                    if(empty($getUserData["result"][0]->image)){
                        $userDetails['image'] = "https://projectliberte.io/lbt/public/images/default-user.jpg";
                    }else{
                        $userDetails['image'] = $getUserData["result"][0]->image;
                    }
                    
                    $userDetails['profile_completion'] = $getUserData["result"][0]->profile_completion;
                    $userDetails['phone_verify'] = $getUserData["result"][0]->phone_verify;
                    $userDetails['email_verify'] = $getUserData["result"][0]->email_verify;
                    $userDetails['wallet_id'] = $getUserData["result"][0]->wallet_id;
                    $userDetails['wallet_point'] = $getUserData["result"][0]->wallet_point;
                    // ========== users details ========== //

                    // ========== weather details ========== //
                    $userWeather = $this->get_user_weather($userId,$latitude,$longitude);
                    $weatherDetails["mode"] = $userWeather["mode"];
                    $weatherDetails["temperature"] = $userWeather["temperature"];
                    $weatherDetails["icon"] = $userWeather["icon"];
                    $weatherDetails["unit"] = $userWeather["unit"];
                    // ========== weather details ========== //

                    $userDetails["section_order"] = array(
                        "weather_service",
                        "banner_top_banner",
                        "kyc",
                        "invite_friends",
                        "banner_ad_banner_slider",
                        "banner_ad_banner_single",
                        "daily_reward",
                        "wank_earn_details",
                        "banner_insurance_banner",
                        "gift_card",
                        "social_network_link",
                        "investment",
                        "instagram_post",
                        
                    );

                    // ========== service details ========== //
                    $serviceList = $operation->get_data("service",'*',array("status"=>1),"sort_order","ASC");
                    $serviceDetails = array();
                    if($serviceList["num_rows"] > 0){
                        $serviceDetailsCount = 0;
                        foreach($serviceList["result"] as $serviceListRow){
                            $serviceDetails[$serviceDetailsCount]["title"] = $serviceListRow->name;
                            $serviceDetails[$serviceDetailsCount]["image"] = $serviceListRow->icon;
                            $serviceDetails[$serviceDetailsCount]["is_premium"] = $serviceListRow->is_premium;
                            $serviceDetails[$serviceDetailsCount]["external_link"] = $serviceListRow->external_link;
                            $serviceDetails[$serviceDetailsCount]["internal_link_mode"] = $serviceListRow->internal_link_mode;
                            $serviceDetailsCount++;
                        }
                    }
                    // ========== service details ========== //


                    // ========== kyc details ========== //
                    if($getUserData["result"][0]->kyc_verify == 0){
                        $kycModuleDetails = $operation->get_data("app_module",'*',array("id"=>4));
                        $kycDetails["kyc_verified"] = "0";
                        $kycDetails["section_details"]["title"] = $kycModuleDetails["result"][0]->title;
                        $kycDetails["section_details"]["sub_title"] = $kycModuleDetails["result"][0]->sub_title;
                        $kycDetails["section_details"]["image"] = $kycModuleDetails["result"][0]->image;
                        $kycDetails["section_details"]["external_link"] = $kycModuleDetails["result"][0]->external_link;
                        $kycDetails["section_details"]["internal_link_mode"] = $kycModuleDetails["result"][0]->internal_link_module;
                        $kycDetails["section_details"]["button_text"] = $kycModuleDetails["result"][0]->button_text;
                    }else{
                        $kycDetails["kyc_verified"] = "1";
                    }
                    // ========== kyc details ========== //

                    // ========== banner details ========== //
                    $bannerType = $operation->get_data("banner_type",'*',array("status"=>1),"id","ASC");
                    $bannerDetails = array();
                    
                    foreach($bannerType["result"] as $bannerTypeRow){
                        $bannerDetails[$bannerTypeRow->name] = array();
                        $bannerDetailsArr = $operation->get_data("banner",'*',array("type"=>$bannerTypeRow->id),"id","ASC");
                        if($bannerDetailsArr["num_rows"] > 0){
                            $bannerDetailsCount = 0;
                            foreach($bannerDetailsArr["result"] as $bannerDetailsRow){
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["name"] = $bannerDetailsRow->name;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["type"] = $bannerDetailsRow->type;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["title"] = $bannerDetailsRow->title;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["sub_title"] = $bannerDetailsRow->sub_title;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["sub_title_1"] = $bannerDetailsRow->sub_title_1;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["image"] = $bannerDetailsRow->image;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["external_link"] = $bannerDetailsRow->external_link;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["internal_link_mode"] = $bannerDetailsRow->internal_link_mode;
                                $bannerDetails[$bannerTypeRow->name][$bannerDetailsCount]["button_text"] = $bannerDetailsRow->button_text;
                                $bannerDetailsCount++;
                            }
                        }
                    }
                    // ========== banner details ========== //

                    // ==== ******* ### Daily Check In Start - Written By Arnab ### ******** ========== //

                    $today=strtotime($currentdate); // Find Today Date
                   
                    $todayCheckIn="0";

                    $updatedUsersCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId),"date_seq","ASC");
                    $day_checkin=array();
                    for($chkIndex = 1; $chkIndex<=7;$chkIndex++){
                        $curIndexVal = "day_".$chkIndex;
                        $day_checkin[$curIndexVal] = "0";
                    }
                    if($updatedUsersCheckIn["num_rows"] > 0){
                        $updatedUsersCheckInRowCount = 1;
                        foreach($updatedUsersCheckIn["result"] as $updatedUsersCheckInRow){
                            if($updatedUsersCheckInRow->date_seq >= 1){
                                $curIndexVal = "day_".$updatedUsersCheckInRowCount;
                                $day_checkin[$curIndexVal] = "1";
                                if($updatedUsersCheckInRow->dateon == date("Y-m-d")){
                                    $todayCheckIn = "1";
                                }
                            }
                            $updatedUsersCheckInRowCount++;
                        }
                    }


                    // $dailyCheckIn = $operation->get_data("user_daily_check_in",'*',array("user_id"=>$userId),'id','DESC','1');
                    // $dailyCheckInDetails = array();
                    
                    // $total_days=7;
                    
                    // if($dailyCheckIn["num_rows"] > 0)
                    // {
                    //    $get_date=strtotime($dailyCheckIn["result"][0]->dateon);
                    //    $get_seq=$dailyCheckIn["result"][0]->date_seq;

                       
                    //     if($get_date==$today)
                    //     {
                    //         $checkin="1";

                    //         if($get_seq==$total_days)
                    //         {
                    //             $max_checked=0;
                    //         }
                    //         else
                    //         {
                    //             $max_checked=$get_seq;
                    //         }
                    //     }
                    //     else if($get_date==$lastday)
                    //     {
                    //         if($get_seq==$total_days)
                    //         {
                    //             $max_checked=0;
                    //         }
                    //         else
                    //         {
                    //             $max_checked=$get_seq;
                    //         }
                    //     }
                    //     else
                    //     {
                    //         $max_checked=0;
                    //     }
                       
                    // }

                   
                    // for($i=0;$i<$total_days;$i++)
                    // {
                    //     $j=$i+1;
                    //     if($max_checked==0)
                    //     {
                    //         $day_checkin['day_'.$j]="0";
                    //     }
                    //     else
                    //     {
                    //         if(($max_checked>$j) || ($max_checked==$j))
                    //         {
                    //             $day_checkin['day_'.$j]="1";
                    //         }
                    //         else
                    //         {
                    //             $day_checkin['day_'.$j]="0";
                    //         }
                    //     }
                    // }
                   
                    $checkingdata=array();

                    $checkingdata['today_checkin']=$todayCheckIn;
                    $checkingdata['checkin_history']=$day_checkin;

                    // ========== daily claim End - Written By Arnab ========== //

                    // ========== daily reward ========== //
                    $dailyReward = $operation->get_data("daily_reward_settings",'*',array("status"=>1));
                    $rewardDetails = array();
                    $rewardDetails["settings"]["title"] = $dailyReward["result"][0]->title;
                    $rewardDetails["settings"]["sub_title"] = $dailyReward["result"][0]->title;
                    $rewardDetails["settings"]["sub_title_one"] = $dailyReward["result"][0]->sub_title_1;
                    $rewardDetails["settings"]["image"] = $dailyReward["result"][0]->image;
                    $dailyRewardList = $operation->get_data("daily_rewards",'*',array("status"=>1),"id","ASC");
                    $dailyRewardListCount = 0;
                    $rewardDetailsArr = array();
                    foreach($dailyRewardList["result"] as $dailyRewardListRow){
                        $rewardDetailsArr[$dailyRewardListCount]["name"] = $dailyRewardListRow->name;
                        $rewardDetailsArr[$dailyRewardListCount]["image"] = $dailyRewardListRow->image;
                        $rewardDetailsArr[$dailyRewardListCount]["daily_limit"] = $dailyRewardListRow->daily_limit;
                        $rewardDetailsArr[$dailyRewardListCount]["time_duration"] = $dailyRewardListRow->time_duration;
                        $rewardDetailsArr[$dailyRewardListCount]["time_duration"] = $dailyRewardListRow->external_link;
                        $rewardDetailsArr[$dailyRewardListCount]["internal_link_mode"] = $dailyRewardListRow->internal_link_module;
                        $rewardDetailsArr[$dailyRewardListCount]["is_premium"] = $dailyRewardListRow->is_premium;
                        $dailyRewardListCount++;
                    }
                    $rewardDetails["list"] = $rewardDetailsArr;
                    // ========== daily reward ========== //


                    // ========== gift card reward ========== //
                    $giftCard = $operation->get_data("gift_card",'*',array("status"=>1));
                    $giftCardArr = array();
                    $giftCardCount = 0;
                    foreach($giftCard["result"] as $giftCardRow){
                        $giftCardArr[$giftCardCount]["title"] = $giftCardRow->title;
                        $giftCardArr[$giftCardCount]["image"] = $giftCardRow->image;
                        $giftCardArr[$giftCardCount]["external_link"] = $giftCardRow->external_link;
                        $giftCardArr[$giftCardCount]["internal_link_mode"] = $giftCardRow->internal_link_mode;
                        $giftCardCount++;
                    }

                    // ========== gift card reward ========== //

                    // ========== investment details ========== //
                    $investment = $operation->get_data("investment",'*',array("status"=>1));
                    $investmentRowArr = array();
                    $investmentRowCount = 0;
                    foreach($investment["result"] as $investmentRow){
                        $investmentRowArr[$investmentRowCount]["title"] = $investmentRow->name;
                        $investmentRowArr[$investmentRowCount]["image"] = $investmentRow->image;
                        $investmentRowArr[$investmentRowCount]["external_link"] = $investmentRow->external_link;
                        $investmentRowArr[$investmentRowCount]["internal_link_mode"] = $investmentRow->internal_link_mode;
                        $investmentRowCount++;
                    }
                    // ========== investment details ========== //

                    
                    $weatherService = array(
                        "weather"=>$weatherDetails,
                        "service"=>$serviceDetails
                    );
                    
                    $returnArr["user_details"] = $userDetails;
                    // $returnArr["weather_details"] = $weatherDetails;
                    // $returnArr["service"] = $serviceDetails;
                    $returnArr["weather_service"] = $weatherService;
                    $returnArr["kyc"] = $kycDetails;
                    $returnArr["banner_details"] = $bannerDetails;
                    $returnArr["daily_check_in"] = $checkingdata;
                    $returnArr["daily_reward"] = $rewardDetails;
                    $returnArr["gift_card"] = $giftCardArr;
                    $returnArr["investment"] = $investmentRowArr;
                    return $this->success_response("User Profile!",$returnArr);
                }else{
                    return $this->error_response("Sorry! invalid request!");
                }
            }else{
                return $this->error_response("Sorry! user id required!");
            }
        }

        protected function get_user_weather($userId,$lat,$long){
            $operation = new Operation();
            if( (strlen($lat)>0) && (strlen($long)>0) ){
                $cURLConnection = curl_init();
                curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.openweathermap.org/data/2.5/weather?lat='.$lat.'&lon='.$long.'&appid=e0dfc45e7354d0031da739a277eb15cf&units=metric');
                curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
                $phoneList = curl_exec($cURLConnection);
                curl_close($cURLConnection);
                $jsonArrayResponse = json_decode($phoneList);
                $updateUser = $operation->update_data("users",array("id"=>$userId),array("latitude"=>$lat,"longitude"=>$long));
            }else{
                $getUserData = $operation->get_data("users",'id,latitude,longitude',array("id"=>$userId));
                $userLastLat = $getUserData["result"][0]->latitude;
                $userLastLong = $getUserData["result"][0]->longitude;
                if( (empty($userLastLat)) && (empty($userLastLong)) ){
                    $userLastLat = "25.2048";
                    $userLastLong = "55.2708";
                }

                $cURLConnection = curl_init();
                curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.openweathermap.org/data/2.5/weather?lat='.$userLastLat.'&lon='.$userLastLong.'&appid=e0dfc45e7354d0031da739a277eb15cf&units=metric');
                curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
                $phoneList = curl_exec($cURLConnection);
                curl_close($cURLConnection);
                $jsonArrayResponse = json_decode($phoneList);
            }
            $returnArr = array();
            if(!empty($jsonArrayResponse)){
                $returnArr["mode"] = str_replace(" ","_",$jsonArrayResponse->weather[0]->description);
                $returnArr["temperature"] = (string)round($jsonArrayResponse->main->temp);
                $returnArr["unit"] = "C";
                $returnArr["icon"] = "https://openweathermap.org/img/wn/".$jsonArrayResponse->weather[0]->icon."@4x.png";
            }else{
                $returnArr["mode"] = "";
                $returnArr["temperature"] = "";
                $returnArr["unit"] = "";
                $returnArr["icon"] = "C";
            }
            return $returnArr;
        }


        public function walking_update(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $totalCoins = $this->input_post("total_coins");
            $stepCount = $this->input_post("step_count");
            $datetime = $this->input_post("datetime");
            $distanceValue = $this->input_post("distance_value");
            $caloriesValue = $this->input_post("calories_value");
            $timeValues = $this->input_post("time_values");
            $dateRange = $this->input_get("date_range");
            if(strlen($dateRange) == 0){
                $dateRange = 1;
            }
            $date = date("Y-m-d",time());

            if( (strlen($userId)>0) && (strlen($totalCoins)>0) && (strlen($stepCount)>0) && (strlen($datetime)>0) && (strlen($distanceValue)>0) && (strlen($caloriesValue)>0) && (strlen($timeValues)>0) ){
                $getUserDailyData = $operation->get_data("walkearn_user_history",'id,user_id,date',array("user_id"=>$userId,"date"=>date("Y-m-d",strtotime($datetime)),"hour"=> date("H",strtotime($datetime)) ));
                if($getUserDailyData["num_rows"] > 0){
                    $this->db = \Config\Database::connect();
                    $builder = $this->db->table("walkearn_user_history");
                    $totalCoins = 'total_coins+'.$totalCoins;
                    $stepCount = 'step_count+'.$stepCount;
                    $distanceValue = 'distance_value+'.$distanceValue;
                    $caloriesValue = 'calories_value+'.$caloriesValue;
                    $timeValues = 'time_values+'.$timeValues;

                    $datetime = date("Y-m-d H:m:s",strtotime($datetime));
                    
                    $builder->set('total_coins', $totalCoins, FALSE);
                    $builder->set('step_count', $stepCount, FALSE);
                    $builder->set('distance_value', $distanceValue, FALSE);
                    $builder->set('calories_value', $caloriesValue, FALSE);
                    $builder->set('time_values', $timeValues, FALSE);
                    $builder->set('datetime', $datetime);
                    
                    $builder->where(array("user_id"=>$userId));
                    $builder->where(array("date"=>date("Y-m-d",strtotime($datetime))));
                    $builder->update();
                }else{
                    $dataToInsert = array(
                        "user_id"=>$userId,
                        "total_coins"=>$totalCoins,
                        "step_count"=>$stepCount,
                        "datetime"=>date("Y-m-d H:m:s",strtotime($datetime)),
                        "date"=>date("Y-m-d",strtotime($datetime)),
                        "hour"=> date("H",strtotime($datetime)),
                        "day"=> date("d",strtotime($datetime)),
                        "distance_value"=>$distanceValue,
                        "calories_value"=>$caloriesValue,
                        "time_values"=>$timeValues
                    );
                    $added = $operation->insert_data("walkearn_user_history",$dataToInsert);
                }
                // $getUserDailyData = $operation->get_data("walkearn_user_history",'*',array("user_id"=>$userId,"date"=>date("Y-m-d",strtotime($datetime))));
                // $returnArr = $getUserDailyData["result"][0];
                
                if( (strlen($dateRange) == 0) || ($dateRange == 1) ){
                    $endDate = $startDate = date("Y-m-d",strtotime($date));
                }else if($dateRange == "7"){
                    $endDate = date("Y-m-d",strtotime($date));
                    $startDate = date('Y-m-d', strtotime($date. ' - 6 days'));
                }else if($dateRange == "30"){
                    $endDate = date("Y-m-d",strtotime($date));
                    $startDate = date('Y-m-d', strtotime($date. ' - 30 days'));
                }else{
                    return $this->error_response("Sorry! invalid date!");
                }

                $commonData["date_range"] = $dateRange;
                $commonData["start_date"] = $startDate;
                $commonData["end_date"] = $endDate;

                $commonData["total_coins"] = "0";
                $commonData["avg_coins"] = "0";

                $commonData["total_step"] = "0";
                $commonData["avg_step"] = "0";

                $commonData["total_distance"] = "0";
                $commonData["avg_distance"] = "0";

                $commonData["total_calories"] = "0";
                $commonData["avg_calories"] = "0";

                $commonData["total_time"] = "0";
                $commonData["avg_time"] = "0";

                $searchCondition = array("user_id"=>$userId);

                if($dateRange > 1){
                    $searchCondition = array_merge($searchCondition,array("date <="=>date("Y-m-d",strtotime($endDate))));
                    $searchCondition = array_merge($searchCondition,array("date >="=>date("Y-m-d",strtotime($startDate))));
                }else{
                    $searchCondition = array_merge($searchCondition,array("date"=>date("Y-m-d",strtotime($startDate))));
                }

                $getUserData = $operation->get_data("walkearn_user_history",'*',$searchCondition);
                $returnArr = array();
                if($getUserData["num_rows"] > 0){
                    $returnArr = array();
                    if($dateRange == 1){
                        for($currIndex = 0; $currIndex < 24; $currIndex++){
                            $returnArr[$currIndex] = array(
                                "day"=>(string)date("d", strtotime($date)),
                                "month"=>(string)date("M", strtotime($date)),
                                "total_coins"=>(string)0,
                                "step_count"=>(string)0,
                                "distance_value"=>(string)0,
                                "calories_value"=>(string)0,
                                "time_values"=>(string)0
                            );
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = 24;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->hour == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["date"] = (string)$getUserDataRow->date;
                                    $returnArr[$returnArrRowKey]["hour"] = (string)$getUserDataRow->hour;
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)$getUserDataRow->total_coins;
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)$getUserDataRow->step_count;
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)$getUserDataRow->distance_value; 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)$getUserDataRow->calories_value; 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)$getUserDataRow->time_values;

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }
                        
                    }else if($dateRange == 7){
                        $loopStart = date("d",strtotime($startDate));
                        $returnArr = array();
                        for($currIndex = 0; $currIndex < 7; $currIndex++){
                            $currIndexVal = date('d', strtotime($startDate. ' + '.$currIndex.' days'));
                            $currMonthVal = date('M', strtotime($startDate. ' + '.$currIndex.' days'));
                            $returnArr[$currIndex] = array(
                                "day"=>(string)$currIndexVal,
                                "month"=>(string)$currMonthVal,
                                "total_coins"=>(string)0,
                                "step_count"=>(string)0,
                                "distance_value"=>(string)0,
                                "calories_value"=>(string)0,
                                "time_values"=>(string)0
                            );  
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = 7;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->day == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)($returnArr[$returnArrRowKey]["total_coins"]+$getUserDataRow->total_coins);
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)($returnArr[$returnArrRowKey]["step_count"]+$getUserDataRow->step_count); 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)($returnArr[$returnArrRowKey]["distance_value"]+$getUserDataRow->distance_value); 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)($returnArr[$returnArrRowKey]["calories_value"]+$getUserDataRow->calories_value); 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)($returnArr[$returnArrRowKey]["time_values"]+$getUserDataRow->time_values);

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }

                        
                    }else if($dateRange == 30){
                        $indexStop = date('t',strtotime($date));
                        $currMonthVal = date('M',strtotime($date));
                        for($currIndex = 0; $currIndex < $indexStop; $currIndex++){
                            $returnArr[$currIndex] = array();
                            $returnArr[$currIndex]["day"] = (string)($currIndex+1);
                            $returnArr[$currIndex]["month"] = (string)$currMonthVal;
                            $returnArr[$currIndex]["total_coins"] = "0";
                            $returnArr[$currIndex]["step_count"] = "0"; 
                            $returnArr[$currIndex]["distance_value"] = "0"; 
                            $returnArr[$currIndex]["calories_value"] = "0"; 
                            $returnArr[$currIndex]["time_values"] = "0";
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = $indexStop;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->day == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)($returnArr[$returnArrRowKey]["total_coins"]+$getUserDataRow->total_coins);
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)($returnArr[$returnArrRowKey]["step_count"]+$getUserDataRow->step_count); 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)($returnArr[$returnArrRowKey]["distance_value"]+$getUserDataRow->distance_value); 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)($returnArr[$returnArrRowKey]["calories_value"]+$getUserDataRow->calories_value); 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)($returnArr[$returnArrRowKey]["time_values"]+$getUserDataRow->time_values);

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }
                    }
                    $commonData["total_step"] = (string)$totalStep;
                    $commonData["avg_step"] = (string)round($totalStep/$avgCounter);
                    
                    $commonData["total_coins"] = (string)$totalCoins;
                    $commonData["avg_coins"] = (string)round($totalCoins/$avgCounter);

                    $commonData["total_distance"] = (string)$totalDistance;
                    $commonData["avg_distance"] = (string)round($totalDistance/$avgCounter);

                    $commonData["total_calories"] = (string)$totalCalories;
                    $commonData["avg_calories"] = (string)round($totalCalories/$avgCounter);

                    $commonData["total_time"] = (string)$totalTime;
                    $commonData["avg_time"] = (string)round($totalTime/$avgCounter);
                    $returnData = array(
                        "common_data" => $commonData,
                        "graph_data" => $returnArr
                    );
                    return $this->success_response("Walking history!",$returnData);
                }else{
                    $returnData = array(
                        "common_data" => $commonData,
                        "graph_data" => $returnArr
                    );
                    return $this->success_response("No walking history found!",$returnData);
                }


            }else{
                return $this->error_response("Sorry! missing params!");
            }
        }


        public function walking_history(){
            date_default_timezone_set("Asia/Dubai");
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            $reportType = $this->input_get("report_type"); // steps or Calories or time or Distance
            $dateRange = $this->input_get("date_range"); // 1 or 7 or 30
            $date = $this->input_get("date");
            if( strlen($date) == 0){
                $date = date("Y-m-d",time());
            }
            
            if( (strlen($userId)>0) ){

                if( (strlen($dateRange) == 0) || ($dateRange == 1) ){
                    $endDate = $startDate = date("Y-m-d",strtotime($date));
                }else if($dateRange == "7"){
                    $endDate = date("Y-m-d",strtotime($date));
                    $startDate = date('Y-m-d', strtotime($date. ' - 6 days'));
                }else if($dateRange == "30"){
                    $endDate = date("Y-m-d",strtotime($date));
                    $startDate = date('Y-m-d', strtotime($date. ' - 30 days'));
                }else{
                    return $this->error_response("Sorry! invalid date!");
                }


                $commonData["date_range"] = $dateRange;
                $commonData["start_date"] = $startDate;
                $commonData["end_date"] = $endDate;

                $commonData["total_coins"] = "0";
                $commonData["avg_coins"] = "0";

                $commonData["total_step"] = "0";
                $commonData["avg_step"] = "0";

                $commonData["total_distance"] = "0";
                $commonData["avg_distance"] = "0";

                $commonData["total_calories"] = "0";
                $commonData["avg_calories"] = "0";

                $commonData["total_time"] = "0";
                $commonData["avg_time"] = "0";

                $searchCondition = array("user_id"=>$userId);

                if($dateRange > 1){
                    $searchCondition = array_merge($searchCondition,array("date <="=>date("Y-m-d",strtotime($endDate))));
                    $searchCondition = array_merge($searchCondition,array("date >="=>date("Y-m-d",strtotime($startDate))));
                }else{
                    $searchCondition = array_merge($searchCondition,array("date"=>date("Y-m-d",strtotime($startDate))));
                }

                $getUserData = $operation->get_data("walkearn_user_history",'*',$searchCondition);
                $returnArr = array();
                if($getUserData["num_rows"] > 0){
                    $returnArr = array();
                    if($dateRange == 1){
                        for($currIndex = 0; $currIndex < 24; $currIndex++){
                            $returnArr[$currIndex] = array(
                                "day"=>(string)date("d", strtotime($date)),
                                "month"=>(string)date("M", strtotime($date)),
                                "total_coins"=>(string)0,
                                "step_count"=>(string)0,
                                "distance_value"=>(string)0,
                                "calories_value"=>(string)0,
                                "time_values"=>(string)0
                            );
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = 24;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->hour == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["date"] = (string)$getUserDataRow->date;
                                    $returnArr[$returnArrRowKey]["hour"] = (string)$getUserDataRow->hour;
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)$getUserDataRow->total_coins;
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)$getUserDataRow->step_count;
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)$getUserDataRow->distance_value; 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)$getUserDataRow->calories_value; 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)$getUserDataRow->time_values;

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }
                        
                    }else if($dateRange == 7){
                        $loopStart = date("d",strtotime($startDate));
                        $returnArr = array();
                        for($currIndex = 0; $currIndex < 7; $currIndex++){
                            $currIndexVal = date('d', strtotime($startDate. ' + '.$currIndex.' days'));
                            $currMonthVal = date('M', strtotime($startDate. ' + '.$currIndex.' days'));
                            $returnArr[$currIndex] = array(
                                "day"=>(string)$currIndexVal,
                                "month"=>(string)$currMonthVal,
                                "total_coins"=>(string)0,
                                "step_count"=>(string)0,
                                "distance_value"=>(string)0,
                                "calories_value"=>(string)0,
                                "time_values"=>(string)0
                            );  
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = 7;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->day == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)($returnArr[$returnArrRowKey]["total_coins"]+$getUserDataRow->total_coins);
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)($returnArr[$returnArrRowKey]["step_count"]+$getUserDataRow->step_count); 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)($returnArr[$returnArrRowKey]["distance_value"]+$getUserDataRow->distance_value); 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)($returnArr[$returnArrRowKey]["calories_value"]+$getUserDataRow->calories_value); 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)($returnArr[$returnArrRowKey]["time_values"]+$getUserDataRow->time_values);

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }

                        
                    }else if($dateRange == 30){
                        $indexStop = date('t',strtotime($date));
                        $currMonthVal = date('M',strtotime($date));
                        for($currIndex = 0; $currIndex < $indexStop; $currIndex++){
                            $returnArr[$currIndex] = array();
                            $returnArr[$currIndex]["day"] = (string)($currIndex+1);
                            $returnArr[$currIndex]["month"] = (string)$currMonthVal;
                            $returnArr[$currIndex]["total_coins"] = "0";
                            $returnArr[$currIndex]["step_count"] = "0"; 
                            $returnArr[$currIndex]["distance_value"] = "0"; 
                            $returnArr[$currIndex]["calories_value"] = "0"; 
                            $returnArr[$currIndex]["time_values"] = "0";
                        }
                        $totalStep = 0;
                        $totalCoins = 0;
                        $totalDistance = 0;
                        $totalCalories = 0;
                        $totalTime = 0;
                        $avgCounter = $indexStop;
                        foreach($returnArr as $returnArrRowKey=>$returnArrRow){
                            foreach($getUserData["result"] as $getUserDataRow){
                                if($getUserDataRow->day == $returnArrRowKey){
                                    $returnArr[$returnArrRowKey]["total_coins"] = (string)($returnArr[$returnArrRowKey]["total_coins"]+$getUserDataRow->total_coins);
                                    $returnArr[$returnArrRowKey]["step_count"] = (string)($returnArr[$returnArrRowKey]["step_count"]+$getUserDataRow->step_count); 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $returnArr[$returnArrRowKey]["distance_value"] = (string)($returnArr[$returnArrRowKey]["distance_value"]+$getUserDataRow->distance_value); 
                                    $returnArr[$returnArrRowKey]["calories_value"] = (string)($returnArr[$returnArrRowKey]["calories_value"]+$getUserDataRow->calories_value); 
                                    $returnArr[$returnArrRowKey]["time_values"] = (string)($returnArr[$returnArrRowKey]["time_values"]+$getUserDataRow->time_values);

                                    $totalCoins = $totalCoins+$getUserDataRow->total_coins; 
                                    $totalStep = $totalStep+$getUserDataRow->step_count; 
                                    $totalDistance = $totalDistance+$getUserDataRow->distance_value; 
                                    $totalCalories = $totalCalories+$getUserDataRow->calories_value; 
                                    $totalTime = $totalTime+$getUserDataRow->time_values; 
                                }
                            }
                        }
                    }
                    $commonData["total_step"] = (string)$totalStep;
                    $commonData["avg_step"] = (string)round($totalStep/$avgCounter);
                    
                    $commonData["total_coins"] = (string)$totalCoins;
                    $commonData["avg_coins"] = (string)round($totalCoins/$avgCounter);

                    $commonData["total_distance"] = (string)$totalDistance;
                    $commonData["avg_distance"] = (string)round($totalDistance/$avgCounter);

                    $commonData["total_calories"] = (string)$totalCalories;
                    $commonData["avg_calories"] = (string)round($totalCalories/$avgCounter);

                    $commonData["total_time"] = (string)$totalTime;
                    $commonData["avg_time"] = (string)round($totalTime/$avgCounter);
                    $returnData = array(
                        "common_data" => $commonData,
                        "graph_data" => $returnArr
                    );
                    return $this->success_response("Walking history!",$returnData);
                }else{
                    $returnData = array(
                        "common_data" => $commonData,
                        "graph_data" => $returnArr
                    );
                    return $this->success_response("No walking history found!",$returnData);
                }
            }else{
                return $this->error_response("Sorry! user id required!");
            }
        }


        public function walking_details(){
            $operation = new Operation();
            date_default_timezone_set("Asia/Dubai");
            $userId = $this->input_get("user_id");
            if(strlen(($userId) > 0)){
                $searchCondition = array("user_id"=>$userId);
                $searchCondition = array_merge($searchCondition,array("date"=>date("Y-m-d",time())));
                $getUserData = $operation->get_data("walkearn_user_history",'*',$searchCondition);
                $totalStep = 0;
                $totalCoin = 0;
                $totalDistances = 0;
                $totalCalories = 0;
                $totalTime = 0;
                $returnArr = array();
                $stepsArr = array();
                if($getUserData["num_rows"] > 0){
                    foreach($getUserData["result"]  as $getUserDataRow){
                        $totalStep = $totalStep+$getUserDataRow->step_count;
                        $totalCoin = $totalCoin+$getUserDataRow->total_coins;
                        $totalDistances = $totalDistances+$getUserDataRow->distance_value;
                        $totalCalories = $totalCalories+$getUserDataRow->calories_value;
                        $totalTime = $totalTime+$getUserDataRow->time_values;
                    }
                    $stepsArr["date"] = (string)date("Y-m-d", time());
                    $stepsArr["total_step"] = (string)$totalStep;
                    $stepsArr["total_coin"] = (string)$totalCoin;
                    $stepsArr["total_distances"] = (string)$totalDistances;
                    $stepsArr["total_calories"] = (string)$totalCalories;
                    $stepsArr["total_time"] = (string)$totalTime;
                }else{
                    $stepsArr["date"] = (string)date("Y-m-d", time());
                    $stepsArr["total_step"] = "0";
                    $stepsArr["total_coin"] = "0";
                    $stepsArr["total_distances"] = "0";
                    $stepsArr["total_calories"] = "0";
                    $stepsArr["total_time"] = "0";
                }
                $returnArr["walk_reward_point"] = "0.01";
                $returnArr["walk_daily_details"] = $stepsArr;

                $userHealth = $operation->get_data("user_health",'*',array('user_id'=>$userId));
                if($userHealth["num_rows"] > 0){
                    $healthArr = array(
                        "weight"=>(string)$userHealth["result"][0]->weight,
                        "weight_unit"=>(string)$userHealth["result"][0]->weight_unit,
                        "length"=>(string)$userHealth["result"][0]->length,
                        "length_unit"=>(string)$userHealth["result"][0]->length_unit,
                        "bmi_value"=>(string)$userHealth["result"][0]->bmi_value
                    );
                }else{
                    $healthArr = array(
                        "weight"=>"0",
                        "weight_unit"=>"0",
                        "length"=>"0",
                        "length_unit"=>"0",
                        "bmi_value"=>"0"
                    );
                }
                $returnArr["health_details"] = $healthArr;
                $returnArr["banner_one"] = array(
                    array(
                        "id"=>"1",
                        "image"=>BASEPATH."public/files/work_earn/1.png"
                    ),
                    array(
                        "id"=>"2",
                        "image"=>BASEPATH."public/files/work_earn/add-nike.png"
                    )
                    
                );
                $returnArr["banner_two"] = array(
                    array(
                        "id"=>"1",
                        "image"=>BASEPATH."public/files/work_earn/2.png"
                    ),
                    array(
                        "id"=>"2",
                        "image"=>BASEPATH."public/files/work_earn/3.png"
                    )
                );

                return $this->success_response("Daily walking details!",$returnArr);
            }else{
                return $this->error_response("Sorry! user id required!");
            }
        }


        // ============= check referral code =================//
        public function check_referral_code(){
            $operation = new Operation();
            $referralCode = $this->input_post("referral_code");
            if(strlen($referralCode) > 0){
                $checkTeamMember = $operation->get_data("team_referral_code",'*',array('code'=>(string)$referralCode));
                // echo "<pre>";print_r($referralCode);die();
                if($checkTeamMember["num_rows"] > 0){
                    return $this->success_response("This is valid referral code");
                }else{
                    $checkReferral = $operation->get_data("users",'*',array("referral_code"=>$referralCode));
                    if($checkReferral["num_rows"] > 0){
                        return $this->success_response("This is valid referral code");
                    }else{
                        return $this->error_response("Sorry! invalid referral code!");
                    }
                }
            }else{
                return $this->error_response("Sorry! referral code required!");
            }
        }
        // ============= check referral code =================//

        // ============= refer and earn algo ================ //

        protected function refer_and_earn($userId){
            $operation = new Operation();

            $userReferral = $operation->get_data("user_referral",'*',array("user_id"=>$userId));
            if($userReferral["num_rows"] > 0){
                $chainId = $userReferral["result"][0]->chain_id;
                $getAllTeamMember = $operation->get_data("user_referral",'*',array("chain_id"=>$chainId),"level","DESC");
                // echo "<pre>";print_r($getAllTeamMember);die();
                if($getAllTeamMember["num_rows"] > 0){
                    $currUserLevel = 1;
                    foreach($getAllTeamMember["result"] as $getAllTeamMemberRow){
                        if($userId != $getAllTeamMemberRow->user_id){
                            $currUserReward = $this->reward_calculation($currUserLevel);
                            
                            $this->db = \Config\Database::connect();
                            $builder = $this->db->table("users");
                            $walletPoint = 'wallet_point+'.(int)$currUserReward;
                            $totalNetwork = 'total_network+'.(int)1;
                            $builder->set('wallet_point', $walletPoint, FALSE);
                            $builder->set('total_network', $totalNetwork, FALSE);
                            
                            $builder->where(array("id"=>$getAllTeamMemberRow->user_id));
                            $builder->update();
    
                            $transactionData = array(
                                "user_id"=>$getAllTeamMemberRow->user_id,
                                "type"=>1,
                                "points"=>$currUserReward,
                                "details"=>"Referral Reward"
                            );
                            $operation->insert_data("user_wallet_transaction",$transactionData);
                        }else{
                            $currUserReward = 2500;
                            $this->db = \Config\Database::connect();
                            $builder = $this->db->table("users");
                            $walletPoint = 'wallet_point+'.(int)$currUserReward;
                            $builder->set('wallet_point', $walletPoint, FALSE);
                            $builder->where(array("id"=>$getAllTeamMemberRow->user_id));
                            $builder->update();
    
                            $transactionData = array(
                                "user_id"=>$getAllTeamMemberRow->user_id,
                                "type"=>1,
                                "points"=>$currUserReward,
                                "details"=>"Joining Bonus"
                            );
                            $operation->insert_data("user_wallet_transaction",$transactionData);
                        }
                        $currUserLevel++;
                    }
                }
            }
            return true;
        }


        public function check_demo(){
            $level = $this->input_get("level");
            $data = $this->reward_calculation($level);
            // $user = $this->input_get("user");
            // $data = $this->refer_and_earn($user);
            echo $data;
        }

        protected function reward_calculation($level){
            $reward = 2500;
            $rewardPercentage = 100; 
            if($level == 1){
                $prevRewardPercentage = 100;
                $rewardPercentage = 100;
                $rewardPoint = "2500";
            }else if($level > 1){
                switch ($level) {
                    case 2:
                        $prevRewardPercentage = 100;
                        $rewardPercentage = 10;
                        $rewardPoint = "250";
                        break;
                    case 3:
                        $prevRewardPercentage = 10;
                        $rewardPercentage = 9;
                        $rewardPoint = "22.5";
                        break;
                    case 4:
                        $prevRewardPercentage = 9;
                        $rewardPercentage = 8;
                        $rewardPoint = "1.8";
                        break;
                    case 5:
                        $prevRewardPercentage = 8;
                        $rewardPercentage = 7;
                        $rewardPoint = "0.126";
                        break;
                    case 6:
                        $prevRewardPercentage = 7;
                        $rewardPercentage = 6;
                        $rewardPoint = "0.00756";
                        break;
                    case 7:
                        $prevRewardPercentage = 6;
                        $rewardPercentage = 5;
                        $rewardPoint = "0.000378";
                        break;
                    case 8:
                        $prevRewardPercentage = 5;
                        $rewardPercentage = 4;
                        $rewardPoint = "0.00001512";
                        break;
                    case 9:
                        $prevRewardPercentage = 4;
                        $rewardPercentage = 3;
                        $rewardPoint = "0.0000004536";
                        break;
                    case 10:
                        $prevRewardPercentage = 3;
                        $rewardPercentage = 2;
                        $rewardPoint = "0.000000009072";
                        break;
                    case 11:
                        $prevRewardPercentage = 2;
                        $rewardPercentage = 1;
                        $rewardPoint = "0.00000000009072";
                        break;
                    case 12:
                        $prevRewardPercentage = 1;
                        $rewardPercentage = 0.10;
                        $rewardPoint = "0.00000000000009072";
                        break;
                    default:
                        $prevRewardPercentage = 0.10;
                        $rewardPercentage = 0.01;
                        $rewardPoint = "0.00000000000000001";
                }
            }
            // $usersPercentage = $this->cal_percentage($rewardPercentage,$level,$prevRewardPercentage);
            return (string)$rewardPoint;

        }

        protected function cal_percentage($rewardPercentage, $level,$prevRewardPercentage="",$flag=1) {
            if($level == 1){
                $num_total=2500;
            }else{
                if($flag == 1){
                    echo $num_total=$this->cal_percentage($prevRewardPercentage,$level,0);die();
                }
            }
            $count1 = $rewardPercentage / 100;
            $count2 = $count1 * $num_total;
            // $count = number_format($count2, 0);
            return $count2;
        }


        // ================ user health ================= //
        public function user_health(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            if(strlen($userId) > 0){
                $userHealth = $operation->get_data("user_health",'*',array('user_id'=>$userId));
                if($userHealth["num_rows"] > 0){
                    $returnArr = array(
                        "user_id"=>$userHealth["result"][0]->user_id,
                        "weight"=>$userHealth["result"][0]->weight,
                        "weight_unit"=>$userHealth["result"][0]->weight_unit,
                        "length"=>$userHealth["result"][0]->length,
                        "length_unit"=>$userHealth["result"][0]->length_unit,
                        "bmi_value"=>$userHealth["result"][0]->bmi_value,
                        "added_on"=>$userHealth["result"][0]->added_on
                    );
                    return $this->success_response("Users health data!",$returnArr);
                }else{
                    return $this->error_response("Sorry! No data found!");
                }
            }else{
                return $this->error_response("Sorry! user id required!");
            }
        }

        public function user_health_update(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $weight = $this->input_post("weight");
            $weight_unit = $this->input_post("weight_unit");
            $length = $this->input_post("length");
            $length_unit = $this->input_post("length_unit");
            $bmi_value = $this->input_post("bmi_value");
            $userHealth = $operation->get_data("user_health",'*',array('user_id'=>$userId));

            if( (strlen($userId)>0) && (strlen($weight)>0) && (strlen($weight_unit)>0) && (strlen($length)>0) && (strlen($length_unit)>0) && (strlen($bmi_value)>0)){
                if($userHealth["num_rows"] > 0){
                    $updateData = array(
                        "weight"=>$weight,
                        "weight_unit"=> strtolower($weight_unit),
                        "length"=>$length,
                        "length_unit"=> strtolower($length_unit),
                        "bmi_value"=>$bmi_value,
                        "added_on"=>date("Y-m-d H:m:s")
                    );
                    $updated = $operation->update_data("user_health",array("user_id"=>$userId),$updateData);
                }else{
                    $dataToInsert = array(
                        "user_id"=>$userId,
                        "weight"=>$weight,
                        "weight_unit"=>strtolower($weight_unit),
                        "length"=>$length,
                        "length_unit"=>strtolower($length_unit),
                        "bmi_value"=>$bmi_value
                    );
                    $updated = $operation->insert_data("user_health",$dataToInsert);
                }
                if($updated){
                    return $this->success_response("Users health data updated!");
                }else{
                    return $this->error_response("Sorry! already updated!");
                }
            }else{
                return $this->error_response("Sorry! missing params!");
            }
        }


        public function refer_and_earn_demo_test(){
            $operation = new Operation();
            $usedId = $this->input_get("id");
            $returnArr = array();

            $getUserLevel = $operation->get_data("user_referral_new",'*',array("user_id"=>$usedId),"id","ASC");
            $getUserWallet = $operation->get_data("users_new",'id,wallet_point',array("id"=>$usedId),"id","ASC");
            $getUserTransaction = $operation->get_data("user_wallet_transaction_new",'*',array("user_id"=>$usedId),"id","ASC");
            $userLevel = $getUserLevel["result"][0]->level;
            $returnArr["user_data"]["user_id"] = $usedId;
            $returnArr["user_data"]["wallet_point"] = $getUserWallet["result"][0]->wallet_point;
            $returnArr["user_data"]["wallet_transaction"] = $getUserTransaction["result"];


            $sponsonData = $operation->get_data("user_referral_new",'*',array("referred_by"=>$usedId),"id","ASC");
            $returnArr["sponson_data"]["count"] = $sponsonData["num_rows"];
            $sponsonDataArr = array();
            $sponsonDataArrCount = 0;
            if($sponsonData["num_rows"] > 0){
                foreach($sponsonData["result"] as $sponsonDataRow){
                    $sponsonDataArr[$sponsonDataArrCount]["user_id"] = $sponsonDataRow->user_id;
                    $sponsonDataArrCount++;
                }
                $returnArr["sponson_data"]["list"] = $sponsonDataArr;
                $networdLevelVal = $userLevel+1;
                $networkData = $operation->get_data("user_referral_new",'*',array("chain_id"=>1,"level >"=>$networdLevelVal),"id","ASC");
                echo $operation->getLastQuery();
                // echo "<pre>";print_r($networkData);die();
                if($networkData["num_rows"] > 0){
                    $networdDataArr = array();
                    $networkDataRowCount = 0;
                    foreach($networkData["result"] as $networkDataRow){
                        $networdDataArr[$networkDataRowCount]["user_id"] = $networkDataRow->id;
                        $networkDataRowCount++;
                    }
                    $returnArr["network_data"]["count"] = $networkData["num_rows"];
                    $returnArr["network_data"]["list"] =  $networdDataArr;
                    
                }else{
                    $networdDataArr = array();
                    $returnArr["network_data"]["count"] = 0;
                    $returnArr["network_data"]["list"] =  $networdDataArr;
                }

                $returnArr["total_data"]["count"] = $returnArr["network_data"]["count"]+$returnArr["sponson_data"]["count"];
            }else{
                $returnArr["sponson_data"]["count"] = 0;
                $returnArr["sponson_data"]["list"] = array();
                $returnArr["netword_data"]["count"] = 0;
                $returnArr["netword_data"]["list"] =  array();
                $returnArr["total_data"]["count"] = 0;
            }

            

            return $this->success_response("For Demo",$returnArr);
        }


        public function demo_user_create(){
            $operation = new Operation();
            $referBy = $this->input_get("refer_by");
            $dataForUser = array(
                "wallet_point"=>0,
                "total_network"=>0
            );
            $operation->insert_data("users_for_demo",$dataForUser);

            if($referBy != 0){
                $userReferral = $operation->get_data("user_referra_for_demo",'*',array("referred_by"=>$referBy));

                $dataForReferral = array(
                    "user_id"=>$userId,
                    "referred_by"=>0,
                    "chain_id"=>$checkReferral["result"][0]->id,
                    "level"=>1
                );
                $operation->insert_data("user_referra_for_demo",$dataForReferral);
            }

            
            
            
            
            
            if($checkReferral["num_rows"] > 0){
                $dataForReferral = array(
                    "user_id"=>$userId,
                    "referred_by"=>0,
                    "chain_id"=>$checkReferral["result"][0]->id,
                    "level"=>1
                );
                $operation->insert_data("user_referral",$dataForReferral);
            }else{
                return $this->error_response("Sorry! invalid referral code!");
            }


        }

        protected function get_refered_user($userId){
            $userReferral = $operation->get_data("user_referra_for_demo",'*',array("referred_by"=>$userId));
        }


        public function refer_and_earn_demo(){

        }
    }

?>