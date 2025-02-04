<?php

    namespace App\Controllers\Web\admin;
    use App\Controllers\BaseController;
    use App\Models\Operation;

    class Referrel extends BaseController{
        public function referrel_list(){
            $operation = new Operation();
            $session = \Config\Services::session();
            $session_id = $session->get("lbt_admin_id");
            $user_type = $session->get("lbt_admin_type");
            $is_used = $this->input_get("is_used");
              ///////////////////////////////////
              $apiUrl = 'referrel-code-list';
              
              $apiData = array(
               // "is_used"=> $is_used
               "user_id"=> $session_id,
               "user_type"=> $user_type,
              );
              $Datalist = $operation->get_api($apiUrl,$apiData);
              // echo "<pre>";
              //   print_r($Datalist);
              //   die();
              if(!empty($Datalist->status == true)){
                  $data['referrel_code_list'] = $Datalist->data;
              }else{
                  $data['referrel_code_list'] = '';
              }
              if(!empty($Datalist->status == true)){
                $data['pagination'] = $Datalist->pagination;
              }else{
                $data['pagination'] = '';
              }
            //   echo "<pre>";
            //   print_r($data);
            //   die();
              ///////////////////////////////////
            if($session_id != ""){
                echo view('web/admin/includes/header');
                echo view('web/admin/includes/leftbar');
                echo view('web/admin/referrel/referrel_list',$data);
                echo view('web/admin/includes/footer');
            }else{
                return redirect()->to(base_url('console'));  
            }
        }
    }
?>