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

            if(strlen($phone_verify) == 0){
                $phone_verify = 0;
            }
            if(strlen($email_verify) == 0){
                $email_verify = 0;
            }

            if( (strlen($loginType) > 0) && (strlen($deviceType) > 0) && (strlen($deviceToken) > 0) ){

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
                                    $code = 'UAE0000000';
                                    $lastCode = $operation->get_data("users",'id',array(),"id","DESC","1","0");
                                    if($lastCode["num_rows"]==1){
                                        $lastId = $lastCode["result"][0]->id;
                                        $currId = (int)$lastId+1;
                                        $WalletId = $code.$currId;
                                    }else{
                                        $WalletId = $code."1";
                                    }
        
                                    if(!empty($referral)){
                                        $checkReferral = $operation->get_data("users",'id,referral_code',array("referral_code"=>$referral));
                                        if($checkReferral["num_rows"] > 0){
                                            $getRewardForReferredUser = $operation->get_data("app_reward_settings",'*',array("id"=>3));
                                            $rewardForReferredUser = $getRewardForReferredUser["result"][0]->required_point;
        
                                            $this->db = \Config\Database::connect();
                                            $builder = $this->db->table("users");
                                            $walletPoint = 'wallet_point+'.$rewardForReferredUser;
                                            $builder->set('wallet_point', $walletPoint, FALSE);
                                            $builder->where(array("id"=>$checkReferral["result"][0]->id));
                                            $builder->update();
                
                                            $transactionData = array(
                                                "user_id"=>$checkReferral["result"][0]->id,
                                                "type"=>1,
                                                "points"=>$rewardForReferredUser,
                                                "details"=>$getRewardForReferredUser["result"][0]->point_name
                                            );
                                            $operation->insert_data("user_wallet_transaction",$transactionData);
        
                                        }else{
                                            return $this->error_response("Sorry! invalid referral code!");
                                        }
                                    }
        
                                    $getRewardForNewUser = $operation->get_data("app_reward_settings",'*',array("id"=>5));
        
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
                                        "wallet_point"=>$getRewardForNewUser["result"][0]->required_point,
                                        "referral_code"=>"LBT-REF-".$this->generateRandomString()
                                    );
        
                                    $userId = $operation->insert_data("users",$dataToInsert);
        
                                    $transactionData = array(
                                        "user_id"=>$userId,
                                        "type"=>1,
                                        "points"=>$getRewardForNewUser["result"][0]->required_point,
                                        "details"=>"Registration Successfully"
                                    );
                                    $operation->insert_data("user_wallet_transaction",$transactionData);
                                    
                                    if(!empty($referral)){
                                        $getRewardForUser = $operation->get_data("app_reward_settings",'*',array("id"=>4));
                                        $rewardForUser = $getRewardForUser["result"][0]->required_point;
        
                                        $this->db = \Config\Database::connect();
                                        $builder = $this->db->table("users");
                                        $walletPoint = 'wallet_point+'.$rewardForUser;
                                        $builder->set('wallet_point', $walletPoint, FALSE);
                                        $builder->where(array("id"=>$userId));
                                        $builder->update();
            
                                        $transactionData = array(
                                            "user_id"=>$userId,
                                            "type"=>1,
                                            "points"=>$rewardForUser,
                                            "details"=>$getRewardForUser["result"][0]->point_name
                                        );
                                        $operation->insert_data("user_wallet_transaction",$transactionData);
        
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

        function send_user_email_oto(){
            $operation = new Operation();
            $mailer =  new Mailler();
            $userId = $this->input_post("user_id");
            if( (strlen($userId) >0 ) ){
                $checkEmail = $operation->get_data("users",'id,email,name,wallet_id',array("id"=>$userId));
                if($checkEmail["num_rows"] > 0){
                    $otp=rand("1000","9999");
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

                    return $this->error_response("Phone no verified successfully!");
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
                    "kyc_verify"=>$getUserDataRow->kyc_verify
                );
            }
            return $userData;
        }
        
        protected function generateRandomString($length = 10) {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomString = '';
            for ($i = 0; $i < $length; $i++) {
                $randomString .= $characters[rand(0, $charactersLength - 1)];
            }
            return $randomString;
        }


        public function user_dashboard(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");
            $latitude = $this->input_get("latitude");
            $longitude = $this->input_get("longitude");
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
    }

?>