<?php
    namespace App\Controllers\Api\v1;
    use App\Controllers\ApiController;
    use App\Models\Operation;
    use App\Models\Mailler;


    class Insurance extends ApiController{
        public function add_beneficiary(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $name = $this->input_post("name");
            $contactNo = $this->input_post("contact_no");
            $email = $this->input_post("email");
            $relation = $this->input_post("relation");
            $passportNo = $this->input_post("passport_no");
            $nationality = $this->input_post("nationality");
            $attachFile = $this->input_file("attach_file");

            if( (strlen($userId)>0) && (strlen($name)>0) && (strlen($contactNo)>0) && (strlen($email)>0) ){
                $checkInsurance = $operation->get_data("insurance_beneficiary",'*',array("user_id"=>$userId));
                if($checkInsurance["num_rows"] > 0){
                    $checkUserPoint = $operation->get_data("users",'id,wallet_point',array("id"=>$userId));
                    if($checkUserPoint["num_rows"] > 0){
                        $userPoint = $checkUserPoint["result"][0]->wallet_point;
                        if($userPoint >= 1000){
                            $noOfShare = $checkInsurance["num_rows"]+1;
                            $sharePercentage = 100/$noOfShare;
                            foreach($checkInsurance["result"] as $checkInsuranceRow){
                                $operation->update_data("insurance_beneficiary",array('id'=>$checkInsuranceRow->id),array("share"=>round($sharePercentage)));
                            }
                            $this->db = \Config\Database::connect();
                            $builder = $this->db->table("users");
                            $builder->set('wallet_point', 'wallet_point-1000', FALSE);
                            $builder->where(array("id"=>$userId));
                            $builder->update();

                            $transactionData = array(
                                "user_id"=>$userId,
                                "type"=>2,
                                "points"=>1000,
                                "details"=>"Beneficiary Added"
                            );
                            $operation->insert_data("user_wallet_transaction",$transactionData);
                        }else{
                            return $this->error_response("Sorry! Insufficient wallet point!");
                        }
                       
                    }else{
                        return $this->error_response("Sorry! Invalid User!");
                    }
                   
                }else{
                    $sharePercentage = 100;
                    
                }
                $insuranceData = array(
                    "user_id"=>$userId,
                    "name"=>$name,
                    "contact_no"=>$contactNo,
                    "email"=>$email,
                    "relation"=>$relation,
                    "passport_no"=>$passportNo,
                    "nationality"=>$nationality,
                    "share"=>$sharePercentage,
                );

                if( $attachFile->getSize() != 0 ){
                    $newFileName = md5(time()).'-'.$attachFile->getRandomName();
                    $uploadFileName = "lbt-beneficiary-".date("Y-m-d")."-".$newFileName;
                    if (! $attachFile->isValid()) {
                        return $this->error_response("Sorry!".$attachFile->getErrorString());
                    }else{
                        $targetDir = APIASSETSPATH."beneficiary/";
                        $uploadImage = $attachFile->move($targetDir,$uploadFileName);
                        $insuranceData["attach_file"] = BASEPATH."public/files/beneficiary/".$uploadFileName;
                    }
                }

                $addData = $operation->insert_data("insurance_beneficiary",$insuranceData);
                if($addData){
                    return $this->success_response("Beneficiary added successfully!");
                }else{
                    return $this->error_response("Sorry! Try again letter!");
                }

            }else{
                return $this->error_response("Sorry! user_id, name, contact_no and email required!");
            }

        }


        public function beneficiary_list(){
            $operation = new Operation();
            $userId = $this->input_get("user_id");

            if( (strlen($userId)>0) ){
                $beneficiary = $operation->get_data("insurance_beneficiary",'*',array("user_id"=>$userId));
                if($beneficiary["num_rows"] > 0){
                    return $this->success_response("Beneficiary listing!",$beneficiary["result"]);
                }else{
                    return $this->error_response("Sorry! no beneficiary found!");
                }
            }else{
                return $this->error_response("Sorry! user_id is required!");
            }
        }

        public function edit_beneficiary_share(){
            $operation = new Operation();
            $userId = $this->input_post("user_id");
            $beneficiaryShareJson = $this->input_post("beneficiary_share_json");
            if((strlen($userId)>0) && (strlen($beneficiaryShareJson)>0) ){
                $beneficiary = $operation->get_data("insurance_beneficiary",'*',array("user_id"=>$userId));
                $beneficiaryShareArr = json_decode($beneficiaryShareJson,true);
                if(is_array($beneficiaryShareArr)){
                    if($beneficiary["num_rows"] == count($beneficiaryShareArr)){
                        $totalShare = 0;
                        foreach($beneficiaryShareArr as $beneficiaryShareArrRow){
                            if( (!empty($beneficiaryShareArrRow["beneficiary_id"])) && (!empty($beneficiaryShareArrRow["beneficiary_share"]))){
                                $totalShare = $totalShare+$beneficiaryShareArrRow["beneficiary_share"];
                            }else{
                                return $this->error_response("Sorry! invalid beneficiary json!");
                            }
                        }
                        if($totalShare <= 100){
                            foreach($beneficiaryShareArr as $beneficiaryShareArrRow){
                                $operation->update_data("insurance_beneficiary",array('id'=>$beneficiaryShareArrRow["beneficiary_id"]),array("share"=>$beneficiaryShareArrRow["beneficiary_share"]));
                            }
                            return $this->success_response("Beneficiary share updated!");
                        }else{
                            return $this->error_response("Sorry! Total share must be less than or equal to 100%!");
                        }
                    }else{
                        return $this->error_response("Sorry! invalid beneficiary count in json!");
                    }
                }else{
                    return $this->error_response("Sorry! invalid beneficiary json format!");
                }
                
            }else{
                return $this->error_response("Sorry! user_id and beneficiary_share_json required!");
            }
        }


        public function edit_beneficiary_share_raw(){
            $operation = new Operation();
            $request = \Config\Services::request();
            $beneficiaryArr = $request->getJSON("beneficiary_json");
            if((!empty($beneficiaryArr)) ){
                if( (!empty($beneficiaryArr["user_id"])) && (!empty($beneficiaryArr["beneficiary_share"]))){
                    $userId = $beneficiaryArr["user_id"];
                    $beneficiary = $operation->get_data("insurance_beneficiary",'*',array("user_id"=>$userId));
                    $beneficiaryShareArr =  $beneficiaryArr["beneficiary_share"];//json_decode($beneficiaryShareJson,true);
                    if(is_array($beneficiaryShareArr)){
                        if($beneficiary["num_rows"] == count($beneficiaryShareArr)){
                            $totalShare = 0;
                            foreach($beneficiaryShareArr as $beneficiaryShareArrRow){
                                if( (!empty($beneficiaryShareArrRow["beneficiary_id"])) && (!empty($beneficiaryShareArrRow["beneficiary_share"]))){
                                    $totalShare = $totalShare+$beneficiaryShareArrRow["beneficiary_share"];
                                }else{
                                    return $this->error_response("Sorry! invalid beneficiary json!");
                                }
                            }
                            if($totalShare <= 100){
                                foreach($beneficiaryShareArr as $beneficiaryShareArrRow){
                                    $operation->update_data("insurance_beneficiary",array('id'=>$beneficiaryShareArrRow["beneficiary_id"]),array("share"=>$beneficiaryShareArrRow["beneficiary_share"]));
                                }
                                return $this->success_response("Beneficiary share updated!");
                            }else{
                                return $this->error_response("Sorry! Total share must be less than or equal to 100%!");
                            }
                        }else{
                            return $this->error_response("Sorry! invalid beneficiary count in json!");
                        }
                    }else{
                        return $this->error_response("Sorry! invalid beneficiary json format!");
                    }
                }else{
                    return $this->error_response("Sorry! invalid beneficiary json format!");
                }
            }else{
                return $this->error_response("Sorry! user_id and beneficiary_share_json required!");
            }
        }
    }

?>